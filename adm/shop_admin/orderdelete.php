<?
$sub_menu = '400400';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "d");

if ($od_id && $uq_id)
{
    // 장바구니 삭제
    sql_query(" delete from {$g4['yc4_cart_table']} where uq_id = '$uq_id' ");

    // 카드결제내역 삭제
    sql_query(" delete from {$g4['yc4_card_history_table']} where od_id = '$od_id' and uq_id = '$uq_id' ");

    // 주문서 삭제
    sql_query(" delete from {$g4['yc4_order_table']} where od_id = '$od_id' and uq_id = '$uq_id' ");
}

if ($return_url)
{
    goto_url("$return_url");
}
else
{
    $qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&sort1=$sort1&sort2=$sort2&page=$page";
    goto_url("./orderlist{$list}.php?$qstr");
}
?>
