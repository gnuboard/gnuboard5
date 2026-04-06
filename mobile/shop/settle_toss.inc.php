<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

// var_dump($default['cf_toss_client_key']);

$toss = new TossPayments(
    $config['cf_toss_client_key'],
    $config['cf_toss_secret_key'],
    $config['cf_lg_mid']
);

$toss->setPaymentHeader();
?>