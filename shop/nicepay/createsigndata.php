<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$session_order_id = get_session('ss_order_id');
$personalpay_id = get_session('ss_personalpay_id');
$orderNumber = (isset($_POST['moid']) && !is_array($_POST['moid'])) ? addslashes(clean_xss_tags(stripslashes($_POST['moid']))) : '';
$price_checked = false;

if (!$orderNumber) {
    $orderNumber = $session_order_id;
}

if (!$orderNumber) {
    $orderNumber = $personalpay_id;
}

if (! ($default['de_pg_service'] == 'nicepay' && $orderNumber)){
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));
}

if ($session_order_id != $orderNumber && $personalpay_id != $orderNumber) {
    die(json_encode(array('error'=>'주문 정보가 올바르지 않습니다.')));
}

$price = (isset($_POST['price']) && !is_array($_POST['price'])) ? preg_replace('#[^0-9]#', '', $_POST['price']) : '';

if (strlen($price) < 1) {
    die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
}

if ($orderNumber) {
    $sql = " select dt_data from {$g5['g5_shop_order_data_table']} where od_id = '$orderNumber' and dt_pg = 'nicepay' ";
    $row = sql_fetch($sql);

    if (isset($row['dt_data']) && $row['dt_data']) {
        $order_data = unserialize(base64_decode($row['dt_data']));
        $order_price = (isset($order_data['good_mny'])) ? preg_replace('#[^0-9]#', '', $order_data['good_mny']) : '';

        if (strlen($order_price) < 1 || (int)$order_price !== (int)$price) {
            die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
        }

        $price_checked = true;
    }
}

if (!$price_checked && $personalpay_id == $orderNumber) {
    $pp_id = preg_replace('/[^0-9]/', '', $personalpay_id);
    $pp = sql_fetch(" select pp_id, pp_price from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' and pp_use = '1' ");

    if (! (isset($pp['pp_id']) && $pp['pp_id']) || (int)$pp['pp_price'] !== (int)$price) {
        die(json_encode(array('error'=>'가격이 올바르지 않습니다.')));
    }

    $price_checked = true;
}

if (!$price_checked) {
    die(json_encode(array('error'=>'주문 정보가 올바르지 않습니다.')));
}

$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$hashString = bin2hex(hash('sha256', $ediDate.$default['de_nicepay_mid'].$price.$default['de_nicepay_key'], true));

die(json_encode(array('error'=>'', 'ediDate'=>$ediDate, 'SignData'=>$hashString)));
