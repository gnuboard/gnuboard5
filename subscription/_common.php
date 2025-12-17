<?php
include_once '../common.php';
// 쇼핑몰 설정을 그대로 따른다.
include_once G5_SHOP_PATH.'/_common.php';

if (!(defined('G5_USE_SUBSCRIPTION') && G5_USE_SUBSCRIPTION)) {
    exit('<p>정기결제 프로그램을 설치 후 이용해 주십시오.</p>');
}

if (!defined('_SUBSCRIPTION_')) define('_SUBSCRIPTION_', true);
if (!defined('_SUBSCRIPTION_')) define('_SUBSCRIPTION_COMMON_', true); // 모바일 페이지의 직접 접근을 막는 경우에 사용

if ($is_mobile) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/mobile_subscription.css">', 1);
} else {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/subscription.css">', 1);
}