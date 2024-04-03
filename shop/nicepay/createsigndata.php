<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$orderNumber = get_session('ss_order_id');

if (! $orderNumber) {
    $orderNumber = get_session('ss_personalpay_id');
}

if (! ($default['de_pg_service'] == 'nicepay' && $orderNumber)){
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));
}

if (function_exists('add_log')) add_log($_POST, false, 'ajax');

$price = preg_replace('#[^0-9]#', '', $_POST['price']);

if (strlen($price) < 1) {
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
}

$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$hashString = bin2hex(hash('sha256', $ediDate.$default['de_nicepay_mid'].$price.$default['de_nicepay_key'], true));

die(json_encode(array('error'=>'', 'ediDate'=>$ediDate, 'SignData'=>$hashString)));