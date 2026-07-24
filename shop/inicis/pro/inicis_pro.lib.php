<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('inicis_pro_is_enabled')) {
    function inicis_pro_is_enabled()
    {
        global $default;

        return isset($default['de_pg_service']) && isset($default['de_inicis_pro_use'])
            && $default['de_pg_service'] === 'inicis'
            && (int) $default['de_inicis_pro_use'] === 1;
    }
}

if (!function_exists('inicis_pro_use_escrow_creds')) {
    function inicis_pro_use_escrow_creds($pay_type)
    {
        global $default;

        if (empty($default['de_escrow_use']))
            return false;
        // 에스크로(구매안전)는 계좌이체·가상계좌에만 적용한다. 카드·간편결제·휴대폰은 제외.
        // 결제수단이 지정되지 않은(null) 호출은 기존 동작(에스크로 적용)을 유지한다.
        if ($pay_type === null)
            return true;
        $pay_type = strtoupper((string) $pay_type);
        return $pay_type === 'BANK' || $pay_type === 'VBANK';
    }
}

if (!function_exists('inicis_pro_get_mid')) {
    function inicis_pro_get_mid($pay_type = null)
    {
        global $default;

        if (!empty($default['de_card_test'])) {
            return inicis_pro_use_escrow_creds($pay_type) ? 'iniescrow0' : 'INIpayTest';
        }

        $mid = isset($default['de_inicis_mid']) ? trim($default['de_inicis_mid']) : '';
        if ($mid !== '' && strncasecmp($mid, 'SIR', 3) !== 0)
            $mid = 'SIR'.$mid;

        return $mid;
    }
}

if (!function_exists('inicis_pro_get_hashkey')) {
    function inicis_pro_get_hashkey($pay_type = null)
    {
        global $default;

        // 공용 테스트 MID는 KG이니시스 공식 샘플의 테스트 HashKey를 사용한다.
        // 에스크로 테스트 MID(iniescrow0)는 INIpayTest와 다른 HashKey를 사용한다.
        if (!empty($default['de_card_test']))
            return inicis_pro_use_escrow_creds($pay_type) ? '37F3A120C1DFCDEB708B00F5383D2263' : '3CB8183A4BE283555ACC8363C0360223';

        return isset($default['de_inicis_hash_key']) ? trim($default['de_inicis_hash_key']) : '';
    }
}

if (!function_exists('inicis_pro_timestamp')) {
    function inicis_pro_timestamp()
    {
        $parts = explode(' ', microtime());
        $milliseconds = isset($parts[0]) ? (int) floor(((float) $parts[0]) * 1000) : 0;
        $seconds = isset($parts[1]) ? preg_replace('/[^0-9]/', '', $parts[1]) : (string) time();

        return $seconds.sprintf('%03d', $milliseconds);
    }
}

if (!function_exists('inicis_pro_hash')) {
    function inicis_pro_hash($amount, $oid, $timestamp, $hashkey)
    {
        if (!function_exists('hash') || !in_array('sha512', hash_algos()))
            return '';

        return base64_encode(hash('sha512', (string) $amount.(string) $oid.(string) $timestamp.(string) $hashkey, true));
    }
}

if (!function_exists('inicis_pro_equals')) {
    function inicis_pro_equals($known, $user)
    {
        $known = (string) $known;
        $user = (string) $user;
        if (strlen($known) !== strlen($user))
            return false;

        $result = 0;
        for ($i = 0, $length = strlen($known); $i < $length; $i++)
            $result |= ord($known[$i]) ^ ord($user[$i]);

        return $result === 0;
    }
}

if (!function_exists('inicis_pro_cut')) {
    function inicis_pro_cut($value, $length)
    {
        $value = trim(strip_tags((string) $value));
        if (strlen($value) <= $length)
            return $value;

        if (function_exists('mb_strcut'))
            return mb_strcut($value, 0, $length, 'UTF-8');

        $result = substr($value, 0, $length);
        if (function_exists('iconv')) {
            $clean_result = @iconv('UTF-8', 'UTF-8//IGNORE', $result);
            if ($clean_result !== false)
                return $clean_result;
        }

        return $result;
    }
}

if (!function_exists('inicis_pro_valid_email')) {
    function inicis_pro_valid_email($email)
    {
        $email = trim((string) $email);
        if ($email === '' || strlen($email) > 60)
            return false;
        if (!preg_match('/^[0-9A-Za-z._%+-]+@([0-9A-Za-z-]+\.)+[A-Za-z]{2,}$/', $email))
            return false;

        $tld = strtolower(substr(strrchr($email, '.'), 1));
        if (in_array($tld, array('test', 'example', 'invalid', 'localhost', 'local')))
            return false;

        return true;
    }
}

if (!function_exists('inicis_pro_clean_oid')) {
    function inicis_pro_clean_oid($oid)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $oid);
    }
}

if (!function_exists('inicis_pro_clean_tid')) {
    function inicis_pro_clean_tid($tid)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $tid);
    }
}

if (!function_exists('inicis_pro_json_encode')) {
    function inicis_pro_json_encode($data)
    {
        if (!function_exists('json_encode'))
            require_once(G5_SHOP_PATH.'/inicis/libs/json_lib.php');

        return json_encode($data);
    }
}

if (!function_exists('inicis_pro_json_response')) {
    function inicis_pro_json_response($data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo inicis_pro_json_encode($data);
        exit;
    }
}

if (!function_exists('inicis_pro_parse_nvp')) {
    function inicis_pro_parse_nvp($body)
    {
        $result = array();
        $items = explode('&', (string) $body);
        foreach ($items as $item) {
            if ($item === '')
                continue;

            $position = strpos($item, '=');
            if ($position === false)
                continue;

            $key = urldecode(substr($item, 0, $position));
            if (!preg_match('/^[A-Za-z0-9_]+$/', $key))
                continue;

            $result[$key] = urldecode(substr($item, $position + 1));
        }

        return $result;
    }
}

if (!function_exists('inicis_pro_idc_host')) {
    function inicis_pro_idc_host($idc)
    {
        $hosts = array(
            'fc' => 'fcpaypro.inicis.com',
            'ks' => 'kspaypro.inicis.com',
            'stg' => 'stgpaypro.inicis.com'
        );

        return isset($hosts[$idc]) ? $hosts[$idc] : '';
    }
}

if (!function_exists('inicis_pro_http_post')) {
    function inicis_pro_http_post($url, $params, $timeout = 30)
    {
        $timeout = (int) $timeout;
        if ($timeout < 5 || $timeout > 30)
            $timeout = 30;

        if (!function_exists('curl_init'))
            return array('success' => false, 'error' => 'cURL 모듈이 설치되어 있지 않습니다.', 'body' => '');

        $handle = curl_init();
        if (!$handle)
            return array('success' => false, 'error' => '결제 서버 연결을 초기화하지 못했습니다.', 'body' => '');

        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout < 10 ? $timeout : 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'));

        $body = curl_exec($handle);
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($body === false || $errno)
            return array('success' => false, 'error' => '결제 서버 통신 오류: '.$error, 'body' => '');

        if ($status < 200 || $status >= 300)
            return array('success' => false, 'error' => '결제 서버 응답 오류: HTTP '.$status, 'body' => (string) $body);

        return array('success' => true, 'error' => '', 'body' => (string) $body);
    }
}

