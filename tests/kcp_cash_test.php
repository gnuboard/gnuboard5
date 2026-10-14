<?php
// 실행: php tests/kcp_cash_test.php /tmp/격리경로/mysql.sock [--worker 주문번호]
// 운영 DB 설정을 읽지 않는다. root/빈 암호인 별도 MySQL 인스턴스에서만 실행한다.
if ((!defined('KCP_CASH_TEST_BOOTSTRAP') && PHP_SAPI !== 'cli') || empty($argv[1]) || strpos($argv[1], '/tmp/kve2513-') !== 0) exit(1);
define('_GNUBOARD_', true);
define('G5_TABLE_PREFIX', 'fixture_');
define('G5_TOKEN_ENCRYPTION_KEY', 'fixture-key-not-a-production-key');
mysqli_report(MYSQLI_REPORT_OFF);
$db = mysqli_connect('localhost', 'root', '', '', 0, $argv[1]);
if (!$db) throw new RuntimeException('isolated database unavailable');
mysqli_query($db, 'CREATE DATABASE IF NOT EXISTS kve2513_test CHARACTER SET utf8mb4');
mysqli_select_db($db, 'kve2513_test');
mysqli_set_charset($db, 'utf8mb4');
$g5 = array('g5_shop_order_table'=>'cash_order', 'g5_shop_personalpay_table'=>'cash_personal', 'g5_shop_cart_table'=>'cash_cart', 'g5_shop_item_table'=>'cash_item');
$default = array('de_pg_service'=>'kcp','de_taxsave_use'=>1,'de_taxsave_types'=>'account,transfer,vbank');
$member = array('mb_id'=>'owner'); $is_member=true; $is_admin=''; $_SERVER['REMOTE_ADDR']='127.0.0.1';
$session=array(); $fault=''; $passed=0; $calls=array();
function get_session($key) { global $session; return $session[$key] ?? ''; }
function set_session($key,$value) { global $session; $session[$key]=$value; }
function sql_escape_string($value) { global $db; return mysqli_real_escape_string($db,$value); }
function sql_query($sql,$error=true) {
    global $db,$fault;
    if (strpos($sql,'SET od_cash=0')!==false || strpos($sql,'SET pp_cash=0')!==false) {
        if ($fault==='prepare_error') return false;
        if ($fault==='prepare_ack_lost') { mysqli_query($db,$sql); return false; }
    }
    if ($fault==='cleanup_error' && (strpos($sql,"SET od_cash_info=''")!==false || strpos($sql,"SET pp_cash_info=''")!==false)) return false;
    if (strpos($sql,'SET od_cash=1')!==false || strpos($sql,'SET pp_cash=1')!==false) {
        if (in_array($fault,array('sql','prepare_error','prepare_ack_lost','cleanup_error'),true)) return false;
        if ($fault==='throw') throw new RuntimeException('injected write error');
        if (in_array($fault,array('after_commit','after_commit_throw','after_commit_changed','after_commit_changed_cancel_fail'),true)) {
            $ok=mysqli_query($db,$sql);
            if (!$ok) throw new RuntimeException(mysqli_error($db));
            if (strpos($fault,'changed')!==false) mysqli_query($db, strpos($sql,'SET od_cash=1')!==false ? 'UPDATE cash_order SET od_receipt_price=999' : 'UPDATE cash_personal SET pp_receipt_price=999');
            if ($fault==='after_commit_throw') throw new RuntimeException('write acknowledged late');
            return false;
        }
    }
    return mysqli_query($db,$sql);
}
function sql_fetch($sql,$error=true) { $r=sql_query($sql,$error); return $r ? mysqli_fetch_assoc($r) : false; }
function get_sql_affected_rows() { global $db,$fault; $n=mysqli_affected_rows($db); return $fault==='rows2' && $n===1 ? 2 : $n; }
require __DIR__.'/../lib/shop.lib.php';
require __DIR__.'/../lib/kcp_cash.lib.php';
function expect_cash($ok,$name) { global $passed; if (!$ok) throw new RuntimeException('FAIL '.$name); $passed++; echo "PASS $name\n"; }
function setup_cash($engine) {
    global $db;
    foreach(array('cash_order','cash_personal','cash_cart','cash_item') as $table) mysqli_query($db,"DROP TABLE IF EXISTS $table");
    // 제품 설치 SQL을 직접 사용하여 테스트 전용 컬럼이 구현 오류를 가리지 않게 한다.
    $schema=file_get_contents(__DIR__.'/../install/gnuboard5shop.sql');
    mysqli_query($db,"SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");
    foreach(array('order'=>'cash_order','personalpay'=>'cash_personal') as $source=>$target) {
        $pattern='/CREATE TABLE IF NOT EXISTS `g5_shop_'.$source.'` \(.*?\) ENGINE=MyISAM DEFAULT CHARSET=utf8;/s';
        if (!preg_match($pattern,$schema,$match)) throw new RuntimeException('설치 스키마 없음');
        $ddl=str_replace(array('`g5_shop_'.$source.'`','ENGINE=MyISAM'),array('`'.$target.'`','ENGINE='.$engine),$match[0]);
        if (!mysqli_query($db,$ddl)) throw new RuntimeException(mysqli_error($db));
    }
    mysqli_query($db,"SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
    mysqli_query($db,'CREATE TABLE cash_item (it_id varchar(20), it_name varchar(255))');
    mysqli_query($db,'CREATE TABLE cash_cart (ct_id int, od_id varchar(20), it_id varchar(20))');
    mysqli_query($db,"INSERT INTO cash_item VALUES ('1','검증 상품')");
}
function fixture_cash($type='order',$id='100') {
    global $db,$session,$is_member,$member,$is_admin,$fault,$calls,$default;
    $p=$type==='order'?'od':'pp'; $table=$type==='order'?'cash_order':'cash_personal';
    mysqli_query($db,"DELETE FROM $table WHERE {$p}_id='$id'");
    $values=array();$columns=mysqli_query($db,"SHOW COLUMNS FROM $table");
    while($column=mysqli_fetch_assoc($columns)) {
        $value=$column['Default'];
        if ($value===null) $value=preg_match('/int|decimal|float|double/',$column['Type']) ? '0' : '';
        $values[$column['Field']]=$value;
    }
    $values=array_merge($values,array($p.'_id'=>$id,$p.'_pg'=>'kcp',$p.'_settle_case'=>'무통장',
        $p.'_name'=>'가상 주문자',$p.'_email'=>'test@example.invalid',$p.'_hp'=>'01000000000',
        $p.'_ip'=>'127.0.0.1',$p.'_time'=>'2026-10-14 09:00:00',$p.'_receipt_price'=>1000,$p.'_cash_info'=>''));
    if ($p==='od') $values=array_merge($values,array('mb_id'=>'owner','od_tel'=>'01000000000','od_status'=>'입금','od_tax_mny'=>909,'od_vat_mny'=>91));
    else $values=array_merge($values,array('od_id'=>0,'pp_price'=>1000));
    $quoted=array_map(function($v){return "'".sql_escape_string((string)$v)."'";},array_values($values));
    if (!mysqli_query($db,"INSERT INTO $table (`".implode('`,`',array_keys($values))."`) VALUES (".implode(',',$quoted).")")) throw new RuntimeException(mysqli_error($db));
    mysqli_query($db,"DELETE FROM cash_cart WHERE od_id='$id'");
    mysqli_query($db,"INSERT INTO cash_cart VALUES (1,'$id','1')");
    $session=array();$is_member=true;$member=array('mb_id'=>'owner');$is_admin='';$fault='';$calls=array();
    $default=array('de_pg_service'=>'kcp','de_taxsave_use'=>1,'de_taxsave_types'=>'account,transfer,vbank');
    if ($type==='personalpay') $session['ss_personalpay_uid']=get_shop_uid($type,$id,'2026-10-14 09:00:00','127.0.0.1');
    return request_cash($type,$id);
}
function request_cash($type,$id) {
    return array('req_tx'=>'pay','tx'=>$type,'ordr_idxx'=>$id,'tr_code'=>'0','id_info'=>'01000000000','cash_token'=>kcp_cash_token($type,$id));
}
function gateway_cash($op,$data) {
    global $calls;
    $calls[]=array($op,$data);
    return array('res_cd'=>'0000','cash_no'=>'12345678901234','receipt_no'=>'100000001','app_time'=>'20261014090000','reg_stat'=>'NTRW','reg_desc'=>'테스트');
}
function invoke_cash($r,$gateway='gateway_cash',$method='POST') { return kcp_cash_process(kcp_cash_request($r,$method),$gateway); }
function denied_cash($r,$name,$method='POST') {
    global $calls; $before=count($calls);$denied=false;
    try { invoke_cash($r,'gateway_cash',$method); } catch (RuntimeException $e) { $denied=true; }
    expect_cash($denied && count($calls)===$before,$name.' / PG 호출 없음');
}
if (defined('KCP_CASH_TEST_BOOTSTRAP')) return;
if (in_array(($argv[2] ?? ''),array('--worker','--crash'),true)) {
    $r=request_cash('order',$argv[3]);
    try {
        $result=invoke_cash($r,function($op,$data) use ($argv) {
            file_put_contents(dirname($argv[1]).'/calls.log', $op."\n",FILE_APPEND|LOCK_EX);
            if ($argv[2]==='--crash') exit(23);
            usleep(400000);
            return gateway_cash($op,$data);
        });
        echo $result['state'];
    } catch (RuntimeException $e) { echo 'blocked'; }
    exit;
}
foreach(array('MyISAM','InnoDB') as $engine) {
    setup_cash($engine);
    foreach(array('order','personalpay') as $type) {
        $p=$type==='order'?'od':'pp';$table=$type==='order'?'cash_order':'cash_personal';
        foreach(array('guest','other','group','missing','get','mod','array','bad_id','missing_token','forged','expired','other_token') as $case) {
            $r=fixture_cash($type);
            if ($type==='personalpay' && in_array($case,array('guest','other','group'),true)) unset($session['ss_personalpay_uid']);
            switch($case) {
                case 'guest':$is_member=false;break;
                case 'other':$member['mb_id']='other';break;
                case 'group':$member['mb_id']='other';$is_admin='group';break;
                case 'missing':$r['ordr_idxx']='999';$is_admin='super';break;
                case 'mod':$r['req_tx']='mod';break;
                case 'array':$r['ordr_idxx']=array('100');break;
                case 'bad_id':$r['ordr_idxx']='10!0';break;
                case 'missing_token':unset($r['cash_token']);break;
                case 'forged':$r['cash_token']=str_repeat('0',64);break;
                case 'expired':$session[kcp_cash_key($type,'100')]['expires']=time()-1;break;
                case 'other_token':$r['cash_token']=kcp_cash_token($type,'101');break;
            }
            denied_cash($r,"$engine $type $case",$case==='get'?'GET':'POST');
        }
        foreach(array('member','guest_uid','super') as $auth) {
            $r=fixture_cash($type);
            if ($auth==='super') {$is_member=false;$is_admin='super';}
            if ($auth==='guest_uid') {
                $is_member=false;
                $session[$type==='order'?'ss_orderview_uid':'ss_personalpay_uid']=get_shop_uid($type,'100','2026-10-14 09:00:00','127.0.0.1');
            }
            $r+=array('amt_tot'=>'99999999','amt_tax'=>'0','buyr_name'=>'tampered','corp_type'=>'1','mod_value'=>'OTHER');
            $result=invoke_cash($r);
            expect_cash($result['state']==='issued' && count($calls)===1 && $calls[0][1]['amt_tot']===1000 && $calls[0][1]['buyr_name']==='가상 주문자',"$engine $type $auth / 원장 데이터 발급");
            denied_cash($r,"$engine $type 재전송");
            $saved=sql_fetch("SELECT * FROM $table WHERE {$p}_id='100'");
            expect_cash((int)$saved[$p.'_cash']===1 && $saved[$p.'_cash_no']==='12345678901234',"$engine $type 발급 저장");
        }
        foreach(array('unpaid','card','other_pg','already','pending','disabled') as $case) {
            $r=fixture_cash($type);
            $set=array('unpaid'=>"{$p}_receipt_price=0",'card'=>"{$p}_settle_case='신용카드'",'other_pg'=>"{$p}_pg='toss'",'already'=>"{$p}_cash=1",'pending'=>"{$p}_cash_info='pending'");
            if ($case==='disabled') $default['de_taxsave_use']=0; else mysqli_query($db,"UPDATE $table SET ".$set[$case]);
            denied_cash($r,"$engine $type $case");
        }
    }
    $r=fixture_cash('personalpay');mysqli_query($db,"UPDATE cash_personal SET od_id='100'");denied_cash($r,"$engine 주문 연결 개인결제");
    foreach(array('req_tx','tx','cash_token','tr_code','id_info') as $field) {
        $r=fixture_cash();$r[$field]=array('unexpected');denied_cash($r,"$engine 배열 입력 $field");
    }
    $r=fixture_cash();$r['id_info']='01000<script>';denied_cash($r,"$engine 식별번호 형식");
    $r=fixture_cash();$r['tr_code']='2';denied_cash($r,"$engine 발급 용도 형식");
    $r=fixture_cash();$fault='rows2';denied_cash($r,"$engine 선점 비정상 행 수");$fault='';
    $r=fixture_cash();mysqli_query($db,'UPDATE cash_order SET od_refund_price=1000');denied_cash($r,"$engine 전액환불");
    $r=fixture_cash();mysqli_query($db,'UPDATE cash_order SET od_refund_price=500');denied_cash($r,"$engine 부분환불 과세 불일치");
    $r=fixture_cash();mysqli_query($db,'UPDATE cash_order SET od_refund_price=500, od_tax_mny=455, od_vat_mny=45');
    expect_cash(invoke_cash($r)['amount']===500,"$engine 부분환불 정합 금액");
    $r=fixture_cash();mysqli_query($db,'UPDATE cash_order SET od_tax_mny=409, od_vat_mny=41, od_free_mny=550');
    expect_cash(invoke_cash($r)['state']==='issued' && $calls[0][1]['amt_sup']===959,"$engine 복합과세");
    foreach(array('sql','throw','changed','cancel_fail','timeout','malformed','rows2','receipt_bad') as $case) {
        $r=fixture_cash();
        if ($case==='sql' || $case==='throw') $fault=$case;
        $result=invoke_cash($r,function($op,$data) use($case) {
            global $db,$calls;
            if ($case==='timeout') throw new RuntimeException('timeout');
            if ($case==='malformed') return array('res_cd'=>'0000','cash_no'=>'');
            if (($case==='changed' || $case==='cancel_fail') && $op==='pay') mysqli_query($db,'UPDATE cash_order SET od_receipt_price=999');
            if ($case==='rows2' && $op==='pay') { global $fault; $fault='rows2'; }
            if ($case==='cancel_fail' && $op==='cancel') {$calls[]=array($op,$data);return array('res_cd'=>'9999');}
            $answer=gateway_cash($op,$data);
            if ($case==='receipt_bad' && $op==='pay') $answer['receipt_no']="'><script>";
            return $answer;
        });
        if ($case==='rows2') {
            expect_cash($result['state']==='issued' && count($calls)===1,"$engine 저장 결과 오류 / 원장 재확인으로 불필요한 취소 방지");
        } elseif (in_array($case,array('sql','throw','changed','receipt_bad'),true)) {
            expect_cash($result['state']==='compensated' && count($calls)===2 && $calls[1][1]['cash_no']==='12345678901234',"$engine $case / 이번 승인만 보상취소");
        } else {
            expect_cash($result['state']==='pending',"$engine $case / 확인 대기");
            $fault='';denied_cash(request_cash('order','100'),"$engine $case / 재발급 차단");
        }
    }
    foreach(array('order','personalpay') as $type) {
        $p=$type==='order'?'od':'pp';$table=$type==='order'?'cash_order':'cash_personal';
        foreach(array('after_commit','after_commit_throw','after_commit_changed','after_commit_changed_cancel_fail') as $mode) {
            $r=fixture_cash($type);$fault=$mode;
            $result=invoke_cash($r,function($op,$data)use($mode,$table,$p){
                if ($op==='cancel') {
                    $current=sql_fetch("SELECT * FROM $table WHERE {$p}_id=100");
                    expect_cash((int)$current[$p.'_cash']===0 && kcp_cash_pending_notice($current[$p.'_cash_info'])!=='','외부 취소 전 확인 필요 상태 영속화');
                }
                $response=gateway_cash($op,$data);
                if ($op==='cancel' && $mode==='after_commit_changed_cancel_fail') $response['res_cd']='9999';
                return $response;
            });
            $fault='';$stored=sql_fetch("SELECT * FROM $table WHERE {$p}_id=100");
            if ($mode==='after_commit' || $mode==='after_commit_throw') {
                expect_cash($result['state']==='issued' && count($calls)===1 && (int)$stored[$p.'_cash']===1,"$engine $type $mode / 이미 저장된 승인 유지");
            } elseif ($mode==='after_commit_changed') {
                expect_cash($result['state']==='compensated' && count($calls)===2 && (int)$stored[$p.'_cash']===0 && $stored[$p.'_cash_no']==='' && $stored[$p.'_cash_info']==='',"$engine $type $mode / 취소와 원장 최종 상태 일치");
            } else {
                expect_cash($result['state']==='pending' && count($calls)===2 && (int)$stored[$p.'_cash']===0 && kcp_cash_pending_notice($stored[$p.'_cash_info'])!=='',"$engine $type $mode / 취소 실패 관리자 경고");
                denied_cash(request_cash($type,'100'),"$engine $type 취소 실패 재발급 차단");
            }
        }
    }
    foreach(array('order','personalpay') as $type) {
        $p=$type==='order'?'od':'pp';$table=$type==='order'?'cash_order':'cash_personal';
        foreach(array('prepare_error','prepare_ack_lost','cleanup_error','other_trade') as $mode) {
            $r=fixture_cash($type);$fault=$mode;
            $result=invoke_cash($r,function($op,$data)use($mode,$table,$p){
                global $db;
                if ($mode==='other_trade' && $op==='pay') mysqli_query($db,"UPDATE $table SET {$p}_cash=1, {$p}_cash_no='999', {$p}_cash_info='other trade'");
                return gateway_cash($op,$data);
            });
            $fault='';$stored=sql_fetch("SELECT * FROM $table WHERE {$p}_id=100");
            if ($mode==='prepare_ack_lost') {
                expect_cash($result['state']==='compensated' && count($calls)===2 && (int)$stored[$p.'_cash']===0 && $stored[$p.'_cash_info']==='',"$engine $type 취소 준비 응답 유실 / 재조회 후 안전 취소");
            } elseif ($mode==='other_trade') {
                expect_cash($result['state']==='pending' && count($calls)===1 && $stored[$p.'_cash_no']==='999' && $stored[$p.'_cash_info']==='other trade',"$engine $type 다른 거래 변경·취소 없음");
            } else {
                expect_cash($result['state']==='pending' && count($calls)===($mode==='prepare_error'?1:2) && (int)$stored[$p.'_cash']===0 && kcp_cash_pending_notice($stored[$p.'_cash_info'])!=='',"$engine $type $mode / 상태 불명 경고 유지");
            }
        }
    }
    fixture_cash('order','200');
    @unlink(dirname($argv[1]).'/calls.log');
    $children=array();
    for($i=0;$i<4;$i++) {
        $cmd=array(PHP_BINARY,__FILE__,$argv[1],'--worker','200');
        $proc=proc_open($cmd,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
        $children[]=array($proc,$pipes);
    }
    $issued=0;
    foreach($children as $child) {
        fclose($child[1][0]); $out=stream_get_contents($child[1][1]); $err=stream_get_contents($child[1][2]); fclose($child[1][1]);fclose($child[1][2]);
        expect_cash(proc_close($child[0])===0,"$engine 동시 프로세스 정상 종료");
        if ($out==='issued') $issued++;
    }
    expect_cash($issued===1 && trim(file_get_contents(dirname($argv[1]).'/calls.log'))==='pay',"$engine 4개 세션 경합 / 발급 1회");
    fixture_cash('order','300');
    $proc=proc_open(array(PHP_BINARY,__FILE__,$argv[1],'--crash','300'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    foreach($pipes as $pipe) fclose($pipe);
    expect_cash(proc_close($proc)===23,"$engine PG 호출 도중 프로세스 종료");
    denied_cash(request_cash('order','300'),"$engine 프로세스 종료 후 영속 상태 재발급 차단");
    $pending=sql_fetch("SELECT od_cash_info FROM cash_order WHERE od_id='300'");
    expect_cash(strpos(kcp_cash_pending_notice($pending['od_cash_info']),'처리 확인 필요')!==false,"$engine 관리자 확인 안내");
    expect_cash(strpos(kcp_cash_pending_notice(serialize(array('kcp_cash_state'=>'pending','attempt'=>'<script>'))),'<script>')===false,"$engine 관리자 상태 출력 이스케이프");
}
echo "TOTAL $passed\n";
