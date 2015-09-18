<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// curl 체크
if (!function_exists('curl_init')) {
    alert('cURL 모듈이 설치되어 있지 않습니다.\\n상점관리자에게 문의해 주십시오.');
}

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
        $useescrow = '&useescrow=Y';
    }
    else {
        // 일반결제 테스트
        $useescrow = '';
    }
}

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
    'ISP'    => '신용카드',
    'CARD'   => '신용카드',
    'BANK'   => '계좌이체',
    'MOBILE' => '휴대폰',
    'VBANK'  => '가상계좌'
);

$noti_url   = G5_MSHOP_URL.'/inicis/settle_common.php';
$next_url   = G5_MSHOP_URL.'/inicis/pay_approval.php';
$return_url = G5_MSHOP_URL.'/inicis/pay_return.php?oid=';
?>