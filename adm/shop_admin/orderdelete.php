<?php
$sub_menu = '400400';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "d");

if ($od_id)
{
    // 장바구니 삭제
    sql_query(" delete from {$g4['shop_cart_table']} where od_id = '$od_id' ");

    // 주문서 삭제
    sql_query(" delete from {$g4['shop_order_table']} where od_id = '$od_id' ");
}

if ($return_url)
{
    goto_url("$return_url");
}
else
{
    $qstr = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";
    goto_url("./orderlist{$list}.php?$qstr");
}
?>
