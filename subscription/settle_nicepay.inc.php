<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// curl 체크
if (!function_exists('curl_init')) {
    alert('cURL 모듈이 설치되어 있지 않습니다.\\n상점관리자에게 문의해 주십시오.');
}

// 테스트이면
if (get_subs_option('su_card_test')) {
    $nicepay_clientid = get_subs_option('su_nice_clientid') ? get_subs_option('su_nice_clientid') : 'S2_af4543a0be4d49a98122e01ec2059a56';
    $nicepay_secretkey = get_subs_option('su_nice_secretkey') ? get_subs_option('su_nice_secretkey') : '9eb85607103646da9f9c02b128f2e5ee';
    
    set_subs_option('su_nice_clientid', $nicepay_clientid);
    set_subs_option('su_nice_secretkey', $nicepay_secretkey);
    
    set_subs_option('su_nicepay_mid', 'nictest04m');
    set_subs_option('su_nicepay_key', 'b+zhZ4yOZ7FsH8pm5lhDfHZEb79tIwnjsdA0FBXh86yLc6BJeFVrZFXhAoJ3gEWgrWwN+lJMV0W4hvDdbe4Sjw==');
    
} else {
    // 실 사용이면
    // $nicepay_clientid = 'SR_'.get_subs_option('su_nice_clientid');
    $nicepay_clientid = get_subs_option('su_nice_clientid');
    $nicepay_secretkey = get_subs_option('su_nice_secretkey');
    
}

if (!function_exists('requestPost')) {
    //CURL: Basic auth, json, post
    function requestPost($url, $json, $userpwd)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }
}

$returnUrl = G5_SUBSCRIPTION_URL.'/nicepay/nicepay_returnurl.php';