if (!function_exists('inicis_pro_pay_type')) {
    function inicis_pro_pay_type($settle_case)
    {
        switch ($settle_case) {
            case '계좌이체':
                return 'BANK';
            case '가상계좌':
                return 'VBANK';
            case '휴대폰':
                return 'HPP';
            case '신용카드':
            case '간편결제':
            case '삼성페이':
            case 'lpay':
            case 'inicis_kakaopay':
                return 'CARD';
        }

        return '';
    }
}

if (!function_exists('inicis_pro_easypay_code')) {
    function inicis_pro_easypay_code($settle_case)
    {
        switch ($settle_case) {
            case '삼성페이':
                return 'B';
            case 'lpay':
                return 'L';
            case 'inicis_kakaopay':
                return 'O';
        }

        return '';
    }
}

if (!function_exists('inicis_pro_easypay_name')) {
    function inicis_pro_easypay_name($settle_case)
    {
        switch ($settle_case) {
            case '삼성페이':
                return 'SAMSUNGPAY';
            case 'lpay':
                return 'LPAY';
            case 'inicis_kakaopay':
                return 'KAKAOPAY';
            case '간편결제':
                return 'EASYPAY';
        }

        return '';
    }
}

if (!function_exists('inicis_pro_environment')) {
    function inicis_pro_environment()
    {
        global $default;

        return !empty($default['de_card_test']) ? 'test' : 'live';
    }
}

if (!function_exists('inicis_pro_easypay_is_enabled')) {
    function inicis_pro_easypay_is_enabled($settle_case)
    {
        global $default;

        switch ($settle_case) {
            case '삼성페이':
                return !empty($default['de_samsung_pay_use']);
            case 'lpay':
                return !empty($default['de_inicis_lpay_use']);
            case 'inicis_kakaopay':
                return !empty($default['de_inicis_kakaopay_use']);
        }

        return true;
    }
}

if (!function_exists('inicis_pro_result_type_matches')) {
    function inicis_pro_result_type_matches($settle_case, $result_type)
    {
        $expected = inicis_pro_pay_type($settle_case);
        $result_type = strtoupper((string) $result_type);

        if ($expected === 'HPP')
            return $result_type === 'HPP' || $result_type === 'MOBILE';

        return $expected !== '' && $expected === $result_type;
    }
}

if (!function_exists('inicis_pro_expected_amount')) {
    function inicis_pro_expected_amount($order_data, $temp_row)
    {
        global $g5;

        if (!empty($order_data['pp_id'])) {
            $pp_id = inicis_pro_clean_oid($order_data['pp_id']);
            $pp = sql_fetch(" select pp_id, pp_price, pp_use, pp_tno from {$g5['g5_shop_personalpay_table']} where pp_id = '".sql_escape_string($pp_id)."' ");
            if (empty($pp['pp_id']) || empty($pp['pp_use']) || !empty($pp['pp_tno']))
                return 0;

            return (int) $pp['pp_price'];
        }

        $price = isset($order_data['od_price']) ? (int) $order_data['od_price'] : 0;
        $send_cost = isset($order_data['od_send_cost']) ? (int) $order_data['od_send_cost'] : 0;
        $send_cost2 = isset($order_data['od_send_cost2']) ? (int) $order_data['od_send_cost2'] : 0;
        $send_coupon = isset($order_data['od_send_coupon']) ? abs((int) $order_data['od_send_coupon']) : 0;
        $point = isset($order_data['od_temp_point']) ? (int) $order_data['od_temp_point'] : 0;

        return $price + $send_cost + $send_cost2 - $send_coupon - $point;
    }
}

if (!function_exists('inicis_pro_goods_name')) {
    function inicis_pro_goods_name($order_data, $temp_row)
    {
        global $g5;

        if (!empty($order_data['pp_id'])) {
            $name = isset($order_data['pp_name']) ? $order_data['pp_name'] : '개인결제';
            return inicis_pro_cut($name.'님 개인결제', 80);
        }

        if (!empty($order_data['od_goods_name']))
            return inicis_pro_cut($order_data['od_goods_name'], 80);

        $cart_id = isset($temp_row['cart_id']) ? sql_escape_string($temp_row['cart_id']) : '';
        $row = sql_fetch(" select min(it_name) as it_name, count(distinct it_id) as cnt from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' and ct_select = '1' ");
        $name = !empty($row['it_name']) ? $row['it_name'] : '상품 주문';
        if ((int) $row['cnt'] > 1)
            $name .= ' 외 '.((int) $row['cnt'] - 1).'건';

        return inicis_pro_cut($name, 80);
    }
}

if (!function_exists('inicis_pro_make_noti')) {
    function inicis_pro_make_noti($oid, $amount, $device, $hashkey)
    {
        $device_code = $device === 'MOBILE' ? 'M' : 'W';
        $signature = hash('sha256', 'PRO|'.$device_code.'|'.$oid.'|'.$amount.'|'.$hashkey);

        // 결제 요청의 가맹점 예약필드는 이니시스 호환성을 위해 영문과 숫자만 사용한다.
        return 'PRO'.$device_code.$signature;
    }
}

if (!function_exists('inicis_pro_check_noti')) {
    function inicis_pro_check_noti($noti, $oid, $amount, $hashkey)
    {
        $noti = (string) $noti;
        $device_code = '';

        if (strlen($noti) === 68 && substr($noti, 0, 3) === 'PRO') {
            $device_code = substr($noti, 3, 1);
        } else {
            // 코드 교체 전에 열린 결제창의 콜백도 검증할 수 있도록 이전 형식을 허용한다.
            $parts = explode(':', $noti, 3);
            if (count($parts) === 3 && $parts[0] === 'PRO')
                $device_code = $parts[1];
        }

        if ($device_code !== 'W' && $device_code !== 'M')
            return '';

        $device = $device_code === 'M' ? 'MOBILE' : 'WEB';
        $expected = inicis_pro_make_noti($oid, $amount, $device, $hashkey);
        if (inicis_pro_equals($expected, $noti))
            return $device;

        $legacy_expected = 'PRO:'.$device_code.':'.substr($expected, 4);

        return inicis_pro_equals($legacy_expected, $noti) ? $device : '';
    }
}

if (!function_exists('inicis_pro_lock')) {
    function inicis_pro_lock($oid, $timeout = 10)
    {
        $timeout = (int) $timeout;
        if ($timeout < 0)
            $timeout = 0;
        $lock_name = 'g5_inicis_pro_'.md5($oid);
        $row = sql_fetch(" select get_lock('".sql_escape_string($lock_name)."', ".$timeout.") as lock_result ");

        return !empty($row['lock_result']) ? $lock_name : '';
    }
}

if (!function_exists('inicis_pro_unlock')) {
    function inicis_pro_unlock($lock_name)
    {
        if ($lock_name !== '')
            sql_query(" do release_lock('".sql_escape_string($lock_name)."') ", false);
    }
}

