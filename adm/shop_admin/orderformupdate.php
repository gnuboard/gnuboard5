<?php
$sub_menu = '400400';
include_once('./_common.php');

check_admin_token();

$od_shop_memo = isset($_POST['od_shop_memo']) ? addslashes(strip_tags(stripslashes($_POST['od_shop_memo']))) : '';
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

if(isset($_POST['mod_type']) && $_POST['mod_type'] === 'info') {
    $od_zip1   = isset($_POST['od_zip']) ? preg_replace('/[^0-9]/', '', substr($_POST['od_zip'], 0, 3)) : '';
    $od_zip2   = isset($_POST['od_zip']) ? preg_replace('/[^0-9]/', '', substr($_POST['od_zip'], 3)) : '';
    $od_b_zip1 = isset($_POST['od_b_zip']) ? preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 0, 3)) : '';
    $od_b_zip2 = isset($_POST['od_b_zip']) ? preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 3)) : '';
    $od_email = isset($_POST['od_email']) ? addslashes(strip_tags(clean_xss_attributes(stripslashes($_POST['od_email'])))) : '';
    $od_name = isset($_POST['od_name']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_name']), 1, 1)) : '';
    $od_tel = isset($_POST['od_tel']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_tel']), 1, 1)) : '';
    $od_hp = isset($_POST['od_hp']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_hp']), 1, 1)) : '';
    $od_addr1 = isset($_POST['od_addr1']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_addr1']), 1, 1)) : '';
    $od_addr2 = isset($_POST['od_addr2']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_addr2']), 1, 1)) : '';
    $od_addr3 = isset($_POST['od_addr3']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_addr3']), 1, 1)) : '';
    $od_addr_jibeon = isset($_POST['od_addr_jibeon']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_addr_jibeon']), 1, 1)) : '';
    $od_b_name = isset($_POST['od_b_name']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_name']), 1, 1)) : '';
    $od_b_tel = isset($_POST['od_b_tel']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_tel']), 1, 1)) : '';
    $od_b_hp = isset($_POST['od_b_hp']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_hp']), 1, 1)) : '';
    $od_b_addr1 = isset($_POST['od_b_addr1']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_addr1']), 1, 1)) : '';
    $od_b_addr2 = isset($_POST['od_b_addr2']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_addr2']), 1, 1)) : '';
    $od_b_addr3 = isset($_POST['od_b_addr3']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_addr3']), 1, 1)) : '';
    $od_b_addr_jibeon = isset($_POST['od_b_addr_jibeon']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_b_addr_jibeon']), 1, 1)) : '';
    $od_hope_date = isset($_POST['od_hope_date']) ? addslashes(clean_xss_tags(stripslashes($_POST['od_hope_date']), 1, 1)) : '';

    $sql = " update {$g5['g5_shop_order_table']}
                set od_name = '$od_name',
                    od_tel = '$od_tel',
                    od_hp = '$od_hp',
                    od_zip1 = '$od_zip1',
                    od_zip2 = '$od_zip2',
                    od_addr1 = '$od_addr1',
                    od_addr2 = '$od_addr2',
                    od_addr3 = '$od_addr3',
                    od_addr_jibeon = '$od_addr_jibeon',
                    od_email = '$od_email',
                    od_b_name = '$od_b_name',
                    od_b_tel = '$od_b_tel',
                    od_b_hp = '$od_b_hp',
                    od_b_zip1 = '$od_b_zip1',
                    od_b_zip2 = '$od_b_zip2',
                    od_b_addr1 = '$od_b_addr1',
                    od_b_addr2 = '$od_b_addr2',
                    od_b_addr3 = '$od_b_addr3',
                    od_b_addr_jibeon = '$od_b_addr_jibeon' ";

    if ($default['de_hope_date_use'])
        $sql .= " , od_hope_date = '$od_hope_date' ";
} else {
    $sql = "update {$g5['g5_shop_order_table']}
                set od_shop_memo = '$od_shop_memo' ";
}
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
