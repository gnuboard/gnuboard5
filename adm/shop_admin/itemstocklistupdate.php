<?
$sub_menu = "400620";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

// 재고 일괄수정
for ($i=0; $i<count($_POST[it_id]); $i++) 
{
    $sql = "update $g4[yc4_item_table] 
               set it_stock_qty    = '{$_POST[it_stock_qty][$i]}',
                   it_use = '{$_POST[it_use][$i]}'
             where it_id = '{$_POST[it_id][$i]}' ";
    sql_query($sql);
}

goto_url("./itemstocklist.php?sort1=$sort&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page");
?>