if (!function_exists('inicis_pro_log_payload')) {
    function inicis_pro_log_payload($payload)
    {
        $allowed = array(
            'P_STATUS', 'P_RMESG', 'P_MID', 'P_OID', 'P_AUTH_TID', 'P_APPL_TID',
            'P_APPL_DT', 'P_APPL_TM', 'P_AMT', 'P_TYPE', 'P_UNAME', 'P_NOTI',
            'P_APPL_NO', 'P_FN_CD1', 'P_FN_NM', 'P_CARD_ISSUER_CODE',
            'P_CARD_ISSUER_NAME', 'P_SRC_CODE', 'P_VACT_NUM', 'P_VACT_DATE',
            'P_VACT_TIME', 'P_VACT_NAME', 'P_CSHR_CODE', 'P_CSHR_AUTH_NO',
            'P_CSHR_DT', 'P_HPP_CORP', 'P_HPP_NUM', 'P_APPL_NUM',
            'P_IN_TID', 'P_IDCNAME', '__pro', '__device', '__noti_status',
            '__noti_auth_dt', '__noti_received_at', '__noti_ip'
        );
        $result = array();
        foreach ($allowed as $key) {
            if (isset($payload[$key]) && !is_array($payload[$key]))
                $result[$key] = inicis_pro_cut($payload[$key], 600);
        }

        return $result;
    }
}

if (!function_exists('inicis_pro_save_log')) {
    function inicis_pro_save_log($payload)
    {
        global $g5;

        $payload = inicis_pro_log_payload($payload);
        $oid = isset($payload['P_OID']) ? inicis_pro_clean_oid($payload['P_OID']) : '';
        if ($oid === '')
            return false;

        $tid = isset($payload['P_APPL_TID']) && $payload['P_APPL_TID'] !== '' ? $payload['P_APPL_TID'] : (isset($payload['P_AUTH_TID']) ? $payload['P_AUTH_TID'] : '');
        $mid = isset($payload['P_MID']) ? $payload['P_MID'] : '';
        $auth_dt = (isset($payload['P_APPL_DT']) ? $payload['P_APPL_DT'] : '').(isset($payload['P_APPL_TM']) ? $payload['P_APPL_TM'] : '');
        $status = isset($payload['P_STATUS']) ? $payload['P_STATUS'] : '';
        $type = isset($payload['P_TYPE']) ? $payload['P_TYPE'] : '';
        $fn_name = isset($payload['P_FN_NM']) ? $payload['P_FN_NM'] : '';
        $auth_no = isset($payload['P_APPL_NO']) ? $payload['P_APPL_NO'] : '';
        $amount = isset($payload['P_AMT']) ? (int) $payload['P_AMT'] : 0;
        $message = isset($payload['P_RMESG']) ? $payload['P_RMESG'] : '';
        $post_data = base64_encode(serialize($payload));

        $sql = " insert into {$g5['g5_shop_inicis_log_table']}
                    set oid = '".sql_escape_string($oid)."',
                        P_TID = '".sql_escape_string($tid)."',
                        P_MID = '".sql_escape_string($mid)."',
                        P_AUTH_DT = '".sql_escape_string($auth_dt)."',
                        P_STATUS = '".sql_escape_string($status)."',
                        P_TYPE = '".sql_escape_string($type)."',
                        P_OID = '".sql_escape_string($oid)."',
                        P_FN_NM = '".sql_escape_string($fn_name)."',
                        P_AUTH_NO = '".sql_escape_string($auth_no)."',
                        P_AMT = '$amount',
                        P_RMESG1 = '".sql_escape_string($message)."',
                        post_data = '".sql_escape_string($post_data)."',
                        is_mail_send = '1'
                on duplicate key update
                        P_TID = values(P_TID),
                        P_MID = values(P_MID),
                        P_AUTH_DT = values(P_AUTH_DT),
                        P_STATUS = values(P_STATUS),
                        P_TYPE = values(P_TYPE),
                        P_OID = values(P_OID),
                        P_FN_NM = values(P_FN_NM),
                        P_AUTH_NO = values(P_AUTH_NO),
                        P_AMT = values(P_AMT),
                        P_RMESG1 = values(P_RMESG1),
                        post_data = values(post_data),
                        is_mail_send = values(is_mail_send) ";

        return (bool) sql_query($sql, false);
    }
}

if (!function_exists('inicis_pro_get_log')) {
    function inicis_pro_get_log($oid)
    {
        global $g5;

        $oid = inicis_pro_clean_oid($oid);
        if ($oid === '')
            return array();

        $row = sql_fetch(" select * from {$g5['g5_shop_inicis_log_table']} where oid = '".sql_escape_string($oid)."' ");
        if (empty($row['oid']))
            return array();

        $payload = !empty($row['post_data']) ? @unserialize(base64_decode($row['post_data'])) : array();
        $row['pro_data'] = is_array($payload) ? $payload : array();

        return $row;
    }
}

if (!function_exists('inicis_pro_audit_tables')) {
    function inicis_pro_audit_tables()
    {
        global $g5;

        $summary = isset($g5['g5_shop_inicis_pay_table']) ? $g5['g5_shop_inicis_pay_table'] : G5_SHOP_TABLE_PREFIX.'inicis_pay';
        $event = isset($g5['g5_shop_inicis_pay_event_table']) ? $g5['g5_shop_inicis_pay_event_table'] : G5_SHOP_TABLE_PREFIX.'inicis_pay_event';

        return array('summary' => $summary, 'event' => $event);
    }
}

