<?php
include_once('./_common.php');
include_once(G5_SUBSCRIPTION_PATH.'/settle_inicis.inc.php');

if(get_subs_option('su_pg_service') != 'inicis')
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));

$orderNumber = get_session('subs_order_id');
$price = preg_replace('#[^0-9]#', '', $_POST['price']);

if(strlen($price) < 1)
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));

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

$params = array(
    "oid" => $orderNumber,
    "price" => $price,
    "timestamp" => $timestamp
);

$sign   = $SignatureUtil->makeSignature($params);

$params = array(
    "oid" => $orderNumber,
    "price" => $price,
    "signKey" => $signKey,
    "timestamp" => $timestamp
);

$sign2   = $SignatureUtil->makeSignature($params);

die(json_encode(array('error'=>'', 'mKey'=>$mKey, 'timestamp'=>$timestamp, 'sign'=>$sign, 'sign2'=>$sign2)));
