<?php
$sub_menu = '600410';
include_once('./_common.php');

check_demo();

check_admin_token();

auth_check_menu($auth, $sub_menu, "w");

$py_subscription_memo = isset($_POST['py_subscription_memo']) ? strip_tags($_POST['py_subscription_memo']) : '';
$pay_id = isset($_POST['pay_id']) ? clean_xss_tags($_POST['pay_id'], 1, 1) : '';

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$is_memo_update = false;

if(isset($_POST['mpy_type']) && $_POST['mpy_type'] === 'info') {
    $py_b_zip = isset($_POST['py_b_zip']) ? preg_replace('/[^0-9]/', '', substr($_POST['py_b_zip'], 0, 6)) : '';

    $py_b_name = isset($_POST['py_b_name']) ? clean_xss_tags($_POST['py_b_name'], 1, 1) : '';
    $py_b_tel = isset($_POST['py_b_tel']) ? clean_xss_tags($_POST['py_b_tel'], 1, 1) : '';
    $py_b_hp = isset($_POST['py_b_hp']) ? clean_xss_tags($_POST['py_b_hp'], 1, 1) : '';
    $py_b_addr1 = isset($_POST['py_b_addr1']) ? clean_xss_tags($_POST['py_b_addr1'], 1, 1) : '';
    $py_b_addr2 = isset($_POST['py_b_addr2']) ? clean_xss_tags($_POST['py_b_addr2'], 1, 1) : '';
    $py_b_addr3 = isset($_POST['py_b_addr3']) ? clean_xss_tags($_POST['py_b_addr3'], 1, 1) : '';
    $py_b_addr_jibeon = isset($_POST['py_b_addr_jibeon']) ? clean_xss_tags($_POST['py_b_addr_jibeon'], 1, 1) : '';
    $py_hope_date = isset($_POST['py_hope_date']) ? clean_xss_tags($_POST['py_hope_date'], 1, 1) : '';

    $sql = " update {$g5['g5_subscription_pay_table']}
                set py_b_name = '$py_b_name',
                    py_b_tel = '$py_b_tel',
                    py_b_hp = '$py_b_hp',
                    py_b_zip = '$py_b_zip',
                    py_b_addr1 = '$py_b_addr1',
                    py_b_addr2 = '$py_b_addr2',
                    py_b_addr3 = '$py_b_addr3',
                    py_b_addr_jibeon = '$py_b_addr_jibeon' ";

} else {
    $sql = "update {$g5['g5_subscription_pay_table']}
                set py_subscription_memo = '$py_subscription_memo' ";
                
    $is_memo_update = true;
}
$sql .= " where pay_id = '$pay_id' ";

sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

if ($is_memo_update) {
    $qstr .= '#anc_sodr_memo';
}

goto_url("./payform.php?pay_id=$pay_id&amp;$qstr");