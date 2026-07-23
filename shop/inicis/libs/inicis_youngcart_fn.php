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

    $url = !empty($args['url']) ? $args['url'] : (!empty($default['de_card_test']) ? 'https://stginiapi.inicis.com/api/v1/refund' : 'https://iniapi.inicis.com/api/v1/refund');
    if (!function_exists('json_encode'))
        require_once(G5_SHOP_PATH.'/inicis/libs/json_lib.php');
    if (!in_array($url, array('https://iniapi.inicis.com/api/v1/refund', 'https://stginiapi.inicis.com/api/v1/refund')))
        return json_encode(array('resultCode' => 'INVALID_ENDPOINT', 'resultMsg' => 'Invalid INIAPI endpoint'));

    $audit_row = array();
    $audit_values = array();
    if (!isset($args['audit']) || $args['audit']) {
        include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
        if (function_exists('inicis_pro_audit_tables') && function_exists('inicis_pro_audit_write')) {
            $tables = inicis_pro_audit_tables();
            $audit_tid = inicis_pro_clean_tid($tid);
            $audit_oid = isset($args['audit_oid']) ? inicis_pro_clean_oid($args['audit_oid']) : '';
            $audit_where = $audit_oid !== ''
                ? "ip_oid = '".sql_escape_string($audit_oid)."' and ip_tid = '".sql_escape_string($audit_tid)."'"
                : "ip_tid = '".sql_escape_string($audit_tid)."'";
            $audit_row = $audit_tid !== '' ? sql_fetch(" select * from `{$tables['summary']}` where $audit_where order by ip_id desc limit 1 ", false) : array();
            if (!empty($audit_row['ip_oid'])) {
                $audit_source = isset($args['audit_source']) && in_array($args['audit_source'], array('web', 'admin', 'system')) ? $args['audit_source'] : 'system';
                $audit_actor = $audit_source === 'web' ? '주문자' : ($audit_source === 'admin' ? '관리자' : '시스템');
                $audit_values = array(
                    'tid' => $audit_tid,
                    'auth_tid' => isset($audit_row['ip_auth_tid']) ? $audit_row['ip_auth_tid'] : '',
                    'mid' => isset($audit_row['ip_mid']) ? $audit_row['ip_mid'] : '',
                    'environment' => isset($audit_row['ip_environment']) ? $audit_row['ip_environment'] : '',
                    'amount' => isset($audit_row['ip_amount']) ? (int) $audit_row['ip_amount'] : 0,
                    'pay_type' => isset($audit_row['ip_pay_type']) ? $audit_row['ip_pay_type'] : '',
                    'easy_pay' => isset($audit_row['ip_easy_pay']) ? $audit_row['ip_easy_pay'] : '',
                    'device' => isset($audit_row['ip_device']) ? $audit_row['ip_device'] : '',
                    'order_type' => isset($audit_row['ip_order_type']) ? $audit_row['ip_order_type'] : '',
                    'source' => $audit_source,
                    'message' => $is_part ? $audit_actor.' 부분취소 요청' : $audit_actor.' 전체취소 요청'
                );
                if ($is_part)
                    $audit_values['event_only'] = '1';
                inicis_pro_audit_write($audit_row['ip_oid'], 'cancel', 'cancel_requested', $audit_values);
            }
        }
    }

    $response = '';
    $request_environment = strpos($url, 'stginiapi.') !== false ? 'test' : 'live';
    if (!empty($audit_row['ip_oid'])) {
        $audit_environment = !empty($audit_row['ip_environment']) ? $audit_row['ip_environment'] : $request_environment;
        if ((!empty($audit_row['ip_mid']) && $audit_row['ip_mid'] !== $mid) || $audit_environment !== $request_environment)
            $response = json_encode(array('resultCode' => 'TRANSACTION_CONFIG_MISMATCH', 'resultMsg' => '거래 당시 MID 또는 결제환경과 현재 취소 설정이 다릅니다.'));
    }

    if ($response === '') {
        $ch = curl_init();
        if (!$ch) {
            $response = json_encode(array('resultCode' => 'COMMUNICATION_FAILED', 'resultMsg' => 'cURL 초기화 실패'));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
            curl_setopt($ch, CURLOPT_POST, 1);

            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $http_status < 200 || $http_status >= 300) {
                $error_message = $curl_error !== '' ? $curl_error : 'HTTP '.$http_status;
                $response = json_encode(array('resultCode' => 'COMMUNICATION_FAILED', 'resultMsg' => substr($error_message, 0, 150)));
            }
        }
    }

    // INIpay PRO 거래는 관리자 취소 결과도 단계별 결제 이력에 남긴다.
    if (!empty($audit_row['ip_oid'])) {
        if (!function_exists('json_decode'))
            require_once(G5_SHOP_PATH.'/inicis/libs/json_lib.php');
        $result_data = json_decode($response, true);
        $result_code = is_array($result_data) && isset($result_data['resultCode']) ? inicis_pro_cut($result_data['resultCode'], 30) : 'INVALID_RESPONSE';
        $result_message = is_array($result_data) && isset($result_data['resultMsg']) ? inicis_pro_cut($result_data['resultMsg'], 180) : '';
        $audit_success = $result_code === '00';
        $audit_values['code'] = $result_code;
        $audit_values['message'] = $is_part ? ($audit_success ? $audit_actor.' 부분취소 완료' : $audit_actor.' 부분취소 결과 확인 필요') : ($audit_success ? $audit_actor.' 전체취소 완료' : $audit_actor.' 전체취소 결과 확인 필요');
        if ($result_message !== '')
            $audit_values['message'] .= ' - '.$result_message;

        inicis_pro_audit_write($audit_row['ip_oid'], 'cancel', $is_part ? ($audit_success ? 'partial_canceled' : 'partial_cancel_failed') : ($audit_success ? 'canceled' : 'cancel_failed'), $audit_values);
    }

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
