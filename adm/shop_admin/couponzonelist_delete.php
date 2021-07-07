<?php
$sub_menu = '400810';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
if(!$count)
    alert('선택삭제 하실 항목을 하나이상 선택해 주세요.');

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
    $ccz_id = isset($_POST['cz_id'][$k]) ? (int) $_POST['cz_id'][$k] : 0;

    $sql = " delete from {$g5['g5_shop_coupon_zone_table']} where cz_id = '{$ccz_id}' ";
    sql_query($sql);
}

goto_url('./couponzonelist.php?'.$qstr);