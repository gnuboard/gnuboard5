<?php
$sub_menu = '400620';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$count_post_it_id = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_ca_id = isset($_REQUEST['sel_ca_id']) ? clean_xss_tags($_REQUEST['sel_ca_id'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

// 재고 일괄수정
for ($i=0; $i<$count_post_it_id; $i++)
{
    $it_stock_qty = isset($_POST['it_stock_qty'][$i]) ? (int) $_POST['it_stock_qty'][$i] : 0;
    $it_noti_qty = isset($_POST['it_noti_qty'][$i]) ? (int) $_POST['it_noti_qty'][$i] : 0;
    $it_use = isset($_POST['it_use'][$i]) ? (int) $_POST['it_use'][$i] : 0;
    $it_soldout = isset($_POST['it_soldout'][$i]) ? (int) $_POST['it_soldout'][$i] : 0;
    $it_stock_sms = isset($_POST['it_stock_sms'][$i]) ? (int) $_POST['it_stock_sms'][$i] : 0;
    $it_id = isset($_POST['it_id'][$i]) ? safe_replace_regex($_POST['it_id'][$i], 'it_id') : '';

    $sql = "update {$g5['g5_shop_item_table']}
               set it_stock_qty    = '".$it_stock_qty."',
                   it_noti_qty     = '".$it_noti_qty."',
                   it_use          = '".$it_use."',
                   it_soldout      = '".$it_soldout."',
                   it_stock_sms    = '".$it_stock_sms."'
             where it_id = '".$it_id."' ";
    sql_query($sql);
}

goto_url("./itemstocklist.php?sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page");