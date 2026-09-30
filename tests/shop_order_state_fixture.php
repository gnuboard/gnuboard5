<?php
// 실제 설치본을 대상으로 하는 CLI 전용 테스트 대역. 운영 DB에서는 실행되지 않는다.
if (PHP_SAPI !== 'cli') exit;
$test_config_path = getenv('G5_ORDER_TEST_CONFIG');
$test_config = $test_config_path ? json_decode(file_get_contents($test_config_path), true) : null;
if (!$test_config || !is_file($test_config['root'].'/data/.order-state-test') ||
    trim(file_get_contents($test_config['root'].'/data/.order-state-test')) !== 'KVE-2026-2140-local-test') die('격리 테스트 설치본이 필요합니다.');
$test_input = json_decode(stream_get_contents(STDIN),true);
chdir($test_config['root']);
$_SERVER['DOCUMENT_ROOT']=$test_config['root'];
$_SERVER['SCRIPT_FILENAME']=$test_config['root'].'/tests/shop_order_state_fixture.php';
$_SERVER['SCRIPT_NAME']='/tests/shop_order_state_fixture.php';
$_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
$_SERVER['HTTP_HOST']='127.0.0.1';$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['REQUEST_URI']=$_SERVER['SCRIPT_NAME'];
if (isset($test_input['sid'])) { session_id($test_input['sid']); $_COOKIE['PHPSESSID']=$test_input['sid']; }
$_REQUEST=isset($test_input['request'])?$test_input['request']:array();
$_POST=isset($test_input['post'])?$test_input['post']:array();
require './common.php';
if (strpos(G5_MYSQL_DB,'issue48_') !== 0 || strpos(G5_MYSQL_HOST,'127.0.0.1:') !== 0) die('테스트 전용 DB가 아닙니다.');
require_once G5_LIB_PATH.'/shop_order_access.lib.php';
require_once G5_LIB_PATH.'/shop_order_maintenance.lib.php';

class OrderStateFakeToss {
    public $headers=array(); public $responseData=array();
    private $id; private $key; private $expected; private $mode;
    public function __construct($id,$key,$expected,$mode) { $this->id=$id; $this->key=$key; $this->expected=$expected; $this->mode=$mode; }
    public function approvePayment() {
        if (count(preg_grep('/^Idempotency-Key: g5-confirm-[a-f0-9]{64}$/',$this->headers)) !== 1) throw new RuntimeException('멱등 키가 없습니다.');
        $this->responseData=array('orderId'=>$this->id,'paymentKey'=>$this->key,'totalAmount'=>$this->expected,'status'=>'DONE','method'=>'카드');
        $data=sql_escape_string(json_encode($this->responseData));
        sql_query("insert into g5_order_test_pg set od_id='{$this->id}',approvals=1,queries=0,data='$data' on duplicate key update approvals=approvals+1,data='$data'");
        usleep(250000);
        return $this->mode !== 'timeout';
    }
    public function getPaymentByOrderId($id) {
        sql_query("update g5_order_test_pg set queries=queries+1 where od_id='$id'");
        $row=sql_fetch("select data from g5_order_test_pg where od_id='$id'");
        $this->responseData=$row?json_decode($row['data'],true):array();
        return !empty($this->responseData);
    }
}

$action=$test_input['action'];$result=null;
if ($action==='sql') {
    $q=sql_query($test_input['sql'],false);
    if ($q instanceof mysqli_result) { $result=array();while($row=sql_fetch_array($q))$result[]=$row; }
    else $result=array('ok'=>(bool)$q);
} elseif ($action==='session') {
    $_SESSION=$test_input['data'];
    if (!empty($test_input['member'])) {
        $test_member=get_member('testadmin');$_SESSION['ss_mb_id']='testadmin';
        $_SESSION['ss_mb_token_key']=get_token_encryption_key($test_member['mb_datetime']);
    }
    $result=array('ok'=>true);
}
elseif ($action==='session-get') $result=$_SESSION;
elseif ($action==='checkout') $result=shop_order_checkout_fields($test_input['id'],!empty($test_input['personal']));
elseif ($action==='load') $result=shop_order_access_load($test_input['id'],$test_input['pg']);
elseif ($action==='prepare') { shop_order_state_prepare(!empty($test_input['personal']));$result=array('session'=>$_SESSION,'post'=>$_POST,'member'=>$member['mb_id']); }
elseif ($action==='approve') {
    $id=$test_input['id'];$data=shop_order_access_load($id,'toss');shop_order_state_prepare(false);
    $fake=new OrderStateFakeToss($id,'test_key',1000,isset($test_input['mode'])?$test_input['mode']:'normal');
    $result=shop_order_toss_approve($fake,$id,'test_key',1000);
} elseif ($action==='cancel') {
    $id=$test_input['id'];shop_order_access_load($id,'toss');
    $cancel_msg='LOCAL TEST CANCELLATION';$od=array('od_id'=>'999');
    include G5_SHOP_PATH.'/toss/toss_cancel.php';
    $result=shop_order_state_row($id)['status'];
} elseif ($action==='cleanup') $result=shop_order_cleanup(!empty($test_input['apply']),3600);
elseif ($action==='reconcile-toss') {
    $fake=new OrderStateFakeToss($test_input['id'],'test_key',1000,'normal');
    $result=shop_order_reconcile_toss($test_input['id'],$fake);
}
elseif ($action==='quarantine') $result=shop_order_quarantine_legacy($test_input['id']);
elseif ($action==='complete') { shop_order_access_forget($test_input['id']);$result=array('ok'=>true); }
elseif ($action==='migration' || $action==='migration-sandbox') {
    require_once G5_LIB_PATH.'/migration.lib.php';
    if ($action==='migration-sandbox') {
        $g5['g5_shop_order_access_table']='g5_oa_test_state';
        $g5['g5_shop_order_data_table']='g5_oa_test_data';
        sql_query('DROP TABLE IF EXISTS g5_oa_test_state,g5_oa_test_data');
        sql_query('CREATE TABLE g5_oa_test_data LIKE g5_shop_order_data');
    }
    $m=g5_migration_parse_file(G5_PATH.'/migrations/20260914_001_order_access_state.sql');
    if(isset($m['error']))throw new RuntimeException($m['error']);
    $result=array();
    foreach($m['statements'] as $statement) {
        if(g5_migration_should_run_statement($statement['conditions']))$result[]=g5_migration_execute_statement($statement);
        else $result[]=array('skipped'=>true);
    }
    if ($action==='migration-sandbox') {
        $installed=sql_fetch('SHOW CREATE TABLE g5_shop_order_access');
        $upgraded=sql_fetch('SHOW CREATE TABLE g5_oa_test_state');
        $result['same_schema']=str_replace('g5_shop_order_access','TABLE',$installed['Create Table'])===str_replace('g5_oa_test_state','TABLE',$upgraded['Create Table']);
        $result['repeat_skipped']=!g5_migration_should_run_statement($m['statements'][0]['conditions']);
        sql_query('DROP TABLE g5_oa_test_state,g5_oa_test_data');
        $result['no_shop_skipped']=!g5_migration_should_run_statement($m['statements'][0]['conditions']);
    }
}
echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
