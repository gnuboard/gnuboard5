<?php
include_once './_common.php';

@header('Progma:no-cache');
@header('Cache-Control:no-cache,must-revalidate');

if (!function_exists('sendRequest')) {
    function sendRequest($url, $authKey, $postData)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: $authKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

// $orderNumber = isset($_POST['orderNumber']) ? preg_replace("/[ #\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", '', strip_tags($_POST['orderNumber'])) : 0;
$orderNumber = isset($_REQUEST['customerKey']) ? preg_replace("/[ #\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", '', strip_tags($_REQUEST['customerKey'])) : 0;
$authKey = isset($_REQUEST['authKey']) ? clean_xss_tags($_REQUEST['authKey']) : '';
$session_order_num = get_session('subs_order_id');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if (!$orderNumber) {
    alert('주문번호가 없습니다.', G5_SUBSCRIPTION_URL);
}

if ($orderNumber !== $session_order_num) {
    alert('요청주문번호가 실제주문번호와 틀립니다.\n장바구니에서 다시 주문을 확인해 주세요.', G5_SUBSCRIPTION_URL);
}

$sql = "SELECT * 
        FROM {$g5['g5_subscription_order_data_table']} 
        WHERE od_id = '$orderNumber'";
$row = sql_fetch($sql);

$data = unserialize(base64_decode($row['dt_data']));

$params = array();
$save_forms = array();

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
        $save_forms[$key] = $value;

        $$key = $value;
    }
}

include_once G5_SUBSCRIPTION_PATH . '/orderformupdate.php';
