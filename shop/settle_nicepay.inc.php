<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($default['de_card_test']) {
    $default['de_nicepay_mid'] = 'nicepay00m';
    $default['de_nicepay_sign_key'] = 'EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg==';
    $default['de_nicepay_admin_key'] = '123456';
} else {
    $default['de_nicepay_mid'] = 'sir'.$default['de_nicepay_mid'];
}

// 일반(0), 에스크로(1)
$useescrow = "0";
if ($default['de_escrow_use'] == 1) {
    $useescrow = "1";
}

// 현금 영수증 발행 사용 여부 확인
// 기존 PG사들과의 동일한 구성을 위해 모듈내의 현금영수증 발행은 막음
$optionList = 'no_receipt';

// goodsCl에 실물여부에 관련된 값 적용 ( 1 : 실물, 0 : 컨텐츠 )
$goodsCl = '0';

require_once(G5_SHOP_PATH."/nicepay/lib/NicepayLite.php");

$mid = $default['de_nicepay_mid'];
$signKey = $default['de_nicepay_sign_key'];

$siteDomain = G5_SHOP_URL.'/nicepay';

$nicepay = new NicepayLite();

$nicepay->m_NicepayHome = G5_SHOP_PATH."/nicepay/log";
$nicepay->m_charSet = "UTF8";
$nicepay->m_ssl = "true";
$nicepay->m_log = "true";
$nicepay->m_MID = $mid;
$nicepay->m_MerchantKey = $signKey;
$nicepay->m_TransType = $useescrow;
$nicepay->requestProcess();

$PAY_METHOD = array(
    'CARD'          => '신용카드',
    'BANK'          => '계좌이체',
    'VBANK'         => '가상계좌',
    'CELLPHONE'     => '휴대폰',
);

?>