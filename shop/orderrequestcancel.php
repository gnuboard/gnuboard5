<?php
include_once('./_common.php');

$rq_id = $_POST['rq_id'];
$od_id = $_POST['od_id'];

$sql = " select * from {$g4['shop_request_table']} where od_id = '$od_id' and rq_id = '$rq_id' and rq_parent = '0' ";
$req = sql_fetch($sql);

if(!$req['rq_id'])
    die('요청정보가 존재하지 않습니다.');

// 처리내역이 있는지
$sql = " select count(*) as cnt from {$g4['shop_request_table']} where rq_parent = '$rq_id' and od_id = '$od_id' ";
$row = sql_fetch($sql);
if($row['cnt'])
    die('요청내용을 관리자가 확인 중이므로 취소할 수 없습니다.');

// 고객요청 자료에 상태반영
$sql = " update {$g4['shop_request_table']}
            set rq_status = '99'
            where rq_id = '$rq_id' ";
sql_query($sql);

// 처리내용입력
$rq_content = '고객이 요청내역 취소';
$sql = " insert into `{$g4['shop_request_table']}`
              ( rq_type, rq_parent, od_id, ct_id, mb_id, rq_content, rq_status, rq_item, dl_company, rq_invoice, rq_amount1, rq_amount2, rq_amount3, rq_account, rq_ip, rq_time )
            values
              ( '{$req['rq_type']}', '$rq_id', '$od_id', '{$req['ct_id']}', '{$member['mb_id']}', '$rq_content', '99', '', '', '', '', '', '', '', '$REMOTE_ADDR', '".G4_TIME_YMDHIS."' ) ";
sql_query($sql);
?>