<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once(G5_LIB_PATH.'/shop_order_access.lib.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if($default['de_pg_service'] != 'inicis' && ! ($default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use']) )
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));

$orderNumber = isset($_POST['oid']) && is_string($_POST['oid']) ? $_POST['oid'] : '';
$price = isset($_POST['price']) && is_string($_POST['price']) ? $_POST['price'] : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !shop_order_access_id($orderNumber) || $signKey === ''
    || !preg_match('/\A[0-9]{1,12}\z/', $price) || (int) $price <= 0)
    die(json_encode(array('error'=>'결제 서명 요청을 확인할 수 없습니다.')));
if (function_exists('check_request_origin')) check_request_origin(G5_SHOP_URL);

// 현재 폼의 주문번호를 저장된 주문 상태와 대조한다. 다른 탭의 주문을 서명하지 않는다.
$states = get_session('ss_order_data_access');
if (!is_array($states) || !isset($states[$orderNumber]['pg']))
    die(json_encode(array('error'=>'임시 주문정보를 확인할 수 없습니다.')));
$data = shop_order_access_load($orderNumber, $states[$orderNumber]['pg']);
$settle_case = !empty($data['pp_id']) ? $data['pp_settle_case'] : $data['od_settle_case'];
if (!inicis_pro_easypay_is_enabled($settle_case)
    || ($default['de_pg_service'] !== 'inicis' && !is_inicis_order_pay($settle_case)))
    die(json_encode(array('error'=>'사용할 수 없는 결제수단입니다.')));
$amount = inicis_pro_expected_amount($data, array());
if ($amount <= 0 || (string) $amount !== $price)
    die(json_encode(array('error'=>'결제금액이 일치하지 않습니다. 다시 확인해 주십시오.')));

//
//###################################
// 2. 가맹점 확인을 위한 signKey를 해시값으로 변경 (SHA-256방식 사용)
//###################################
$mKey = hash("sha256", $signKey);

/*
  //*** 위변조 방지체크를 signature 생성 ***
  oid, price, timestamp 3개의 키와 값을
  key=value 형식으로 하여 '&'로 연결한 하여 SHA-256 Hash로 생성 된값
  ex) oid=INIpayTest_1432813606995&price=819000&timestamp=2012-02-01 09:19:04.004
 * key기준 알파벳 정렬
 * timestamp는 반드시 signature생성에 사용한 timestamp 값을 timestamp input에 그대로 사용하여야함
 */
$params = "oid=" . $orderNumber . "&price=" . $price . "&timestamp=" . $timestamp;
$sign = hash("sha256", $params);
$verification = hash('sha256', 'oid='.$orderNumber.'&price='.$price.'&signKey='.$signKey.'&timestamp='.$timestamp);

die(json_encode(array('error'=>'', 'mKey'=>$mKey, 'timestamp'=>$timestamp, 'sign'=>$sign, 'verification'=>$verification)));
