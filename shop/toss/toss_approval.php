<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/shop_order_access.lib.php');

$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$paymentKey = isset($_REQUEST['paymentKey']) ? $_REQUEST['paymentKey'] : '';
$amount = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
shop_order_access_payment($orderId, $paymentKey, $amount);
