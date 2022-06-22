<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

if($default['de_pg_service'] != 'inicis' && ! ($default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use']) )
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));

$orderNumber = get_session('ss_order_inicis_id');
$price = preg_replace('#[^0-9]#', '', $_POST['price']);

if(strlen($price) < 1)
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));

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

die(json_encode(array('error'=>'', 'mKey'=>$mKey, 'timestamp'=>$timestamp, 'sign'=>$sign)));
