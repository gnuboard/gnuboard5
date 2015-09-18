<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($default['de_card_test']) {
    if ($default['de_escrow_use'] == 1) {
        // 에스크로결제 테스트
        $default['de_inicis_mid'] = 'iniescrow0';
        $default['de_inicis_admin_key'] = '1111';
    }
    else {
        // 일반결제 테스트
        $default['de_inicis_mid'] = 'INIpayTest';
        $default['de_inicis_admin_key'] = '1111';
    }
}
else {
    $default['de_inicis_mid'] = "SIR".$default['de_inicis_mid'];

    if ($default['de_escrow_use'] == 1) {
        // 에스크로결제 테스트
        $useescrow = ':useescrow';
    }
    else {
        // 일반결제 테스트
        $useescrow = '';
    }
}

/**************************
 * 1. 라이브러리 인클루드 *
 **************************/
require(G5_SHOP_PATH.'/inicis/libs/INILib.php');

/***************************************
 * 2. INIpay50 클래스의 인스턴스 생성  *
 ***************************************/
$inipay = new INIpay50;

$inipay->SetField("inipayhome", G5_SHOP_PATH.'/inicis'); // 이니페이 홈디렉터리(상점수정 필요)
$inipay->SetField("debug", "false");                     // 로그모드("true"로 설정하면 상세로그가 생성됨.)

$inipay_nointerest = 'no'; //무이자여부(no:일반, yes:무이자)
$inipay_quotabase  = '선택:일시불:2개월:3개월:4개월:5개월:6개월:7개월:8개월:9개월:10개월:11개월:12개월'; // 할부기간

$BANK_CODE = array(
    '03' => '기업은행',
    '04' => '국민은행',
    '05' => '외환은행',
    '07' => '수협중앙회',
    '11' => '농협중앙회',
    '20' => '우리은행',
    '23' => 'SC 제일은행',
    '31' => '대구은행',
    '32' => '부산은행',
    '34' => '광주은행',
    '37' => '전북은행',
    '39' => '경남은행',
    '53' => '한국씨티은행',
    '71' => '우체국',
    '81' => '하나은행',
    '88' => '신한은행',
    'D1' => '동양종합금융증권',
    'D2' => '현대증권',
    'D3' => '미래에셋증권',
    'D4' => '한국투자증권',
    'D5' => '우리투자증권',
    'D6' => '하이투자증권',
    'D7' => 'HMC 투자증권',
    'D8' => 'SK 증권',
    'D9' => '대신증권',
    'DA' => '하나대투증권',
    'DB' => '굿모닝신한증권',
    'DC' => '동부증권',
    'DD' => '유진투자증권',
    'DE' => '메리츠증권',
    'DF' => '신영증권'
);

$CARD_CODE = array(
    '01' => '외환',
    '03' => '롯데',
    '04' => '현대',
    '06' => '국민',
    '11' => 'BC',
    '12' => '삼성',
    '14' => '신한',
    '15' => '한미',
    '16' => 'NH',
    '17' => '하나 SK',
    '21' => '해외비자',
    '22' => '해외마스터',
    '23' => 'JCB',
    '24' => '해외아멕스',
    '25' => '해외다이너스'
);

$PAY_METHOD = array(
    'VCard'      => '신용카드',
    'Card'       => '신용카드',
    'DirectBank' => '계좌이체',
    'HPP'        => '휴대폰',
    'VBank'      => '가상계좌'
);

// 플러그인 호출 URL
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
    $ini_js_url = 'https://plugin.inicis.com/pay61_secunissl_cross.js';
} else {
    $ini_js_url = 'http://plugin.inicis.com/pay61_secuni_cross.js';
}
?>