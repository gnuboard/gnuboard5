<?
$sub_menu = "200200";
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

    $sql = " delete from {$g4['point_table']} where po_id = '{$_POST['po_id'][$k]}' ";
    sql_query($sql);

    $sql = " select sum(po_point) as sum_po_point from {$g4['point_table']} where mb_id = '{$_POST['mb_id'][$k]}' ";
    $row = sql_fetch($sql);
    $sum_point = $row['sum_po_point'];

    $sql= " update {$g4['member_table']} set mb_point = '{$sum_point}' where mb_id = '{$_POST['mb_id'][$k]}' ";
    sql_query($sql);
}

goto_url('./point_list.php?'.$qstr);
?>
