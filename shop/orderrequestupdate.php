<?php
include_once('./_common.php');

$od_id = $_POST['od_id'];
$uq_id = $_POST['uq_id'];
$rq_type = $_POST['rq_type'];
$rq_content = $_POST['rq_content'];

switch($rq_type) {
    case 1:
        $req_act = '교환요청';
        break;
    case 2:
        $req_act = '반품요청';
        break;
    default:
        $req_act = '취소요청';
        break;
}

if(!count($_POST['chk_ct_id']))
    alert($req_act.'하실 상품을 하나이상 선택해 주십시오.');

$od = sql_fetch(" select od_id from {$g4['shop_order_table']} where od_id = '$od_id' and uq_id = '$uq_id' and mb_id = '{$member['mb_id']}' ");

if (!$od['od_id']) {
    alert("존재하는 주문이 아닙니다.");
}

$sql = " select count(*) as cnt from {$g4['shop_request_table']} where od_id = '$od_id' and rq_status = '0' ";
$rq = sql_fetch($sql);

if($rq['cnt'])
    alert('관리자가 확인 중인 '.$req_act.'이 있습니다');

$count = count($_POST['ct_id']);
$ct_id  = '';
$rsp = '';

for($i=0; $i<$count; $i++) {
    if($_POST['chk_ct_id'][$i]) {
        $ct_id .= $rsp.$_POST['ct_id'][$i];
        $rsp = ',';
    }
}

$sql = " insert into {$g4['shop_request_table']}
              ( rq_type, od_id, ct_id, mb_id, rq_content, rq_reg_time, rq_ip )
            values
              ( '$rq_type', '$od_id', '$ct_id', '{$member['mb_id']}', '$rq_content', '".G4_TIME_YMDHIS."', '$REMOTE_ADDR' ) ";
sql_query($sql);

goto_url(G4_SHOP_URL.'/orderinquiryview.php?od_id='.$od_id.'&amp;uq_id='.$uq_id);
?>