if (!function_exists('inicis_pro_audit_ensure_tables')) {
    // 감사 테이블이 없으면 생성한다(스키마는 dbupgrade.php와 동일하게 유지).
    function inicis_pro_audit_ensure_tables()
    {
        $tables = inicis_pro_audit_tables();

        if (!sql_query(" DESC `{$tables['summary']}` ", false)) {
            sql_query(" CREATE TABLE IF NOT EXISTS `{$tables['summary']}` (
                          `ip_id` int(11) NOT NULL AUTO_INCREMENT,
                          `ip_oid` varchar(64) NOT NULL DEFAULT '',
                          `ip_tid` varchar(80) NOT NULL DEFAULT '',
                          `ip_auth_tid` varchar(80) NOT NULL DEFAULT '',
                          `ip_mid` varchar(80) NOT NULL DEFAULT '',
                          `ip_environment` varchar(10) NOT NULL DEFAULT '',
                          `mb_id` varchar(20) NOT NULL DEFAULT '',
                          `ip_amount` int(11) NOT NULL DEFAULT '0',
                          `ip_pay_type` varchar(20) NOT NULL DEFAULT '',
                          `ip_easy_pay` varchar(20) NOT NULL DEFAULT '',
                          `ip_device` varchar(10) NOT NULL DEFAULT '',
                          `ip_order_type` varchar(10) NOT NULL DEFAULT '',
                          `ip_status` varchar(30) NOT NULL DEFAULT '',
                          `ip_result_code` varchar(30) NOT NULL DEFAULT '',
                          `ip_result_message` varchar(255) NOT NULL DEFAULT '',
                          `ip_noti_status` varchar(30) NOT NULL DEFAULT '',
                          `ip_noti_code` varchar(30) NOT NULL DEFAULT '',
                          `ip_noti_message` varchar(255) NOT NULL DEFAULT '',
                          `ip_noti_failed_count` int(11) NOT NULL DEFAULT '0',
                          `ip_noti_at` datetime DEFAULT NULL,
                          `ip_cancel_status` varchar(30) NOT NULL DEFAULT '',
                          `ip_cancel_code` varchar(30) NOT NULL DEFAULT '',
                          `ip_cancel_message` varchar(255) NOT NULL DEFAULT '',
                          `ip_cancel_checked_at` datetime DEFAULT NULL,
                          `ip_refund_required` tinyint(4) NOT NULL DEFAULT '0',
                          `ip_vbank_due_at` datetime DEFAULT NULL,
                          `ip_expired_at` datetime DEFAULT NULL,
                          `ip_order_exists` tinyint(4) NOT NULL DEFAULT '0',
                          `ip_approved_at` datetime DEFAULT NULL,
                          `ip_ordered_at` datetime DEFAULT NULL,
                          `ip_notified_at` datetime DEFAULT NULL,
                          `ip_canceled_at` datetime DEFAULT NULL,
                          `ip_created_at` datetime DEFAULT NULL,
                          `ip_updated_at` datetime DEFAULT NULL,
                          `ip_ip` varchar(45) NOT NULL DEFAULT '',
                          `ip_event_count` int(11) NOT NULL DEFAULT '0',
                          `ip_audit_error` tinyint(4) NOT NULL DEFAULT '0',
                          `ip_alerted_at` datetime DEFAULT NULL,
                          `ip_alert_key` varchar(64) NOT NULL DEFAULT '',
                          `ip_pg_status` varchar(30) NOT NULL DEFAULT '',
                          `ip_pg_amount` int(11) NOT NULL DEFAULT '0',
                          `ip_pg_tid` varchar(80) NOT NULL DEFAULT '',
                          `ip_pg_result_code` varchar(30) NOT NULL DEFAULT '',
                          `ip_pg_message` varchar(255) NOT NULL DEFAULT '',
                          `ip_pg_checked_at` datetime DEFAULT NULL,
                          PRIMARY KEY (`ip_id`),
                          UNIQUE KEY `ip_oid` (`ip_oid`),
                          KEY `ip_tid` (`ip_tid`),
                          KEY `ip_auth_tid` (`ip_auth_tid`),
                          KEY `ip_status` (`ip_status`),
                          KEY `ip_noti_status` (`ip_noti_status`),
                          KEY `ip_cancel_status` (`ip_cancel_status`),
                          KEY `ip_refund_required` (`ip_refund_required`),
                          KEY `ip_updated_at` (`ip_updated_at`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
        }

        if (!sql_query(" DESC `{$tables['event']}` ", false)) {
            sql_query(" CREATE TABLE IF NOT EXISTS `{$tables['event']}` (
                          `pe_id` int(11) NOT NULL AUTO_INCREMENT,
                          `ip_oid` varchar(64) NOT NULL DEFAULT '',
                          `ip_tid` varchar(80) NOT NULL DEFAULT '',
                          `pe_stage` varchar(30) NOT NULL DEFAULT '',
                          `pe_status` varchar(30) NOT NULL DEFAULT '',
                          `pe_code` varchar(30) NOT NULL DEFAULT '',
                          `pe_message` varchar(255) NOT NULL DEFAULT '',
                          `pe_source` varchar(10) NOT NULL DEFAULT '',
                          `pe_ip` varchar(45) NOT NULL DEFAULT '',
                          `pe_created_at` datetime DEFAULT NULL,
                          PRIMARY KEY (`pe_id`),
                          KEY `ip_oid` (`ip_oid`),
                          KEY `ip_tid` (`ip_tid`),
                          KEY `pe_status` (`pe_status`),
                          KEY `pe_created_at` (`pe_created_at`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
        }
    }
}

if (!function_exists('inicis_pro_audit_tables_ready')) {
    function inicis_pro_audit_tables_ready()
    {
        $tables = inicis_pro_audit_tables();

        return (bool) sql_query(" select ip_id from `{$tables['summary']}` limit 1 ", false)
            && (bool) sql_query(" select pe_id from `{$tables['event']}` limit 1 ", false);
    }
}

if (!function_exists('inicis_pro_audit_column_exists')) {
    function inicis_pro_audit_column_exists($table, $column)
    {
        static $columns = array();

        $cache_key = $table.'.'.$column;
        if (isset($columns[$cache_key]))
            return $columns[$cache_key];

        if (!preg_match('/^[A-Za-z0-9_]+$/', $column))
            return false;

        $result = sql_query(" show columns from `{$table}` like '".sql_escape_string($column)."' ", false);
        $columns[$cache_key] = $result && sql_num_rows($result) > 0;

        return $columns[$cache_key];
    }
}

if (!function_exists('inicis_pro_audit_schema_ready')) {
    function inicis_pro_audit_schema_ready()
    {
        if (!inicis_pro_audit_tables_ready())
            return false;

        $tables = inicis_pro_audit_tables();
        $required = array(
            'ip_audit_error', 'ip_alerted_at', 'ip_alert_key', 'ip_pg_status',
            'ip_pg_amount', 'ip_pg_tid', 'ip_pg_result_code', 'ip_pg_message', 'ip_pg_checked_at',
            'ip_environment', 'ip_easy_pay', 'ip_noti_status', 'ip_noti_code',
            'ip_noti_message', 'ip_noti_failed_count', 'ip_noti_at', 'ip_cancel_status',
            'ip_cancel_code', 'ip_cancel_message', 'ip_cancel_checked_at',
            'ip_refund_required', 'ip_vbank_due_at', 'ip_expired_at'
        );
        foreach ($required as $column) {
            if (!inicis_pro_audit_column_exists($tables['summary'], $column))
                return false;
        }

        return true;
    }
}

if (!function_exists('inicis_pro_audit_error')) {
    function inicis_pro_audit_error($oid, $stage, $status, $part)
    {
        $message = '[INIpay PRO audit] '.$part.' write failed oid='.(string) $oid
            .' stage='.(string) $stage.' status='.(string) $status;
        error_log($message);
    }
}

if (!function_exists('inicis_pro_audit_write')) {
    function inicis_pro_audit_write($oid, $stage, $status, $values)
    {
        $oid = inicis_pro_clean_oid($oid);
        $stage = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $stage));
        $status = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $status));
        if ($oid === '' || $stage === '' || $status === '')
            return false;

        if (!is_array($values))
            $values = array();

        $tables = inicis_pro_audit_tables();
        $event_only = !empty($values['event_only']);
        $tid = isset($values['tid']) ? inicis_pro_clean_tid($values['tid']) : '';
        $auth_tid = isset($values['auth_tid']) ? inicis_pro_clean_tid($values['auth_tid']) : '';
        $mid = isset($values['mid']) ? inicis_pro_cut($values['mid'], 80) : '';
        $member_id = isset($values['member_id']) ? inicis_pro_cut($values['member_id'], 20) : '';
        $amount = isset($values['amount']) ? (int) $values['amount'] : 0;
        if ($amount < 0)
            $amount = 0;

        $pay_type = isset($values['pay_type']) ? strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', (string) $values['pay_type'])) : '';
        $environment = isset($values['environment']) ? strtolower(preg_replace('/[^a-z]/', '', (string) $values['environment'])) : '';
        if (!in_array($environment, array('', 'test', 'live')))
            $environment = '';
        $easy_pay = isset($values['easy_pay']) ? strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', (string) $values['easy_pay'])) : '';
        $device = isset($values['device']) ? strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $values['device'])) : '';
        if (!in_array($device, array('', 'WEB', 'MOBILE', 'SERVER')))
            $device = '';

        $order_type = isset($values['order_type']) ? strtolower((string) $values['order_type']) : '';
        if (!in_array($order_type, array('', 'order', 'personal')))
            $order_type = '';

        $code = isset($values['code']) ? inicis_pro_cut($values['code'], 30) : '';
        $message = isset($values['message']) ? inicis_pro_cut($values['message'], 255) : '';
        $source = isset($values['source']) ? strtolower(preg_replace('/[^a-z]/i', '', (string) $values['source'])) : 'web';
        if (!in_array($source, array('web', 'mobile', 'server', 'system', 'admin')))
            $source = 'system';

        $remote_ip = isset($values['remote_ip']) ? $values['remote_ip'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        $remote_ip = substr(preg_replace('/[^0-9a-fA-F:.]/', '', (string) $remote_ip), 0, 45);
        $now = G5_TIME_YMDHIS;
        $approved_at_sql = $status === 'approved' ? "'$now'" : 'NULL';
        $ordered_at_sql = $status === 'order_saved' ? "'$now'" : 'NULL';
        $notified_at_sql = in_array($status, array('notification_received', 'vbank_issued', 'paid', 'paid_after_cancel')) ? "'$now'" : 'NULL';
        $canceled_at_sql = $status === 'canceled' ? "'$now'" : 'NULL';
        $order_exists = $status === 'order_saved' ? 1 : 0;
        $noti_status = $stage === 'notification' ? $status : '';
        $noti_code = $stage === 'notification' ? $code : '';
        $noti_message = $stage === 'notification' ? $message : '';
        $noti_failed_count = $status === 'notification_failed' ? 1 : 0;
        $noti_at_sql = $stage === 'notification' ? "'$now'" : 'NULL';
        $cancel_status = $stage === 'cancel' ? $status : '';
        $cancel_code = $stage === 'cancel' ? $code : '';
        $cancel_message = $stage === 'cancel' ? $message : '';
        $cancel_at_sql = $stage === 'cancel' ? "'$now'" : 'NULL';
        $refund_required = !empty($values['refund_required']) || in_array($status, array('paid_after_cancel', 'refund_required')) ? 1 : 0;
        $vbank_due_at = isset($values['vbank_due_at']) ? trim((string) $values['vbank_due_at']) : '';
        if ($vbank_due_at !== '' && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $vbank_due_at))
            $vbank_due_at = '';
        $vbank_due_at_sql = $vbank_due_at !== '' ? "'".sql_escape_string($vbank_due_at)."'" : 'NULL';
        $expired_at_sql = $status === 'request_expired' ? "'$now'" : 'NULL';

        $extended_schema = inicis_pro_audit_column_exists($tables['summary'], 'ip_noti_status')
            && inicis_pro_audit_column_exists($tables['summary'], 'ip_refund_required');
        $extended_insert = '';
        $extended_update = '';
        if ($extended_schema) {
            $extended_insert = ",
                        ip_environment = '".sql_escape_string($environment)."',
                        ip_easy_pay = '".sql_escape_string($easy_pay)."',
                        ip_noti_status = '".sql_escape_string($noti_status)."',
                        ip_noti_code = '".sql_escape_string($noti_code)."',
                        ip_noti_message = '".sql_escape_string($noti_message)."',
                        ip_noti_failed_count = '$noti_failed_count',
                        ip_noti_at = $noti_at_sql,
                        ip_cancel_status = '".sql_escape_string($cancel_status)."',
                        ip_cancel_code = '".sql_escape_string($cancel_code)."',
                        ip_cancel_message = '".sql_escape_string($cancel_message)."',
                        ip_cancel_checked_at = $cancel_at_sql,
                        ip_refund_required = '$refund_required',
                        ip_vbank_due_at = $vbank_due_at_sql,
                        ip_expired_at = $expired_at_sql";
            $extended_update = ",
                        ip_environment = if(values(ip_environment) <> '', values(ip_environment), ip_environment),
                        ip_easy_pay = if(values(ip_easy_pay) <> '', values(ip_easy_pay), ip_easy_pay),
                        ip_noti_status = if(values(ip_noti_status) <> '', values(ip_noti_status), ip_noti_status),
                        ip_noti_code = if(values(ip_noti_status) <> '', values(ip_noti_code), ip_noti_code),
                        ip_noti_message = if(values(ip_noti_status) <> '', values(ip_noti_message), ip_noti_message),
                        ip_noti_failed_count = ip_noti_failed_count + values(ip_noti_failed_count),
                        ip_noti_at = if(values(ip_noti_at) is null, ip_noti_at, values(ip_noti_at)),
                        ip_cancel_status = if(values(ip_cancel_status) <> '', values(ip_cancel_status), ip_cancel_status),
                        ip_cancel_code = if(values(ip_cancel_status) <> '', values(ip_cancel_code), ip_cancel_code),
                        ip_cancel_message = if(values(ip_cancel_status) <> '', values(ip_cancel_message), ip_cancel_message),
                        ip_cancel_checked_at = if(values(ip_cancel_checked_at) is null, ip_cancel_checked_at, values(ip_cancel_checked_at)),
                        ip_refund_required = case
                            when ip_status = 'refund_completed' then '0'
                            when values(ip_status) = 'refund_completed' then '0'
                            when values(ip_refund_required) = '1' then '1'
                            else ip_refund_required
                        end,
                        ip_vbank_due_at = if(values(ip_vbank_due_at) is null, ip_vbank_due_at, values(ip_vbank_due_at)),
                        ip_expired_at = if(values(ip_expired_at) is null, ip_expired_at, values(ip_expired_at))";
        }

        $status_update = $event_only
            ? "if(ip_status = '', values(ip_status), ip_status)"
            : "case
                            when ip_status = 'refund_completed' then ip_status
                            when values(ip_status) = 'paid_after_cancel' then values(ip_status)
                            when values(ip_status) = 'refund_completed' then values(ip_status)
                            when ip_status = 'paid_after_cancel' then ip_status
                            when ip_status = 'canceled' and values(ip_status) <> 'paid_after_cancel' then ip_status
                            when ip_status = 'cancel_failed' and values(ip_status) <> 'canceled' then ip_status
                            when ip_status = 'paid' and values(ip_status) not in ('cancel_requested', 'canceled', 'cancel_failed', 'paid_after_cancel') then ip_status
                            when ip_status = 'order_saved' and values(ip_status) in ('request_ready', 'authentication_received', 'authentication_failed', 'validation_failed', 'approval_started', 'approval_failed', 'approved', 'order_saving') then ip_status
                            else values(ip_status)
                        end";

        $sql = " insert into `{$tables['summary']}`
                    set ip_oid = '".sql_escape_string($oid)."',
                        ip_tid = '".sql_escape_string($tid)."',
                        ip_auth_tid = '".sql_escape_string($auth_tid)."',
                        ip_mid = '".sql_escape_string($mid)."',
                        mb_id = '".sql_escape_string($member_id)."',
                        ip_amount = '$amount',
                        ip_pay_type = '".sql_escape_string($pay_type)."',
                        ip_device = '".sql_escape_string($device)."',
                        ip_order_type = '".sql_escape_string($order_type)."',
                        ip_status = '".sql_escape_string($status)."',
                        ip_result_code = '".sql_escape_string($code)."',
                        ip_result_message = '".sql_escape_string($message)."',
                        ip_order_exists = '$order_exists',
                        ip_approved_at = $approved_at_sql,
                        ip_ordered_at = $ordered_at_sql,
                        ip_notified_at = $notified_at_sql,
                        ip_canceled_at = $canceled_at_sql,
                        ip_created_at = '$now',
                        ip_updated_at = '$now',
                        ip_ip = '".sql_escape_string($remote_ip)."',
                        ip_event_count = '1'
                        $extended_insert
                on duplicate key update
                        ip_tid = if(values(ip_tid) <> '', values(ip_tid), ip_tid),
                        ip_auth_tid = if(values(ip_auth_tid) <> '', values(ip_auth_tid), ip_auth_tid),
                        ip_mid = if(values(ip_mid) <> '', values(ip_mid), ip_mid),
                        mb_id = if(values(mb_id) <> '', values(mb_id), mb_id),
                        ip_amount = if(values(ip_amount) > 0, values(ip_amount), ip_amount),
                        ip_pay_type = if(values(ip_pay_type) <> '', values(ip_pay_type), ip_pay_type),
                        ip_device = if(values(ip_device) <> '', values(ip_device), ip_device),
                        ip_order_type = if(values(ip_order_type) <> '', values(ip_order_type), ip_order_type),
                        ip_status = $status_update,
                        ip_result_code = ".($event_only ? 'ip_result_code' : "if(values(ip_result_code) <> '', values(ip_result_code), ip_result_code)").",
                        ip_result_message = ".($event_only ? 'ip_result_message' : "if(values(ip_result_message) <> '', values(ip_result_message), ip_result_message)").",
                        ip_order_exists = if(values(ip_order_exists) = '1', '1', ip_order_exists),
                        ip_approved_at = if(values(ip_approved_at) is null, ip_approved_at, values(ip_approved_at)),
                        ip_ordered_at = if(values(ip_ordered_at) is null, ip_ordered_at, values(ip_ordered_at)),
                        ip_notified_at = if(values(ip_notified_at) is null, ip_notified_at, values(ip_notified_at)),
                        ip_canceled_at = if(values(ip_canceled_at) is null, ip_canceled_at, values(ip_canceled_at)),
                        ip_updated_at = values(ip_updated_at),
                        ip_ip = if(values(ip_ip) <> '', values(ip_ip), ip_ip),
                        ip_event_count = ip_event_count + 1
                        $extended_update ";

        $summary_result = sql_query($sql, false);

        if (!$summary_result) {
            inicis_pro_audit_error($oid, $stage, $status, 'summary');
            return false;
        }

        $event_sql = " insert into `{$tables['event']}`
                           set ip_oid = '".sql_escape_string($oid)."',
                               ip_tid = '".sql_escape_string($tid)."',
                               pe_stage = '".sql_escape_string($stage)."',
                               pe_status = '".sql_escape_string($status)."',
                               pe_code = '".sql_escape_string($code)."',
                               pe_message = '".sql_escape_string($message)."',
                               pe_source = '".sql_escape_string($source)."',
                               pe_ip = '".sql_escape_string($remote_ip)."',
                               pe_created_at = '$now' ";

        $event_result = (bool) sql_query($event_sql, false);
        if (!$event_result) {
            inicis_pro_audit_error($oid, $stage, $status, 'event');
            if (inicis_pro_audit_column_exists($tables['summary'], 'ip_audit_error'))
                sql_query(" update `{$tables['summary']}` set ip_audit_error = '1' where ip_oid = '".sql_escape_string($oid)."' ", false);
        }

        // 주문/결제 상태가 요약 테이블에 안전하게 남았다면 통보 재시도 여부는
        // 상세 이벤트 기록 성공과 분리한다. 이벤트 실패는 ip_audit_error로 경고한다.
        return (bool) $summary_result;
    }
}

