<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$sql = " select * from {$g4['shop_request_table']} where rq_id = '$rq_id' ";
$rq = sql_fetch($sql);
if(!$rq['rq_id'])
    alert('등록된 자료가 없습니다.');

switch($rq['rq_type']) {
    case 0:
        $type = '취소';
        break;
    case 1:
        $type = '교환';
        break;
    case 2:
        $type = '반품';
        break;
    default:
        $type = '';
        break;
}

$g4['title'] = $type.'요청 상세내용';
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<?php include_once('./orderrequestview.inc.php'); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>