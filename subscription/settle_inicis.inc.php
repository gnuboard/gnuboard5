<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (get_subs_option('su_card_test')) {

    // 웹 결제 테스트 mid
    set_subs_option('su_inicis_mid', 'INIBillTst');
    // 웹 결제 테스트 signkey
    set_subs_option('su_inicis_sign_key', 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS');

    $inicis_iniapi_key = "rKnPljRn5m6J9Mzz";
    $inicis_iniapi_iv = "W2KLNKra6Wxc1P==";
    
    // 테스트 결제 URL
    $stdpay_js_url = 'https://stgstdpay.inicis.com/stdjs/INIStdPay.js';
    
} else {
    set_subs_option('su_inicis_mid', "SIR".get_subs_option('su_inicis_mid'));
    
    $inicis_iniapi_key = get_subs_option('su_inicis_iniapi_key');
    $inicis_iniapi_iv = get_subs_option('su_inicis_iniapi_iv');
    
    // 실 결제 URL
    $stdpay_js_url = 'https://stdpay.inicis.com/stdjs/INIStdPay.js';
}
    
/**************************
 * 1. 라이브러리 인클루드 *
 **************************/
require_once(G5_SUBSCRIPTION_PATH.'/inicis/libs/INIStdPayUtil.php');

$mid = get_subs_option('su_inicis_mid');
$signKey = get_subs_option('su_inicis_sign_key');

$SignatureUtil = new INIStdPayUtil();
$mKey 	= $SignatureUtil->makeHash($signKey, "sha256");

$timestamp 		= $SignatureUtil->getTimestamp();   			// util에 의해서 자동생성
$use_chkfake	= "Y";											// PC결제 보안강화 사용 ["Y" 고정]

$acceptmethod = 'HPP(1):below1000:va_receipt:BILLAUTH(Card):centerCd(Y)';

/* 기타 */
$siteDomain = G5_SUBSCRIPTION_URL.'/inicis'; //가맹점 도메인 입력

$returnUrl = $siteDomain.'/INIbill_pc_return.php';
$closeUrl  = $siteDomain.'/close.php';

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

function get_subscription_inicis_iniapi_key() {

    // iniapi_key 는 전체취소, 부분취소, 현금영수증, 에스크로 배송등록에 사용됨
    if (get_subs_option('su_card_test')) {     // 테스트결제이면
        return "rKnPljRn5m6J9Mzz";
    }

    return get_subs_option('su_inicis_iniapi_key');
}

function get_subscription_inicis_iniapi_iv() {

    // iniapi_iv 는 현금영수증 발급에 사용됨
    if (get_subs_option('su_card_test')) {     // 테스트결제이면
        return "W2KLNKra6Wxc1P==";
    }

    return get_subs_option('su_inicis_iniapi_iv');
}

// KG 이니시스 전체취소 요청 함수
// $args 변수의 타입은 array, $is_part 변수는 부분취소 구분 변수
function subscription_inicis_tid_cancel($args, $is_part=false){

    // step1. 요청을 위한 파라미터 설정
    // 가맹점관리자 > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성조회
    $key         = isset($args['key']) ? $args['key'] : get_subscription_inicis_iniapi_key();
    $type        = "refund";        // 고정
    $paymethod   = isset($args['paymethod']) ? $args['paymethod'] : "Card";
    $timestamp   = isset($args['timestamp']) ? $args['timestamp'] : date("YmdHis");
    $clientIp    = isset($args['clientIp']) ? $args['clientIp'] : $_SERVER['SERVER_ADDR'];				
    $mid         = isset($args['mid']) ? $args['mid'] : get_subs_option('su_inicis_mid');
    $tid         = $args['tid'];
    $msg         = $args['msg'];
    
    $detail = array();
	$detail["tid"] = $tid;
	$detail["msg"] = $msg;
    $details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
    
    // 부분취소인 경우
    if ($is_part){
        $type = 'PartialRefund';
        $price = $args['price'];
        $confirmPrice = $args['confirmPrice'];

        // INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid + price + confirmPrice
        $hashData = hash("sha512",(string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid.(string)$price.(string)$confirmPrice); // hash 암호화
        
        /*
        //step2. key=value 로 post 요청
        $data = array(
            'type' => $type,
            'paymethod' => $paymethod,
            'timestamp' => $timestamp,
            'clientIp' => $clientIp,
            'mid' => $mid,
            'tid' => $tid,
            'price' => $price,
            'confirmPrice' => $confirmPrice,
            'msg' => $msg,
            'hashData'=> $hashData
        );
        */
        
    } else {
        // 전체취소인 경우
        // $key.$mid.$type.$timestamp.$details
        $hashData = hash("sha512", (string)$key.(string)$mid.(string)$type.(string)$timestamp.(string)$details); // hash 암호화

        //step2. key=value 로 post 요청
        $postdata = array(
            'type' => $type,
            'timestamp' => $timestamp,
            'clientIp' => $clientIp,
            'mid' => $mid,
            'data' => $detail,
            'hashData'=> $hashData
        );
        
        /*
        echo (string)$key.'_'.(string)$mid.'_'.(string)$type.'_'.(string)$timestamp.'_'.(string)$details;
        
        print_r( $postdata );
        exit;
        */
        
        $post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
    }

    $url = "https://iniapi.inicis.com/v2/pg/refund";

    $ch = curl_init();                                      
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, 1);     
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    //step3. 요청 결과
    return $response;
}