if (!function_exists('inicis_pro_get_iniapi_key')) {
    function inicis_pro_get_iniapi_key($pay_type = null)
    {
        global $default;

        if (!empty($default['de_card_test']))
            return inicis_pro_use_escrow_creds($pay_type) ? 'yERbIlJ3NhTeObsA' : 'ItEQKi3rY7uvDS8l';

        return isset($default['de_inicis_iniapi_key']) ? trim($default['de_inicis_iniapi_key']) : '';
    }
}

if (!function_exists('inicis_pro_server_ipv4')) {
    function inicis_pro_server_ipv4()
    {
        $ip = defined('G5_INICIS_PRO_CLIENT_IP') ? G5_INICIS_PRO_CLIENT_IP : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
        $ip = trim((string) $ip);
        if ($ip === '' || ip2long($ip) === false || strpos($ip, ':') !== false)
            return '';

        return $ip;
    }
}

if (!function_exists('inicis_pro_inquiry')) {
    function inicis_pro_inquiry($tid, $oid, $transaction = array())
    {
        global $default;

        if (!is_array($transaction))
            $transaction = array();
        $tid = inicis_pro_clean_tid($tid);
        $oid = inicis_pro_clean_oid($oid);
        $environment = isset($transaction['ip_environment']) ? strtolower($transaction['ip_environment']) : '';
        if (!in_array($environment, array('test', 'live')))
            $environment = inicis_pro_environment();
        $txn_pay_type = isset($transaction['ip_pay_type']) && $transaction['ip_pay_type'] !== '' ? $transaction['ip_pay_type'] : null;
        $mid = !empty($transaction['ip_mid']) ? trim($transaction['ip_mid']) : inicis_pro_get_mid($txn_pay_type);
        $configured_mid = inicis_pro_get_mid($txn_pay_type);
        if ($environment === 'test')
            $key = inicis_pro_use_escrow_creds($txn_pay_type) ? 'yERbIlJ3NhTeObsA' : 'ItEQKi3rY7uvDS8l';
        else
            $key = isset($default['de_inicis_iniapi_key']) ? trim($default['de_inicis_iniapi_key']) : '';
        $client_ip = inicis_pro_server_ipv4();
        if ($tid === '' && $oid === '')
            return array('success' => false, 'result_code' => 'REFERENCE_REQUIRED', 'message' => '승인 TID 또는 주문번호가 없어 KG이니시스 거래조회를 실행할 수 없습니다.', 'data' => array());
        if ($key === '' || $mid === '')
            return array('success' => false, 'result_code' => 'CONFIG_REQUIRED', 'message' => 'MID 또는 INIAPI KEY가 설정되지 않았습니다.', 'data' => array());
        if ($mid !== $configured_mid || $environment !== inicis_pro_environment())
            return array('success' => false, 'result_code' => 'TRANSACTION_CONFIG_MISMATCH', 'message' => '거래 당시 MID 또는 결제환경과 현재 설정이 달라 자동 조회하지 않았습니다.', 'data' => array());
        if ($client_ip === '')
            return array('success' => false, 'result_code' => 'CLIENT_IP_INVALID', 'message' => 'INIAPI 거래조회에 사용할 서버 IPv4 주소를 확인할 수 없습니다.', 'data' => array());
        if (!function_exists('hash') || !in_array('sha512', hash_algos()))
            return array('success' => false, 'result_code' => 'SHA512_REQUIRED', 'message' => '서버에서 SHA-512 해시를 지원하지 않습니다.', 'data' => array());

        $type = 'Extra';
        $paymethod = 'Inquiry';
        $timestamp = date('YmdHis', G5_SERVER_TIME);
        $params = array(
            'type' => $type,
            'paymethod' => $paymethod,
            'timestamp' => $timestamp,
            'clientIp' => $client_ip,
            'mid' => $mid,
            'hashData' => hash('sha512', $key.$type.$paymethod.$timestamp.$client_ip.$mid)
        );
        if ($tid !== '')
            $params['originalTid'] = $tid;
        if ($oid !== '')
            $params['oid'] = $oid;

        $host = $environment === 'test' ? 'stginiapi.inicis.com' : 'iniapi.inicis.com';
        $response = inicis_pro_http_post('https://'.$host.'/api/v1/extra', $params, 10);
        if (!$response['success'])
            return array('success' => false, 'result_code' => 'COMMUNICATION_FAILED', 'message' => inicis_pro_cut($response['error'], 255), 'data' => array());

        if (!function_exists('json_decode'))
            require_once(G5_SHOP_PATH.'/inicis/libs/json_lib.php');
        $data = json_decode($response['body'], true);
        if (!is_array($data))
            return array('success' => false, 'result_code' => 'INVALID_RESPONSE', 'message' => 'KG이니시스 거래조회 응답을 해석하지 못했습니다.', 'data' => array());

        $result_code = isset($data['resultCode']) ? inicis_pro_cut($data['resultCode'], 30) : '';
        $message = isset($data['resultMsg']) ? inicis_pro_cut($data['resultMsg'], 255) : '';

        return array(
            'success' => $result_code === '00',
            'result_code' => $result_code !== '' ? $result_code : 'INVALID_RESPONSE',
            'message' => $message !== '' ? $message : ($result_code === '00' ? '거래조회 완료' : '거래조회 실패'),
            'data' => $data
        );
    }
}

