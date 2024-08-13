<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// curl 체크
if (!function_exists('curl_init')) {
    alert('cURL 모듈이 설치되어 있지 않습니다.\\n상점관리자에게 문의해 주십시오.');
}

// 테스트이면
if (get_subs_option('su_card_test')) {
    $nicepay_clientid = 'S2_af4543a0be4d49a98122e01ec2059a56';
    $nicepay_secretkey = '9eb85607103646da9f9c02b128f2e5ee';
} else {
    // 실 사용이면
    // $nicepay_clientid = 'SR_'.get_subs_option('su_nice_clientid');
    $nicepay_clientid = get_subs_option('su_nice_clientid');
    $nicepay_secretkey = get_subs_option('su_nicepay_secretkey');
}

$returnUrl = G5_SUBSCRIPTION_URL.'/nicepay/nicepay_returnurl.php';
