<?php
$sub_menu = '400300';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

// 판매가격 일괄수정
for ($i=0; $i<count($_POST['it_id']); $i++)
{
    $sql = "update {$g4['shop_item_table']}
               set ca_id          = '{$_POST['ca_id'][$i]}',
                   it_name        = '{$_POST['it_name'][$i]}',
                   it_cust_price  = '{$_POST['it_cust_price'][$i]}',
                   it_price       = '{$_POST['it_price'][$i]}',
                   it_point       = '{$_POST['it_point'][$i]}',
                   it_stock_qty   = '{$_POST['it_stock_qty'][$i]}',
                   it_use         = '{$_POST['it_use'][$i]}',
                   it_order       = '{$_POST['it_order'][$i]}'
             where it_id   = '{$_POST['it_id'][$i]}' ";
    sql_query($sql);
}

//goto_url("./itemlist.php?sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page");
goto_url("./itemlist.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
?>