if (!function_exists('inicis_pro_save_inquiry')) {
    function inicis_pro_save_inquiry($oid, $inquiry, $source)
    {
        $oid = inicis_pro_clean_oid($oid);
        if ($oid === '' || !is_array($inquiry) || !inicis_pro_audit_schema_ready())
            return false;

        $tables = inicis_pro_audit_tables();
        $data = isset($inquiry['data']) && is_array($inquiry['data']) ? $inquiry['data'] : array();
        $pg_status = isset($data['status']) ? strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', (string) $data['status'])) : '';
        $pg_amount = isset($data['price']) && preg_match('/^[0-9]+$/', (string) $data['price']) ? (int) $data['price'] : 0;
        $pg_tid = isset($data['tid']) ? inicis_pro_clean_tid($data['tid']) : '';
        $result_code = isset($inquiry['result_code']) ? inicis_pro_cut($inquiry['result_code'], 30) : '';
        $message = isset($inquiry['message']) ? inicis_pro_cut($inquiry['message'], 255) : '';
        $now = G5_TIME_YMDHIS;
        $before = sql_fetch(" select ip_refund_required from `{$tables['summary']}` where ip_oid = '".sql_escape_string($oid)."' ");
        $refund_completed = !empty($before['ip_refund_required']) && $result_code === '00'
            && in_array($pg_status, array('1', '9', 'REFUND_COMPLETED', 'C', 'CANCEL', 'DEPOSIT_CANCELED'));

        $saved = sql_query(" update `{$tables['summary']}`
                                set ip_tid = if(ip_tid = '' and '".sql_escape_string($result_code)."' = '00' and '".sql_escape_string($pg_tid)."' <> '', '".sql_escape_string($pg_tid)."', ip_tid),
                                    ip_pg_status = '".sql_escape_string($pg_status)."',
                                    ip_pg_amount = '$pg_amount',
                                    ip_pg_tid = '".sql_escape_string($pg_tid)."',
                                    ip_pg_result_code = '".sql_escape_string($result_code)."',
                                    ip_pg_message = '".sql_escape_string($message)."',
                                    ip_pg_checked_at = '$now',
                                    ip_status = case
                                        when '".sql_escape_string($result_code)."' = '00'
                                         and '".sql_escape_string($pg_status)."' in ('1','9','REFUND_COMPLETED','C','CANCEL','DEPOSIT_CANCELED')
                                         and ip_refund_required = '1' then 'refund_completed'
                                        else ip_status
                                    end,
                                    ip_refund_required = case
                                        when '".sql_escape_string($result_code)."' = '00'
                                         and '".sql_escape_string($pg_status)."' in ('1','9','REFUND_COMPLETED','C','CANCEL','DEPOSIT_CANCELED') then '0'
                                        else ip_refund_required
                                    end
                              where ip_oid = '".sql_escape_string($oid)."' ", false);
        if (!$saved) {
            inicis_pro_audit_error($oid, 'reconcile', !empty($inquiry['success']) ? 'inquiry_success' : 'inquiry_failed', 'inquiry');
            return false;
        }

        inicis_pro_audit_write($oid, 'reconcile', !empty($inquiry['success']) ? 'inquiry_success' : 'inquiry_failed', array(
            'tid' => $pg_tid,
            'source' => in_array($source, array('system', 'admin')) ? $source : 'system',
            'code' => $result_code,
            'message' => $message,
            'event_only' => '1'
        ));
        if ($refund_completed) {
            inicis_pro_audit_write($oid, 'reconcile', 'refund_completed', array(
                'tid' => $pg_tid,
                'source' => in_array($source, array('system', 'admin')) ? $source : 'system',
                'code' => $result_code,
                'message' => 'KG 거래조회에서 취소 또는 환불 완료 확인',
                'event_only' => '1'
            ));
        }

        return true;
    }
}

if (!function_exists('inicis_pro_recover_approved_tid')) {
    function inicis_pro_recover_approved_tid($oid, $source)
    {
        $oid = inicis_pro_clean_oid($oid);
        if ($oid === '' || !inicis_pro_audit_schema_ready())
            return '';

        $tables = inicis_pro_audit_tables();
        $summary = sql_fetch(" select * from `{$tables['summary']}` where ip_oid = '".sql_escape_string($oid)."' ");
        if (empty($summary['ip_id']))
            return '';
        if (!empty($summary['ip_tid']))
            return inicis_pro_clean_tid($summary['ip_tid']);

        $log = inicis_pro_get_log($oid);
        $data = isset($log['pro_data']) && is_array($log['pro_data']) ? $log['pro_data'] : array();
        $tid = isset($data['P_APPL_TID']) ? inicis_pro_clean_tid($data['P_APPL_TID']) : '';
        $amount = isset($data['P_AMT']) ? (int) $data['P_AMT'] : 0;
        $summary_pay_type = isset($summary['ip_pay_type']) && $summary['ip_pay_type'] !== '' ? $summary['ip_pay_type'] : null;
        $summary_mid = !empty($summary['ip_mid']) ? $summary['ip_mid'] : inicis_pro_get_mid($summary_pay_type);
        if ($tid === '' || empty($data['__pro']) || $data['__pro'] !== '1'
            || !isset($log['P_STATUS']) || $log['P_STATUS'] !== '00'
            || !isset($data['P_APPL_TID']) || $data['P_APPL_TID'] !== $tid
            || !isset($data['P_MID']) || $data['P_MID'] !== $summary_mid
            || !isset($data['P_OID']) || $data['P_OID'] !== $oid
            || $amount <= 0 || ((int) $summary['ip_amount'] > 0 && (int) $summary['ip_amount'] !== $amount))
            return '';

        if (!sql_query(" update `{$tables['summary']}`
                            set ip_tid = '".sql_escape_string($tid)."',
                                ip_amount = if(ip_amount > 0, ip_amount, '$amount')
                          where ip_oid = '".sql_escape_string($oid)."'
                            and ip_tid = '' ", false))
            return '';

        inicis_pro_audit_write($oid, 'reconcile', 'approval_reference_recovered', array(
            'tid' => $tid,
            'amount' => $amount,
            'source' => in_array($source, array('system', 'admin')) ? $source : 'system',
            'message' => '승인 로그에서 KG 거래조회용 TID 복구',
            'event_only' => '1'
        ));

        return $tid;
    }
}

if (!function_exists('inicis_pro_noti_allowed_ips')) {
    function inicis_pro_noti_allowed_ips()
    {
        // KG이니시스 공식 가상계좌 통보 서버 IPv4. 관리자 입력 대신 코드에 고정한다.
        $value = '203.238.37.15,118.129.210.25,183.109.71.153';

        $result = array();
        $items = preg_split('/[\s,;]+/', $value);
        foreach ($items as $ip) {
            $ip = trim($ip);
            if ($ip !== '' && strpos($ip, ':') === false && ip2long($ip) !== false)
                $result[$ip] = $ip;
        }

        $result = array_values($result);
        return function_exists('run_replace') ? run_replace('inicis_pro_noti_allowed_ips', $result) : $result;
    }
}

if (!function_exists('inicis_pro_purge_events')) {
    function inicis_pro_purge_events($days, $limit)
    {
        $days = (int) $days;
        $limit = (int) $limit;
        if ($days < 30 || $limit < 1 || !inicis_pro_audit_tables_ready())
            return false;
        if ($limit > 1000)
            $limit = 1000;

        $tables = inicis_pro_audit_tables();
        $cutoff = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));

        return (bool) sql_query(" delete from `{$tables['event']}`
                                  where pe_created_at < '".sql_escape_string($cutoff)."'
                                  order by pe_id asc
                                  limit $limit ", false);
    }
}

if (!function_exists('inicis_pro_purge_payloads')) {
    function inicis_pro_purge_payloads($days, $limit)
    {
        global $g5;

        $days = (int) $days;
        $limit = (int) $limit;
        if ($days < 30 || $limit < 1)
            return false;
        if ($limit > 1000)
            $limit = 1000;

        $cutoff = date('YmdHis', G5_SERVER_TIME - ($days * 86400));

        return (bool) sql_query(" update {$g5['g5_shop_inicis_log_table']}
                                     set post_data = ''
                                   where post_data <> ''
                                     and length(P_AUTH_DT) >= 14
                                     and P_AUTH_DT not regexp '[^0-9]'
                                     and substr(P_AUTH_DT, 1, 14) < '".sql_escape_string($cutoff)."'
                                   limit $limit ", false);
    }
}

if (!function_exists('inicis_pro_purge_summaries')) {
    function inicis_pro_purge_summaries($days, $limit)
    {
        global $g5;

        $days = (int) $days;
        $limit = (int) $limit;
        if ($days < 365 || $limit < 1 || !inicis_pro_audit_schema_ready())
            return 0;
        if ($limit > 200)
            $limit = 200;

        $tables = inicis_pro_audit_tables();
        $cutoff = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
        $ids = array();
        $result = sql_query(" select p.ip_id
                                from `{$tables['summary']}` p
                                left join {$g5['g5_shop_order_table']} o on p.ip_order_type <> 'personal' and o.od_id = p.ip_oid
                                left join {$g5['g5_shop_personalpay_table']} pp on p.ip_order_type = 'personal' and pp.pp_id = p.ip_oid
                               where p.ip_created_at < '".sql_escape_string($cutoff)."'
                                 and p.ip_refund_required = '0'
                                 and p.ip_audit_error = '0'
                                 and o.od_id is null
                                 and pp.pp_id is null
                                 and p.ip_status in ('request_expired','authentication_failed','validation_failed','approval_failed','order_saved','paid','canceled','refund_completed','notification_failed')
                               order by p.ip_id asc
                               limit $limit ", false);
        if (!$result)
            return 0;
        while ($row = sql_fetch_array($result))
            $ids[] = (int) $row['ip_id'];
        if (!count($ids))
            return 0;

        $id_list = implode(',', $ids);
        sql_query(" delete e from `{$tables['event']}` e
                     inner join `{$tables['summary']}` p on p.ip_oid = e.ip_oid
                    where p.ip_id in ($id_list) ", false);
        if (!sql_query(" delete from `{$tables['summary']}` where ip_id in ($id_list) ", false))
            return 0;

        return count($ids);
    }
}

if (!function_exists('inicis_pro_audit_order_saved')) {
    function inicis_pro_audit_order_saved($oid, $tid, $order_type, $source)
    {
        return inicis_pro_audit_write($oid, 'order', 'order_saved', array(
            'tid' => $tid,
            'order_type' => $order_type,
            'source' => $source,
            'message' => $order_type === 'personal' ? '개인결제 정보 저장 완료' : '영카트 주문 저장 완료'
        ));
    }
}

if (!function_exists('inicis_pro_net_cancel')) {
    function inicis_pro_net_cancel($idc, $mid, $auth_tid, $amount, $oid, $message, $pay_type = null)
    {
        $host = inicis_pro_idc_host($idc);
        $hashkey = inicis_pro_get_hashkey($pay_type);
        if ($host === '' || $hashkey === '')
            return array('success' => false, 'data' => array(), 'error' => '망취소 설정이 올바르지 않습니다.');

        $timestamp = inicis_pro_timestamp();
        $params = array(
            'P_MID' => $mid,
            'P_AUTH_TID' => $auth_tid,
            'P_AMT' => (string) $amount,
            'P_OID' => $oid,
            'P_CANCEL_MSG' => inicis_pro_cut($message, 100),
            'P_TIMESTAMP' => $timestamp,
            'P_CHKFAKE' => inicis_pro_hash($amount, $oid, $timestamp, $hashkey),
            'P_CHARSET' => 'UTF-8'
        );
        $response = inicis_pro_http_post('https://'.$host.'/payment/v1/rest/payNetCancel.ini', $params);
        if (!$response['success'])
            return array('success' => false, 'data' => array(), 'error' => $response['error']);

        $data = inicis_pro_parse_nvp($response['body']);
        return array('success' => isset($data['P_STATUS']) && $data['P_STATUS'] === '00', 'data' => $data, 'error' => isset($data['P_RMESG']) ? $data['P_RMESG'] : '');
    }
}
