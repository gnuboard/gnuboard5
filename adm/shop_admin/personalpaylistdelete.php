<?php
$sub_menu = '400440';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'd');

check_token();

$count = count($_POST['chk']);
if(!$count)
    alert('선택삭제 하실 항목을 하나이상 선택해 주세요.');

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $sql = " delete from {$g5['g5_shop_personalpay_table']} where pp_id = '{$_POST['pp_id'][$k]}' ";
    sql_query($sql);
}

goto_url('./personalpaylist.php');
?>