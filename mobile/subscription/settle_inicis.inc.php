<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (get_subs_option('su_card_test')) {

    // 웹 결제 테스트 mid
    set_subs_option('su_inicis_mid', 'INIBillTst');
    // 웹 결제 테스트 signkey
    set_subs_option('su_inicis_sign_key', 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS');
    
    $merchantKey = "b09LVzhuTGZVaEY1WmJoQnZzdXpRdz09"; // 이니라이트키
    
    $inicis_iniapi_key = "rKnPljRn5m6J9Mzz";
    $inicis_iniapi_iv = "W2KLNKra6Wxc1P==";
    
    // 테스트 결제 URL
    $stdpay_js_url = 'https://stgstdpay.inicis.com/stdjs/INIStdPay.js';

} else {
    set_subs_option('su_inicis_mid', "SIR".get_subs_option('su_inicis_mid'));
    
    $merchantKey = '';
    
    $inicis_iniapi_key = get_subs_option('su_inicis_iniapi_key');
    $inicis_iniapi_iv = get_subs_option('su_inicis_iniapi_iv');
    
    // 실 결제 URL
    $stdpay_js_url = 'https://stdpay.inicis.com/stdjs/INIStdPay.js';
}
    
/**************************
 * 1. 라이브러리 인클루드 *
 **************************/
 
// INIStdPayUtil.php파일은 PC와 mobile 과 코드가 같음
require_once(G5_SUBSCRIPTION_PATH.'/inicis/libs/INIStdPayUtil.php');

$mid = get_subs_option('su_inicis_mid');
$signKey = get_subs_option('su_inicis_sign_key');

$SignatureUtil = new INIStdPayUtil();

$timestamp 		= $SignatureUtil->getTimestamp();   			// util에 의해서 자동생성

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
    '89' => '케이뱅크',
    '90' => '카카오뱅크',
    '92' => '토스뱅크',
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
    '25' => '해외다이너스',
    '93' => '토스머니',
    '94' => 'SSG머니',
    '97' => '카카오머니',
    '98' => '페이코'
);

$PAY_METHOD = array(
    'VCard'      => '신용카드',
    'Card'       => '신용카드',
    'DirectBank' => '계좌이체',
    'HPP'        => '휴대폰',
    'VBank'      => '가상계좌'
);