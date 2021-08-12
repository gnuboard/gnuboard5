<?php
$sub_menu = '400400';
include_once('./_common.php');

//print_r2($_POST); exit;

check_admin_token();

$count_post_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

$sort1 = isset($_POST['sort1']) ? clean_xss_tags($_POST['sort1'], 1, 1) : '';
$sort2 = isset($_POST['sort2']) ? clean_xss_tags($_POST['sort2'], 1, 1) : '';
$sel_field = isset($_POST['sel_field']) ? clean_xss_tags($_POST['sel_field'], 1, 1) : '';
$od_status = isset($_POST['od_status']) ? clean_xss_tags($_POST['od_status'], 1, 1) : '';
$od_settle_case = isset($_POST['od_settle_case']) ? clean_xss_tags($_POST['od_settle_case'], 1, 1) : '';
$od_misu = isset($_POST['od_misu']) ? clean_xss_tags($_POST['od_misu'], 1, 1) : '';
$od_cancel_price = isset($_POST['od_cancel_price']) ? clean_xss_tags($_POST['od_cancel_price'], 1, 1) : '';
$od_receipt_price = isset($_POST['od_receipt_price']) ? clean_xss_tags($_POST['od_receipt_price'], 1, 1) : '';
$od_receipt_point = isset($_POST['od_receipt_point']) ? clean_xss_tags($_POST['od_receipt_point'], 1, 1) : '';
$od_receipt_coupon = isset($_POST['od_receipt_coupon']) ? clean_xss_tags($_POST['od_receipt_coupon'], 1, 1) : '';
$search = isset($_POST['search']) ? get_search_string($_POST['search']) : '';

for ($i=0; $i<$count_post_chk; $i++)
{
    // 실제 번호를 넘김
    $k     = isset($_POST['chk'][$i]) ? $_POST['chk'][$i] : 0;
    $od_id = isset($_POST['od_id'][$k]) ? safe_replace_regex($_POST['od_id'][$k], 'od_id') : '';

    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od) continue;

    // 주문상태가 주문이 아니면 건너뜀
    if($od['od_status'] != '주문') continue;

    $data = serialize($od);

    $sql = " insert {$g5['g5_shop_order_delete_table']} set de_key = '$od_id', de_data = '".addslashes($data)."', mb_id = '{$member['mb_id']}', de_ip = '{$_SERVER['REMOTE_ADDR']}', de_datetime = '".G5_TIME_YMDHIS."' ";
    sql_query($sql, true);

    // cart 테이블의 상품 상태를 삭제로 변경
    $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '삭제' where od_id = '$od_id' and ct_status = '주문' ";
    sql_query($sql);

    $sql = " delete from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    sql_query($sql);
}

$qstr  = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search";
$qstr .= "&amp;od_status=$search_od_status";
$qstr .= "&amp;od_settle_case=$od_settle_case";
$qstr .= "&amp;od_misu=$od_misu";
$qstr .= "&amp;od_cancel_price=$od_cancel_price";
$qstr .= "&amp;od_receipt_price=$od_receipt_price";
$qstr .= "&amp;od_receipt_point=$od_receipt_point";
$qstr .= "&amp;od_receipt_coupon=$od_receipt_coupon";

goto_url("./orderlist.php?$qstr");