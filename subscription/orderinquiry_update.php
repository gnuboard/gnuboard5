<?php
include_once('./_common.php');

// print_r2($_POST);
// exit;

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : 0;
$uid = isset($_POST['uid']) ? clean_xss_tags($_POST['uid']) : 0;

$is_select_memeber = ($is_admin === 'super') ? 0 : 1;

$od = get_subscription_order($od_id, $is_select_memeber);

if (!(isset($od['od_id']) && $od['od_id'])) {
    alert('주문정보가 없거나 권한이 없습니다.', G5_SHOP_URL);
}

$od_b_name        = isset($_POST['od_b_name']) ? clean_xss_tags($_POST['od_b_name']) : '';
$od_b_tel         = isset($_POST['od_b_tel']) ? clean_xss_tags($_POST['od_b_tel']) : '';
$od_b_hp          = isset($_POST['od_b_hp']) ? clean_xss_tags($_POST['od_b_hp']) : '';
$od_b_zip		  = isset($_POST['od_b_zip']) ? preg_replace('/[^0-9]/', '', $_POST['od_b_zip']) : '';
$od_b_addr1       = isset($_POST['od_b_addr1']) ? clean_xss_tags($_POST['od_b_addr1']) : '';
$od_b_addr2       = isset($_POST['od_b_addr2']) ? clean_xss_tags($_POST['od_b_addr2']) : '';
$od_b_addr3       = isset($_POST['od_b_addr3']) ? clean_xss_tags($_POST['od_b_addr3']) : '';
$od_b_addr_jibeon = (isset($_POST['od_b_addr_jibeon']) && preg_match("/^(N|R)$/", $_POST['od_b_addr_jibeon'])) ? $_POST['od_b_addr_jibeon'] : '';
$od_memo          = isset($_POST['od_memo']) ? clean_xss_tags($_POST['od_memo'], 1, 1, 0, 0) : '';

$sql = "update {$g5['g5_subscription_order_table']} set 
od_b_name = '{$od_b_name}',
od_b_tel = '{$od_b_tel}',
od_b_hp = '{$od_b_hp}',
od_b_zip = '{$od_b_zip}',
od_b_addr1 = '{$od_b_addr1}',
od_b_addr2 = '{$od_b_addr2}',
od_b_addr3 = '{$od_b_addr3}',
od_b_addr_jibeon = '{$od_b_addr_jibeon}',
od_memo = '{$od_memo}'
where od_id = '".$od['od_id']."' ";

$result = sql_query($sql);

goto_url(G5_SUBSCRIPTION_URL . '/orderinquiryview.php?od_id=' . $od_id . '&amp;uid=' . $uid .'#sod_fin_receiver');
