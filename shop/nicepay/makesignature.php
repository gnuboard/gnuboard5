<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

if($default['de_pg_service'] != 'nicepay')
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));

$price = preg_replace('#[^0-9]#', '', $_POST['price']);

$ediDate = $_POST['ediDate'];

if(strlen($price) < 1)
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));

/*
  //*** 위변조 방지체크를 signature 생성 ***
  ediDate, mid, price, signkey 4개의 값을 연결하여 SHA-256 Hash로 값 생성
*/
$sign = bin2hex(hash("sha256", $ediDate.$mid.$price.$signKey, true));

die(json_encode(array('error'=>'', 'EncryptData'=> $sign)));