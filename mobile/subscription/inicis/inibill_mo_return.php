<?php
include_once('./_common.php');
include_once(G5_MSUBSCRIPTION_PATH.'/settle_inicis.inc.php');

@header('Progma:no-cache');
@header('Cache-Control:no-cache,must-revalidate');

// print_r2($_POST);

$orderid = isset($_REQUEST['orderid']) ? preg_replace('/[^0-9a-z_\-]/i', '', $_REQUEST['orderid']) : '';
$request_mid = isset($_POST['mid']) ? clean_xss_tags($_POST['mid']) : null;
$resultcode = isset($_POST['resultcode']) ? clean_xss_tags($_POST['resultcode']) : '';
$resultmsg = isset($_POST['resultmsg']) ? clean_xss_tags($_POST['resultmsg']) : '';

if (!$orderid) {
    alert('주문번호가 없습니다.', G5_SHOP_URL);
}

if ($request_mid !== get_subs_option('su_inicis_mid')) {
    alert('요청된 mid 와 설정된 mid 가 틀립니다.', G5_SHOP_URL);
}

if ($resultcode !== '00') {
    die('실패되었습니다. 이유 : '. $resultmsg);
}

$sql = " select * from {$g5['g5_subscription_order_data_table']} where od_id = '$orderid' ";
$row = sql_fetch($sql);

if (empty($row)) {
    alert('임시 주문정보가 저장되지 않았습니다.', G5_SHOP_URL);
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

include_once G5_MSUBSCRIPTION_PATH.'/orderformupdate.php';