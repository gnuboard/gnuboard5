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


$nicepay->m_NicepayHome = G5_SHOP_PATH."/nicepay/log";      // 로그디렉토리 설정
$nicepay->m_charSet = "UTF8";                               // 내부처리 언어셋 설정 (설정된 언어셋은 lib를 통하여, request data & result data의 언어셋을 변경시킴)
$nicepay->m_ssl = "true";                                   // ssl 사용 여부
$nicepay->m_log = "true";                                   // 로그기록 사용 여부
$nicepay->m_MID = $mid;                                     // 상점아이디
$nicepay->m_MerchantKey = $signKey;                         // 상점키
$nicepay->m_TransType = $useescrow;                         // 에스크로 사용여부

// 나이스페이 사용을 위한 초기화
$nicepay->requestProcess();

$PAY_METHOD = array(
    'CARD'          => '신용카드',
    'BANK'          => '계좌이체',
    'VBANK'         => '가상계좌',
    'CELLPHONE'     => '휴대폰',
);

?>