<?php
// 운영자 전용. HTTP 요청으로 실행되지 않으며 토큰/평문/PG 응답을 출력하지 않는다.
if (PHP_SAPI !== 'cli') { header('HTTP/1.0 404 Not Found'); exit; }
$options = getopt('', array('action:', 'order:', 'apply', 'grace:', 'receipt:', 'verified'));
$root = dirname(dirname(__FILE__));
chdir($root);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/tools/shop-order-maintenance.php';
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
require './common.php';
require_once G5_LIB_PATH.'/shop_order_access.lib.php';
require_once G5_LIB_PATH.'/shop_order_maintenance.lib.php';
try {
    $action = isset($options['action']) ? $options['action'] : 'list';
    $id = isset($options['order']) ? $options['order'] : '';
    $apply = isset($options['apply']);
    if (!in_array($action, array('list','cleanup'), true) && !shop_order_access_id($id)) throw new RuntimeException('--order에 주문번호를 지정해 주십시오.');
    $out = array();
    if ($action === 'list') {
        $rows = sql_query("select od_id,pg,status,expires,updated_at from ".shop_order_state_table()." order by updated_at limit 500");
        while ($row = sql_fetch_array($rows)) $out[] = $row;
        $rows = sql_query("select d.od_id,d.dt_pg as pg,d.dt_time from {$g5['g5_shop_order_data_table']} d left join ".shop_order_state_table()." a on d.od_id=a.od_id where a.od_id is null limit 500");
        while ($row = sql_fetch_array($rows)) { $row['status']='legacy_unreviewed'; $out[]=$row; }
    } elseif ($action === 'cleanup') {
        $out = shop_order_cleanup($apply, isset($options['grace']) ? (int)$options['grace'] : 86400);
    } elseif ($action === 'quarantine-legacy') {
        if (!$apply) $out = array('order'=>$id,'action'=>'--apply로 평문 제거·해시 별도 보관·복귀 차단을 실행합니다. 백업과 PG 대조를 먼저 수행하십시오.');
        else $out = shop_order_quarantine_legacy($id);
    } elseif ($action === 'reconcile-toss') {
        if (!$apply) throw new RuntimeException('PG 조회와 상태 갱신을 실행하려면 --apply를 지정해 주십시오.');
        require_once G5_SHOP_PATH.'/toss/toss.inc.php';
        $toss = new TossPayments($config['cf_toss_client_key'],$config['cf_toss_secret_key'],$config['cf_lg_mid']);
        $toss->setPaymentHeader(); $out = shop_order_reconcile_toss($id, $toss);
    } elseif ($action === 'reconcile-kcp') {
        if (!$apply || !isset($options['verified'],$options['receipt'])) throw new RuntimeException('KCP 거래 원장 대조 후 --apply --verified --receipt=파일을 지정해 주십시오.');
        shop_order_state_lock('order', $id); $row = shop_order_state_row($id);
        $state = $row ? json_decode($row['state_json'],true) : null;
        $receipt = json_decode(file_get_contents($options['receipt']),true);
        if (!$row || $row['pg'] !== 'kcp' || !in_array($row['status'],array('approving','unknown'),true) ||
            !is_array($receipt) || !isset($receipt['orderId'],$receipt['amount'],$receipt['tno'],$receipt['res_cd']) ||
            (string)$receipt['orderId'] !== $id || empty($state['expected_amount']) || (int)$receipt['amount'] !== (int)$state['expected_amount'] ||
            !is_string($receipt['tno']) || !preg_match('/\A[A-Za-z0-9_-]{6,100}\z/D',$receipt['tno']) ||
            !in_array($receipt['res_cd'],array('0000','V000'),true)) throw new RuntimeException('KCP 대조 자료가 주문·금액·승인 상태와 일치하지 않습니다.');
        $payload = array_intersect_key($receipt,array_flip(shop_order_kcp_result_fields()));
        foreach ($payload as $value) if (!is_scalar($value)) throw new RuntimeException('승인 필드는 단일 값이어야 합니다.');
        shop_order_state_approved($id,array('payload'=>base64_encode(serialize($payload))));
        $out = array('order'=>$id,'status'=>'approved','action'=>'운영자 확인 자료 반영. 원래 토큰으로 재시도하십시오.');
    } elseif ($action === 'purge-reviewed-legacy') {
        if (!$apply || !isset($options['verified'],$options['receipt'])) throw new RuntimeException('기존 거래 원장 대조 후 --apply --verified --receipt=파일을 지정하십시오.');
        shop_order_state_lock('order',$id);$row=shop_order_state_row($id);
        $receipt=json_decode(file_get_contents($options['receipt']),true);
        if (!$row || $row['status'] !== 'legacy' || !is_array($receipt) || !isset($receipt['orderId'],$receipt['resolution'],$receipt['reference']) ||
            (string)$receipt['orderId'] !== $id || !in_array($receipt['resolution'],array('unpaid','cancelled','completed'),true) ||
            !is_string($receipt['reference']) || trim($receipt['reference'])==='') throw new RuntimeException('격리된 과거 주문과 대조 자료를 확인하십시오.');
        if ($receipt['resolution']==='completed') {
            $order=sql_fetch("select od_tno from {$g5['g5_shop_order_table']} where od_id='$id'");
            $personal=sql_fetch("select pp_tno from {$g5['g5_shop_personalpay_table']} where pp_id='$id'");
            if (empty($order['od_tno']) && empty($personal['pp_tno'])) throw new RuntimeException('완료 거래의 주문 저장을 먼저 복구하십시오.');
        }
        $pg=sql_escape_string($row['pg']);
        if (!sql_query("delete from {$g5['g5_shop_order_data_table']} where od_id='$id' and dt_pg='$pg'",false)) shop_order_access_fail();
        shop_order_state_write($id,array('status'=>'purged','state_json'=>json_encode(array('review_sha256'=>hash_file('sha256',$options['receipt']))),'response_json'=>'','token_hash'=>'','payment_key'=>''));
        $out=array('order'=>$id,'status'=>'purged');
    } elseif ($action === 'complete-reviewed') {
        if (!$apply || !isset($options['verified'])) throw new RuntimeException('주문·PG·장바구니·포인트·쿠폰 대조를 마친 후 --apply --verified를 지정하십시오.');
        shop_order_state_lock('order',$id);$row=shop_order_state_row($id);
        if (!$row || $row['status'] !== 'finalizing') throw new RuntimeException('부분 저장 대조 대상이 아닙니다.');
        $state=json_decode($row['state_json'],true);
        $saved=$state['personal'] ? sql_fetch("select pp_tno as tno from {$g5['g5_shop_personalpay_table']} where pp_id='$id'") : sql_fetch("select od_tno as tno from {$g5['g5_shop_order_table']} where od_id='$id'");
        if (empty($saved['tno'])) throw new RuntimeException('저장된 주문의 거래번호가 없습니다.');
        if ($row['pg']==='toss' && $saved['tno']!==$row['payment_key']) throw new RuntimeException('저장 거래번호와 승인 기록이 다릅니다.');
        if ($row['pg']==='kcp') {
            $response=json_decode($row['response_json'],true);
            $response=isset($response['payload'])?shop_order_decode_data($response['payload']):false;
            if (!is_array($response) || empty($response['tno']) || $saved['tno']!==$response['tno']) throw new RuntimeException('KCP 저장 거래번호와 승인 기록이 다릅니다.');
        }
        shop_order_state_complete($id);$out=array('order'=>$id,'status'=>'completed');
    } else throw new RuntimeException('지원하지 않는 action입니다.');
    echo json_encode($out).PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);
}
