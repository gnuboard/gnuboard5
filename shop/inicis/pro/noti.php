<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

@header('Content-Type: text/plain; charset=UTF-8');
@header('Cache-Control: no-store, no-cache, must-revalidate');

if (!function_exists('inicis_pro_noti_result')) {
    function inicis_pro_noti_result($success, $lock_name)
    {
        inicis_pro_unlock($lock_name);
        echo $success ? 'OK' : 'FAIL';
        exit;
    }
}

if (!function_exists('inicis_pro_noti_fail')) {
    function inicis_pro_noti_fail($code, $oid, $lock_name)
    {
        $oid = inicis_pro_clean_oid($oid);
        if ($oid !== '') {
            inicis_pro_audit_write($oid, 'notification', 'notification_failed', array(
                'event_only' => '1',
                'source' => 'server',
                'device' => 'SERVER',
                'remote_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'code' => $code,
                'message' => '가상계좌 통보 검증 실패'
            ));
        }

        inicis_pro_noti_result(false, $lock_name);
    }
}

if (!function_exists('inicis_pro_noti_time')) {
    function inicis_pro_noti_time($auth_date)
    {
        $auth_date = preg_replace('/[^0-9]/', '', (string) $auth_date);
        if (strlen($auth_date) === 12)
            $auth_date = '20'.$auth_date;
        if (strlen($auth_date) !== 14)
            return '';

        return substr($auth_date, 0, 4).'-'.substr($auth_date, 4, 2).'-'.substr($auth_date, 6, 2)
            .' '.substr($auth_date, 8, 2).':'.substr($auth_date, 10, 2).':'.substr($auth_date, 12, 2);
    }
}

if (!function_exists('inicis_pro_noti_saved_device')) {
    function inicis_pro_noti_saved_device($noti, $pro_log, $pro_data)
    {
        if (!isset($pro_log['P_STATUS']) || $pro_log['P_STATUS'] !== '00'
            || empty($pro_data['__pro']) || $pro_data['__pro'] !== '1'
            || !isset($pro_data['P_NOTI']) || !inicis_pro_equals($pro_data['P_NOTI'], $noti))
            return '';

        $device = isset($pro_data['__device']) ? strtoupper((string) $pro_data['__device']) : '';

        return in_array($device, array('WEB', 'MOBILE'), true) ? $device : '';
    }
}

