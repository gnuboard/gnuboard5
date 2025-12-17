<?php
include_once './_common.php';
include_once G5_SUBSCRIPTION_PATH.'/settle_inicis.inc.php';

@header('Progma:no-cache');
@header('Cache-Control:no-cache,must-revalidate');

$resultCode = isset($_POST['resultCode']) ? clean_xss_tags($_POST['resultCode']) : null;
$request_mid = isset($_POST['mid']) ? clean_xss_tags($_POST['mid']) : null;
$orderNumber = isset($_POST['orderNumber']) ? preg_replace("/[ #\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", '', strip_tags($_POST['orderNumber'])) : 0;
$session_order_num = get_session('subs_order_id');

if (!$orderNumber) {
    alert('주문번호가 없습니다.');
}

$authToken = isset($_POST['authToken']) ? clean_xss_tags($_POST['authToken']) : '';
$authUrl = isset($_POST['authUrl']) ? clean_xss_tags($_POST['authUrl']) : '';
$netCancel = isset($_POST['netCancelUrl']) ? clean_xss_tags($_POST['netCancelUrl']) : '';
$merchantData = isset($_POST['merchantData']) ? clean_xss_tags($_POST['merchantData']) : '';

if ($request_mid != get_subs_option('su_inicis_mid')) {
    alert('요청된 mid 와 설정된 mid 가 틀립니다.', G5_URL);
}

if (!($resultCode && $session_order_num && $authToken && $authUrl && $netCancel)) {
    alert('잘못된 요청입니다.', G5_URL);
}

$sql = " select * from {$g5['g5_subscription_order_data_table']} where od_id = '$orderNumber' ";
$row = sql_fetch($sql);

if (empty($row)) {
    alert('임시 주문정보가 저장되지 않았습니다.', G5_URL);
}

$data = unserialize(base64_decode($row['dt_data']));

$params = array();

foreach ($data as $key => $value) {
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $_POST[$key][$k] = $params[$key][$k] = clean_xss_tags(strip_tags($v));
        }
    } else {
        if (in_array($key, ['od_memo'])) {
            $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value), 0, 0, 0, 0);
        } else {
            $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value));
        }
    }
}

foreach ($params as $key => $value) {
    if (in_array($key, ['od_price', 'od_name', 'od_tel', 'od_hp', 'od_email', 'od_memo', 'od_settle_case', 'max_temp_point', 'od_temp_point', 'od_bank_account', 'od_deposit_name', 'od_test', 'od_ip', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon', 'od_b_zip', 'od_send_cost', 'od_send_cost2', 'od_hope_date'])) {
        $var_datas[$key] = $value;

        $$key = $value;
    }
}

include_once G5_SUBSCRIPTION_PATH.'/orderformupdate.php';
