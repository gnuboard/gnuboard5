<?php
$sub_menu = "400800";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "d");

$count = count($_POST['list_chk']);

if(!$count) {
    alert('삭제할 쿠폰을 1개이상 선택해 주세요.');
}

for($i=0; $i<$count; $i++) {
    $cp_no = $_POST['list_chk'][$i];
    $sql = " delete from {$g4['yc4_coupon_table']} where cp_no = '$cp_no' ";
    @sql_query($sql);
}

goto_url("./couponlist.php?$qstr");
?>