if (!function_exists('inicis_pro_noti_approved_payment_exists')) {
    function inicis_pro_noti_approved_payment_exists($oid, $tid, $mid, $amount, $pay_type, $pro_log, $pro_data)
    {
        global $g5;

        if (!isset($pro_log['P_STATUS']) || $pro_log['P_STATUS'] !== '00'
            || empty($pro_data['__pro']) || $pro_data['__pro'] !== '1'
            || !isset($pro_data['P_MID']) || $pro_data['P_MID'] !== $mid
            || !isset($pro_data['P_OID']) || $pro_data['P_OID'] !== $oid
            || !isset($pro_data['P_AMT']) || (int) $pro_data['P_AMT'] !== $amount
            || !isset($pro_data['P_TYPE']) || strtoupper($pro_data['P_TYPE']) !== $pay_type
            || !isset($pro_data['P_APPL_TID']) || inicis_pro_clean_tid($pro_data['P_APPL_TID']) !== $tid
            || $pro_data['P_APPL_TID'] !== $tid)
            return false;

        $oid_sql = sql_escape_string($oid);
        $tid_sql = sql_escape_string($tid);
        $order = sql_fetch(" select od_id, od_receipt_price
                               from {$g5['g5_shop_order_table']}
                              where od_id = '$oid_sql'
                                and od_tno = '$tid_sql'
                                and od_pg = 'inicis' ");
        if (!empty($order['od_id']) && (int) $order['od_receipt_price'] === $amount)
            return true;

        $personal = sql_fetch(" select pp_id, pp_receipt_price
                                  from {$g5['g5_shop_personalpay_table']}
                                 where pp_id = '$oid_sql'
                                   and pp_tno = '$tid_sql'
                                   and pp_pg = 'inicis'
                                   and pp_use = '1' ");

        return !empty($personal['pp_id']) && (int) $personal['pp_receipt_price'] === $amount;
    }
}

if (!function_exists('inicis_pro_noti_save_audit')) {
    function inicis_pro_noti_save_audit($oid, $status, $auth_date, $remote_ip, $payment_status, $payment_message)
    {
        $log = inicis_pro_get_log($oid);
        $data = isset($log['pro_data']) && is_array($log['pro_data']) ? $log['pro_data'] : array();
        if (empty($data['__pro']) || $data['__pro'] !== '1')
            return false;

        $data['__noti_status'] = $status;
        $data['__noti_auth_dt'] = preg_replace('/[^0-9]/', '', (string) $auth_date);
        $data['__noti_received_at'] = G5_TIME_YMDHIS;
        $data['__noti_ip'] = $remote_ip;
        if (!empty($_POST['P_IN_TID']))
            $data['P_IN_TID'] = inicis_pro_clean_tid($_POST['P_IN_TID']);

        $saved = inicis_pro_save_log($data);
        $audit_saved = false;
        if ($saved) {
            $audit_saved = inicis_pro_audit_write($oid, 'notification', $payment_status, array(
                'tid' => isset($data['P_APPL_TID']) ? $data['P_APPL_TID'] : '',
                'auth_tid' => isset($data['P_AUTH_TID']) ? $data['P_AUTH_TID'] : '',
                'mid' => isset($data['P_MID']) ? $data['P_MID'] : '',
                'amount' => isset($data['P_AMT']) ? (int) $data['P_AMT'] : 0,
                'pay_type' => 'VBANK',
                'device' => 'SERVER',
                'source' => 'server',
                'remote_ip' => $remote_ip,
                'code' => $status,
                'refund_required' => $payment_status === 'paid_after_cancel' ? '1' : '',
                'message' => $payment_message
            ));
        }

        return $saved && $audit_saved;
    }
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST')
    inicis_pro_noti_result(false, '');

$remote_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$allowed_ips = inicis_pro_noti_allowed_ips();
if (!in_array($remote_ip, $allowed_ips, true))
    inicis_pro_noti_result(false, '');

$status = isset($_POST['P_STATUS']) ? trim((string) $_POST['P_STATUS']) : '';
$mid = isset($_POST['P_MID']) ? trim((string) $_POST['P_MID']) : '';
$oid_raw = isset($_POST['P_OID']) ? trim((string) $_POST['P_OID']) : '';
$oid = inicis_pro_clean_oid($oid_raw);
$tid_raw = isset($_POST['P_TID']) ? trim((string) $_POST['P_TID']) : '';
$tid = inicis_pro_clean_tid($tid_raw);
$amount_raw = isset($_POST['P_AMT']) ? trim((string) $_POST['P_AMT']) : '';
$pay_type = isset($_POST['P_TYPE']) ? strtoupper(trim((string) $_POST['P_TYPE'])) : '';
$noti = isset($_POST['P_NOTI']) ? trim((string) $_POST['P_NOTI']) : '';
$auth_date = isset($_POST['P_AUTH_DT']) ? $_POST['P_AUTH_DT'] : '';
$hashkey = inicis_pro_get_hashkey($pay_type);
$amount = preg_match('/^[0-9]+$/', $amount_raw) ? (int) $amount_raw : 0;
$pro_log = array();
$pro_data = array();
if ($oid !== '' && $oid === $oid_raw) {
    $pro_log = inicis_pro_get_log($oid);
    $pro_data = isset($pro_log['pro_data']) && is_array($pro_log['pro_data']) ? $pro_log['pro_data'] : array();
}

$expected_mid = inicis_pro_get_mid($pay_type);
if (isset($pro_log['P_STATUS']) && $pro_log['P_STATUS'] === '00'
    && !empty($pro_data['__pro']) && $pro_data['__pro'] === '1' && !empty($pro_data['P_MID']))
    $expected_mid = trim((string) $pro_data['P_MID']);

if ($mid === '')
    inicis_pro_noti_fail('MID_EMPTY', $oid, '');
if ($mid !== $expected_mid)
    inicis_pro_noti_fail('MID_MISMATCH', $oid, '');
if ($oid === '' || $oid !== $oid_raw)
    inicis_pro_noti_fail('OID_INVALID', $oid, '');
if ($tid === '' || $tid !== $tid_raw)
    inicis_pro_noti_fail('TID_INVALID', $oid, '');
if ($amount <= 0)
    inicis_pro_noti_fail('AMOUNT_INVALID', $oid, '');
if ($pay_type === '' || !preg_match('/^[A-Z0-9_]+$/', $pay_type))
    inicis_pro_noti_fail('TYPE_INVALID', $oid, '');

$device = inicis_pro_noti_saved_device($noti, $pro_log, $pro_data);
if ($device === '' && $hashkey !== '')
    $device = inicis_pro_check_noti($noti, $oid, $amount, $hashkey);
if ($device === '' && $hashkey === '')
    inicis_pro_noti_fail('HASHKEY_EMPTY', $oid, '');
if ($device === '')
    inicis_pro_noti_fail('NOTI_SIGNATURE_INVALID', $oid, '');

// 과거 요청에서 모든 결제수단에 통보 URL을 전달한 경우 카드 등 승인 통보도
// 이 URL로 재전송된다. 이미 승인과 주문 저장이 끝난 동일 거래는 변경 없이 확인한다.
if ($pay_type !== 'VBANK') {
    if ($status !== '00')
        inicis_pro_noti_fail('NON_VBANK_STATUS_INVALID', $oid, '');
    if (!inicis_pro_noti_approved_payment_exists($oid, $tid, $mid, $amount, $pay_type, $pro_log, $pro_data))
        inicis_pro_noti_fail('NON_VBANK_ORDER_MISMATCH', $oid, '');

    inicis_pro_audit_write($oid, 'notification', 'notification_acknowledged', array(
        'event_only' => '1',
        'tid' => $tid,
        'mid' => $mid,
        'amount' => $amount,
        'pay_type' => $pay_type,
        'device' => 'SERVER',
        'source' => 'server',
        'remote_ip' => $remote_ip,
        'code' => $status,
        'message' => '이미 처리된 결제의 서버 통보 확인'
    ));
    inicis_pro_noti_result(true, '');
}

// 가상계좌 채번 통보는 브라우저 승인 처리와 경쟁시키지 않고 확인 응답만 한다.
if ($status === '00') {
    $issued_saved = inicis_pro_audit_write($oid, 'notification', 'vbank_issued', array(
        'tid' => $tid,
        'mid' => $mid,
        'amount' => $amount,
        'pay_type' => 'VBANK',
        'device' => 'SERVER',
        'source' => 'server',
        'remote_ip' => $remote_ip,
        'code' => $status,
        'vbank_due_at' => !empty($pro_data['P_VACT_DATE'])
            ? substr($pro_data['P_VACT_DATE'], 0, 4).'-'.substr($pro_data['P_VACT_DATE'], 4, 2).'-'.substr($pro_data['P_VACT_DATE'], 6, 2).' '
                .(!empty($pro_data['P_VACT_TIME']) ? substr($pro_data['P_VACT_TIME'].'000000', 0, 2).':'.substr($pro_data['P_VACT_TIME'].'000000', 2, 2).':'.substr($pro_data['P_VACT_TIME'].'000000', 4, 2) : '23:59:00')
            : '',
        'message' => '가상계좌 발급 통보 수신'
    ));
    inicis_pro_noti_result($issued_saved, '');
}
if ($status !== '02')
    inicis_pro_noti_fail('STATUS_UNSUPPORTED', $oid, '');

$in_tid_raw = isset($_POST['P_IN_TID']) ? trim((string) $_POST['P_IN_TID']) : '';
$in_tid = inicis_pro_clean_tid($in_tid_raw);
// 상점관리자의 입금통보 테스트 등에서는 P_IN_TID가 빈 값으로 올 수 있다.
// 주문 식별에는 채번 TID(P_TID)를 사용하고, 입금 TID는 전달된 경우에만 형식을 검증한다.
if ($in_tid_raw !== '' && ($in_tid === '' || $in_tid !== $in_tid_raw))
    inicis_pro_noti_fail('IN_TID_FORMAT_INVALID', $oid, '');

$receipt_time = inicis_pro_noti_time($auth_date);
if ($receipt_time === '')
    inicis_pro_noti_fail('AUTH_TIME_INVALID', $oid, '');

$lock_name = inicis_pro_lock($oid);
if ($lock_name === '')
    inicis_pro_noti_fail('LOCK_BUSY', $oid, '');

$approved_tid = isset($pro_data['P_APPL_TID']) ? inicis_pro_clean_tid($pro_data['P_APPL_TID']) : '';
if (empty($pro_data['__pro']) || $pro_data['__pro'] !== '1')
    inicis_pro_noti_fail('APPROVAL_LOG_INVALID', $oid, $lock_name);
if (!isset($pro_log['P_STATUS']) || $pro_log['P_STATUS'] !== '00')
    inicis_pro_noti_fail('APPROVAL_STATUS_INVALID', $oid, $lock_name);
if (!isset($pro_data['P_MID']) || $pro_data['P_MID'] !== $mid)
    inicis_pro_noti_fail('APPROVAL_MID_MISMATCH', $oid, $lock_name);
if (!isset($pro_data['P_OID']) || $pro_data['P_OID'] !== $oid)
    inicis_pro_noti_fail('APPROVAL_OID_MISMATCH', $oid, $lock_name);
if (!isset($pro_data['P_AMT']) || (int) $pro_data['P_AMT'] !== $amount)
    inicis_pro_noti_fail('APPROVAL_AMOUNT_MISMATCH', $oid, $lock_name);
if (!isset($pro_data['P_TYPE']) || strtoupper($pro_data['P_TYPE']) !== 'VBANK')
    inicis_pro_noti_fail('APPROVAL_TYPE_MISMATCH', $oid, $lock_name);
if ($approved_tid !== $tid)
    inicis_pro_noti_fail('APPROVAL_TID_MISMATCH', $oid, $lock_name);

// 환불 완료 후 동일 입금통보가 재전송되면 환불 필요 상태를 다시 만들지 않는다.
$audit_tables = inicis_pro_audit_tables();
$completed_refund = sql_fetch(" select ip_status, ip_refund_required, ip_tid, ip_amount
                                  from `{$audit_tables['summary']}`
                                 where ip_oid = '".sql_escape_string($oid)."' ", false);
if (!empty($completed_refund['ip_status']) && $completed_refund['ip_status'] === 'refund_completed'
    && empty($completed_refund['ip_refund_required'])
    && $completed_refund['ip_tid'] === $tid && (int) $completed_refund['ip_amount'] === $amount) {
    $acknowledged = inicis_pro_audit_write($oid, 'notification', 'notification_acknowledged', array(
        'event_only' => '1',
        'tid' => $tid,
        'mid' => $mid,
        'amount' => $amount,
        'pay_type' => 'VBANK',
        'device' => 'SERVER',
        'source' => 'server',
        'remote_ip' => $remote_ip,
        'code' => $status,
        'message' => '환불 완료 거래의 중복 입금 통보 확인'
    ));
    inicis_pro_noti_result($acknowledged, $lock_name);
}

$received_saved = inicis_pro_audit_write($oid, 'notification', 'notification_received', array(
    'tid' => $tid,
    'auth_tid' => isset($pro_data['P_AUTH_TID']) ? $pro_data['P_AUTH_TID'] : '',
    'mid' => $mid,
    'amount' => $amount,
    'pay_type' => 'VBANK',
    'device' => 'SERVER',
    'order_type' => !empty($pro_data['__order_type']) ? $pro_data['__order_type'] : '',
    'source' => 'server',
    'remote_ip' => $remote_ip,
    'code' => $status,
    'message' => '가상계좌 입금 통보 수신'
));
if (!$received_saved)
    inicis_pro_noti_fail('AUDIT_SUMMARY_FAILED', $oid, $lock_name);

$oid_sql = sql_escape_string($oid);
$tid_sql = sql_escape_string($tid);
$time_sql = sql_escape_string($receipt_time);
$personal = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']}
                         where pp_id = '$oid_sql'
                           and pp_tno = '$tid_sql'
                           and pp_pg = 'inicis'
                           and pp_settle_case = '가상계좌'
                           and pp_use = '1' ");

if (!empty($personal['pp_id'])) {
    if ((int) $personal['pp_price'] !== $amount
        || ((int) $personal['pp_receipt_price'] !== 0 && (int) $personal['pp_receipt_price'] !== $amount))
        inicis_pro_noti_fail('PERSONAL_AMOUNT_MISMATCH', $oid, $lock_name);

    // 연결 주문은 메모의 고유 표식으로 중복 가산을 막는다.
    if (!empty($personal['od_id'])) {
        $linked_id = inicis_pro_clean_oid($personal['od_id']);
        $linked = sql_fetch(" select od_id, od_status, od_cancel_price, od_shop_memo from {$g5['g5_shop_order_table']} where od_id = '".sql_escape_string($linked_id)."' ");
        if (empty($linked['od_id']))
            inicis_pro_noti_fail('LINKED_ORDER_MISSING', $oid, $lock_name);
        if ($linked['od_status'] === '취소' || (int) $linked['od_cancel_price'] > 0) {
            $processed = inicis_pro_noti_save_audit($oid, $status, $auth_date, $remote_ip, 'paid_after_cancel', '취소된 연결 주문에 가상계좌 입금 확인 - 환불 필요');
            inicis_pro_noti_result($processed, $lock_name);
        }

        $marker = '[INIpay PRO personalpay '.$oid.']';
        if (strpos($linked['od_shop_memo'], $marker) === false) {
            $memo = "\n".$marker.' 입금완료 - '.$receipt_time;
            $update_linked = " update {$g5['g5_shop_order_table']}
                                  set od_receipt_price = od_receipt_price + '$amount',
                                      od_receipt_time = '$time_sql',
                                      od_shop_memo = concat(od_shop_memo, '".sql_escape_string($memo)."')
                                where od_id = '".sql_escape_string($linked_id)."' ";
            if (!sql_query($update_linked, false))
                inicis_pro_noti_fail('LINKED_ORDER_RECEIPT_UPDATE_FAILED', $oid, $lock_name);
        }

        $linked_info = get_order_info($linked_id);
        $linked_status_sql = " update {$g5['g5_shop_order_table']} set od_misu = '".(int) $linked_info['od_misu']."' ";
        if ((int) $linked_info['od_misu'] === 0)
            $linked_status_sql .= ", od_status = '입금' ";
        $linked_status_sql .= " where od_id = '".sql_escape_string($linked_id)."' ";
        if (!sql_query($linked_status_sql, false))
            inicis_pro_noti_fail('LINKED_ORDER_STATUS_UPDATE_FAILED', $oid, $lock_name);

        if ((int) $linked_info['od_misu'] === 0
            && !sql_query(" update {$g5['g5_shop_cart_table']} set ct_status = '입금' where od_id = '".sql_escape_string($linked_id)."' ", false))
            inicis_pro_noti_fail('LINKED_CART_STATUS_UPDATE_FAILED', $oid, $lock_name);
    }

    if (!sql_query(" update {$g5['g5_shop_personalpay_table']}
                        set pp_receipt_price = '$amount',
                            pp_receipt_time = '$time_sql'
                      where pp_id = '$oid_sql'
                        and pp_tno = '$tid_sql' ", false))
        inicis_pro_noti_fail('PERSONAL_RECEIPT_UPDATE_FAILED', $oid, $lock_name);

    $check_personal = sql_fetch(" select pp_receipt_price from {$g5['g5_shop_personalpay_table']} where pp_id = '$oid_sql' and pp_tno = '$tid_sql' ");
    $processed = isset($check_personal['pp_receipt_price']) && (int) $check_personal['pp_receipt_price'] === $amount;
    if ($processed)
        $processed = inicis_pro_noti_save_audit($oid, $status, $auth_date, $remote_ip, 'paid', '가상계좌 입금 통보 처리 완료');
    else
        inicis_pro_noti_fail('PERSONAL_RECEIPT_VERIFY_FAILED', $oid, $lock_name);
    inicis_pro_noti_result($processed, $lock_name);
}

$order = sql_fetch(" select * from {$g5['g5_shop_order_table']}
                      where od_id = '$oid_sql'
                        and od_tno = '$tid_sql'
                        and od_pg = 'inicis'
                        and od_settle_case = '가상계좌' ");
if (empty($order['od_id']))
    inicis_pro_noti_fail('ORDER_MISSING', $oid, $lock_name);
if ($order['od_status'] === '취소' || (int) $order['od_cancel_price'] > 0) {
    $processed = inicis_pro_noti_save_audit($oid, $status, $auth_date, $remote_ip, 'paid_after_cancel', '취소 주문에 가상계좌 입금 확인 - 환불 필요');
    inicis_pro_noti_result($processed, $lock_name);
}
if ((int) $order['od_receipt_price'] !== 0 && (int) $order['od_receipt_price'] !== $amount)
    inicis_pro_noti_fail('ORDER_RECEIPT_MISMATCH', $oid, $lock_name);

if (!sql_query(" update {$g5['g5_shop_order_table']}
                    set od_receipt_price = '$amount',
                        od_receipt_time = '$time_sql'
                  where od_id = '$oid_sql'
                    and od_tno = '$tid_sql' ", false))
    inicis_pro_noti_fail('ORDER_RECEIPT_UPDATE_FAILED', $oid, $lock_name);

$order_info = get_order_info($oid);
$status_sql = " update {$g5['g5_shop_order_table']} set od_misu = '".(int) $order_info['od_misu']."' ";
if ((int) $order_info['od_misu'] === 0)
    $status_sql .= ", od_status = '입금' ";
$status_sql .= " where od_id = '$oid_sql' and od_tno = '$tid_sql' ";
if (!sql_query($status_sql, false))
    inicis_pro_noti_fail('ORDER_STATUS_UPDATE_FAILED', $oid, $lock_name);

if ((int) $order_info['od_misu'] === 0
    && !sql_query(" update {$g5['g5_shop_cart_table']} set ct_status = '입금' where od_id = '$oid_sql' ", false))
    inicis_pro_noti_fail('CART_STATUS_UPDATE_FAILED', $oid, $lock_name);

$check_order = sql_fetch(" select od_receipt_price, od_misu from {$g5['g5_shop_order_table']} where od_id = '$oid_sql' and od_tno = '$tid_sql' ");
$processed = isset($check_order['od_receipt_price']) && (int) $check_order['od_receipt_price'] === $amount;
if ($processed)
    $processed = inicis_pro_noti_save_audit($oid, $status, $auth_date, $remote_ip, 'paid', '가상계좌 입금 통보 처리 완료');
else
    inicis_pro_noti_fail('ORDER_RECEIPT_VERIFY_FAILED', $oid, $lock_name);
inicis_pro_noti_result($processed, $lock_name);
