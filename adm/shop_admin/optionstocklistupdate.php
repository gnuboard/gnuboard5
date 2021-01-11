<?php
$sub_menu = '400500';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$count_post_it_id = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';
$sel_ca_id = isset($_REQUEST['sel_ca_id']) ? clean_xss_tags($_REQUEST['sel_ca_id'], 1, 1) : '';

// 재고 일괄수정
for ($i=0; $i<$count_post_it_id; $i++)
{
    $io_stock_qty = isset($_POST['io_stock_qty'][$i]) ? (int) $_POST['io_stock_qty'][$i] : 0;
    $io_noti_qty = isset($_POST['io_noti_qty'][$i]) ? (int) $_POST['io_noti_qty'][$i] : 0;
    $io_use = isset($_POST['io_use'][$i]) ? (int) $_POST['io_use'][$i] : 0;
    $it_id = isset($_POST['it_id'][$i]) ? safe_replace_regex($_POST['it_id'][$i], 'it_id') : '';
    $io_id = isset($_POST['io_id'][$i]) ? preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$i]) : '';
    $io_type = isset($_POST['io_type'][$i]) ? (int) $_POST['io_type'][$i] : 0;

    $sql = "update {$g5['g5_shop_item_option_table']}
               set io_stock_qty    = '".$io_stock_qty."',
                   io_noti_qty     = '".$io_noti_qty."',
                   io_use = '".$io_use."'
             where it_id = '".$it_id."'
               and io_id = '".sql_real_escape_string($io_id)."'
               and io_type = '".$io_type."' ";
    sql_query($sql);
}

goto_url("./optionstocklist.php?sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page");