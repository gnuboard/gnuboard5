<?php
include_once('./_common.php');
include(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');

// 카카오페이를 사용하지 않을 경우
if (!$default['de_kakaopay_enckey']) {
    die('카카오페이를 사용하지 않습니다.');
}

if (!($default['de_kakaopay_mid'] && $default['de_kakaopay_key'])) {
    die(json_encode(array('error' => '올바른 방법으로 이용해 주십시오.')));
}

$orderNumber = get_session('ss_order_id');
$price = preg_replace('#[^0-9]#', '', $_POST['price']);

if (strlen($price) < 1) {
    die(json_encode(array('error' => '가격이 올바르지 않습니다.')));
}

//
//###################################
// 2. 가맹점 확인을 위한 signKey를 해시값으로 변경 (SHA-256방식 사용)
//###################################
$mKey = hash("sha256", $default['de_kakaopay_key']);

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

die(json_encode(array('error'=>'', 'mKey'=>$mKey, 'timestamp'=>$timestamp, 'sign'=>$sign)));
