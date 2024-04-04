<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// curl 체크
if (!function_exists('curl_init')) {
    alert('cURL 모듈이 설치되어 있지 않습니다.\\n상점관리자에게 문의해 주십시오.');
}

if ($default['de_card_test']) {
    // 테스트인 경우
    $default['de_nicepay_mid'] = 'nicepay00m';
    // $default['de_nicepay_key'] = '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==';
    $default['de_nicepay_key'] = 'EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg==';

    // 나이스 카카오페이 간편결제 직접 호출 테스트 아이디
    // $default['de_nicepay_mid'] = 'nickakao1m';
    // $default['de_nicepay_key'] = 'A2SY4ztPs6LPymgFl/5bbsLuINyvgKq5eOdDSHb31gdO4dfGr3O6hBxvRp9oXdat45VninNUySc7E/5UT01vKw==';
    
    // 나이스 네이버페이 간편결제 직접 호출 테스트 아이디
    // $default['de_nicepay_mid'] = 'nicnaver0m';
    // $default['de_nicepay_key'] = 'kNuUIpYvHPGcTTlmRsFddsqp6P9JoTcEcoRB1pindAwCZ0oySNuCQX5Zv483XTU5UuRiy/VYZ9BXw1BRvEUYMg==';

    // 나이스 삼성페이 간편결제 직접 호출 테스트 아이디
    // $default['de_nicepay_mid'] = 'nicessp06m';
    // $default['de_nicepay_key'] = '+iz0ov8wQDjOQ73GhO6QJ/kXF041yRiS+ERc3rD36Oe62onynMp0u0+ZvmKcBw2EKd2LlRcxJqbHBz313h0aJg==';

    // 나이스 애플페이 간편결제 직접 호출 테스트 아이디 (애플페이는 오직 모바일에서만 됩니다)
    // $default['de_nicepay_mid'] = 'nicapple1m';
    // $default['de_nicepay_key'] = 'dBJ0hrJ8HtFpWHYxuyC3nRkBaEA3AXUfzZxfbRHKgrMipQmcY2m0Ga4qG0jz92VJe7BL5tK0qSVSloowdXrtqg==';

} else {
    // 실결제인 경우
    $default['de_nicepay_mid'] = "SR".$default['de_nicepay_mid'];
}

// 개인결제인지 아니면 쇼핑몰 일반결제인지 returnURL이 서로 다름
$nicepay_returnURL = ((isset($pp['pp_id']) && $pp['pp_id'])) ? G5_SHOP_URL.'/personalpayformupdate.php' : G5_SHOP_URL.'/orderformupdate.php';

// $ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
// $hashString = bin2hex(hash('sha256', $ediDate.$default['de_nicepay_mid'].$price.$default['de_nicepay_key'], true));

$NICEPAY_METHOD = array(
    'CARD'  => '신용카드',
    'BANK'  => '계좌이체',
    'VBANK' => '가상계좌',
    'CELLPHONE' => '휴대폰'
);

if (! function_exists('nicepay_reqPost')) {
    //Post api call
    function nicepay_reqPost($data, $url){
        $url_data = parse_url($url);

        // 나이스페이 url이 맞는지 체크하여 틀리면 false를 리턴합니다.
        if (! (isset($url_data['host']) && preg_match('#\.nicepay\.co\.kr$#i', $url_data['host']))) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);					//connection timeout 15 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));	//POST data
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        curl_close($ch);	 
        return $response;
    }
}