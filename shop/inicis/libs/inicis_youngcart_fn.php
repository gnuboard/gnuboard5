<?php
if (!defined('_GNUBOARD_')) exit;

function get_inicis_iniapi_key() {
    global $default;
    
    // iniapi_key 는 전체취소, 부분취소, 현금영수증, 에스크로 배송등록에 사용됨
    if ($default['de_card_test']) {     // 테스트결제이면
        if ($default['de_inicis_mid'] === 'iniescrow0') {       // 에스크로 테스트용 mid
            return 'yERbIlJ3NhTeObsA';
        } else if ($default['de_inicis_mid'] === 'INIpayTest'){     // 일반 테스트용 mid
            return 'ItEQKi3rY7uvDS8l';
        }
    }

    return $default['de_inicis_iniapi_key'];
}

function get_inicis_iniapi_iv() {
    global $default;
    
    // iniapi_iv 는 현금영수증 발급에 사용됨
    if ($default['de_card_test']) {     // 테스트결제이면
        if ($default['de_inicis_mid'] === 'iniescrow0') {       // 에스크로 테스트용 mid
            return 'tOGDXbfoajk2DQ==';
        } else if ($default['de_inicis_mid'] === 'INIpayTest'){     // 일반 테스트용 mid
            return 'HYb3yQ4f65QL89==';
        }
    }

    return $default['de_inicis_iniapi_iv'];
}

// KG 이니시스 일반 주문 취소 함수
// $args 변수의 타입은 array, $is_part 변수는 부분취소 구분 변수
function inicis_tid_cancel($args, $is_part=false){
    global $default;

    // step1. 요청을 위한 파라미터 설정
    // 가맹점관리자 > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성조회
    $key         = isset($args['key']) ? $args['key'] : get_inicis_iniapi_key();
    $type        = "Refund";        // 고정
    $paymethod   = isset($args['paymethod']) ? $args['paymethod'] : "Card";
    $timestamp   = isset($args['timestamp']) ? $args['timestamp'] : date("YmdHis");
    $clientIp    = isset($args['clientIp']) ? $args['clientIp'] : $_SERVER['SERVER_ADDR'];				
    $mid         = isset($args['mid']) ? $args['mid'] : $default['de_inicis_mid'];
    $tid         = $args['tid'];
    $msg         = $args['msg'];
    
    // 부분취소인 경우
    if ($is_part){
        $type = 'PartialRefund';
        $price = $args['price'];
        $confirmPrice = $args['confirmPrice'];

        // INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid + price + confirmPrice
        $hashData = hash("sha512",(string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid.(string)$price.(string)$confirmPrice); // hash 암호화

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
    } else {
        // 전체취소인 경우
        // INIAPIKey + type + paymethod + timestamp + clientIp + mid + tid
        $hashData = hash("sha512", (string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid); // hash 암호화

        //step2. key=value 로 post 요청
        $data = array(
            'type' => $type,
            'paymethod' => $paymethod,
            'timestamp' => $timestamp,
            'clientIp' => $clientIp,
            'mid' => $mid,
            'tid' => $tid,
            'msg' => $msg,
            'hashData'=> $hashData
        );
    }

    $url = "https://iniapi.inicis.com/api/v1/refund";  

    $ch = curl_init();                                      
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);         
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
    curl_setopt($ch, CURLOPT_POST, 1);

    $response = curl_exec($ch);
    curl_close($ch);

    //step3. 요청 결과
    return $response;
}

function get_type_inicis_paymethod($od_settle_case){
    $ini_paymethod = '';

    switch ($od_settle_case) {
        case '신용카드':
        case '간편결제':
            $ini_paymethod = 'Card';
            break;
        case '가상계좌':
            $ini_paymethod = 'GVacct';  // 가상계좌 (입금전, 채번취소 시 사용)
            break;
        case '계좌이체':
            $ini_paymethod = 'Acct';
            break;
        case '휴대폰':
            $ini_paymethod = 'HPP';
            break;
    }
    
    if (! $ini_paymethod) {
        if (is_inicis_order_pay($od_settle_case)) {
            $ini_paymethod = 'Card';
        }
    }

    return $ini_paymethod;
}