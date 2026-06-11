<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$orderNumber = get_session('ss_order_id');
$is_personalpay_order = false;

if (! $orderNumber) {
    $orderNumber = get_session('ss_personalpay_id');
    $is_personalpay_order = true;
}

if (! ($default['de_pg_service'] == 'nicepay' && $orderNumber)){
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));
}

$price = isset($_POST['price']) ? preg_replace('#[^0-9]#', '', $_POST['price']) : '';

if (strlen($price) < 1) {
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
}

if ($is_personalpay_order) {
    $pp_id = preg_replace('/[^0-9]/', '', get_session('ss_personalpay_id'));
    $pp = sql_fetch(" select pp_id, pp_price from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' and pp_use = '1' ");

    if (! (isset($pp['pp_id']) && $pp['pp_id']) || (int)$pp['pp_price'] !== (int)$price) {
        die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
    }
}

$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$hashString = bin2hex(hash('sha256', $ediDate.$default['de_nicepay_mid'].$price.$default['de_nicepay_key'], true));

die(json_encode(array('error'=>'', 'ediDate'=>$ediDate, 'SignData'=>$hashString)));
