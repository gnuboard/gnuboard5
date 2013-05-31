<?php
$sub_menu = "400490";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'd');

check_token();

$count = count($_POST['chk']);
if(!$count)
    alert("선택삭제 하실 항목을 하나이상 선택해 주세요.");

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $sql = " delete from {$g4['shop_mileage_table']} where ml_id = '{$_POST['ml_id'][$k]}' ";
    sql_query($sql);

    $sql = " select sum(ml_point) as sum_ml_point from {$g4['shop_mileage_table']} where mb_id = '{$_POST['mb_id'][$k]}' ";
    $row = sql_fetch($sql);
    $sum_mileage = $row['sum_ml_point'];

    $sql= " update {$g4['member_table']} set mb_mileage = '{$sum_mileage}' where mb_id = '{$_POST['mb_id'][$k]}' ";
    sql_query($sql);
}

goto_url('./mileagelist.php?'.$qstr);
?>
