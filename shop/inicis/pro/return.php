<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

@header('Cache-Control: no-store, no-cache, must-revalidate');

if (!function_exists('inicis_pro_return_error')) {
    function inicis_pro_return_error($message, $return_url, $lock_name, $oid = '', $stage = 'validation', $status = 'validation_failed', $code = '', $values = array())
    {
        if (!is_array($values))
            $values = array();
        $values['message'] = $message;
        if ($code !== '')
            $values['code'] = $code;
        inicis_pro_audit_write($oid, $stage, $status, $values);
        inicis_pro_unlock($lock_name);
        alert($message, $return_url);
    }
}

if (!function_exists('inicis_pro_restore_order_value')) {
    function inicis_pro_restore_order_value($value, $memo)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $item) {
                if (!is_scalar($key))
                    continue;
                $result[$key] = inicis_pro_restore_order_value($item, false);
            }
            return $result;
        }

        if (!is_scalar($value))
            return '';

        $value = stripslashes((string) $value);
        if ($memo)
            return clean_xss_tags(strip_tags($value), 0, 0, 0, 0);

        return clean_xss_tags(strip_tags($value));
    }
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST')
    alert('올바른 결제 결과 요청이 아닙니다.', G5_SHOP_URL);

if (!inicis_pro_is_enabled())
    alert('INIpay PRO 결제가 활성화되어 있지 않습니다.', G5_SHOP_URL);

$status = isset($_POST['P_STATUS']) ? trim((string) $_POST['P_STATUS']) : '';
$message = isset($_POST['P_RMESG']) ? inicis_pro_cut($_POST['P_RMESG'], 150) : '';
$oid_raw = isset($_POST['P_OID']) ? trim((string) $_POST['P_OID']) : '';
$oid = inicis_pro_clean_oid($oid_raw);
$mid = isset($_POST['P_MID']) ? trim((string) $_POST['P_MID']) : '';
$auth_tid_raw = isset($_POST['P_AUTH_TID']) ? trim((string) $_POST['P_AUTH_TID']) : '';
$auth_tid = inicis_pro_clean_tid($auth_tid_raw);
$amount_raw = isset($_POST['P_AMT']) ? trim((string) $_POST['P_AMT']) : '';
$result_type = isset($_POST['P_TYPE']) ? strtoupper(trim((string) $_POST['P_TYPE'])) : '';
$noti = isset($_POST['P_NOTI']) ? trim((string) $_POST['P_NOTI']) : '';
$idc = isset($_POST['P_IDCNAME']) ? strtolower(trim((string) $_POST['P_IDCNAME'])) : '';
$page_return_url = G5_SHOP_URL.'/orderform.php';
$lock_name = '';
$session_order_oid = inicis_pro_clean_oid(get_session('ss_order_id'));
$session_personal_oid = inicis_pro_clean_oid(get_session('ss_personalpay_id'));
$session_matches_oid = $oid !== '' && ($session_order_oid === $oid || $session_personal_oid === $oid);

// 인증 실패 응답은 주문번호 등 일부 필드가 누락될 수 있으므로 결과코드를 먼저 처리한다.
if ($status !== '00') {
    $error_text = $message !== '' ? $message : '결제 인증에 실패했습니다.';
    $error_code = substr(preg_replace('/[^A-Za-z0-9_-]/', '', $status), 0, 12);
    if ($error_code === '')
        $error_code = '확인불가';

    $failure_return_url = $page_return_url;
    $session_personal_id = inicis_pro_clean_oid(get_session('ss_personalpay_id'));
    if ($session_personal_id !== '')
        $failure_return_url = G5_SHOP_URL.'/personalpayform.php?pp_id='.$session_personal_id;
    elseif (get_session('ss_direct'))
        $failure_return_url .= '?sw_direct=1';

    inicis_pro_return_error('결제 인증 실패: '.$error_text.' (코드 '.$error_code.')', $failure_return_url, '', $session_matches_oid ? $oid : '', 'authentication', 'authentication_failed', $error_code, array(
        'mid' => $mid,
        'auth_tid' => $auth_tid,
        'amount' => preg_match('/^[0-9]+$/', $amount_raw) ? (int) $amount_raw : 0,
        'pay_type' => $result_type,
        'device' => defined('G5_IS_MOBILE') && G5_IS_MOBILE ? 'MOBILE' : 'WEB',
        'source' => defined('G5_IS_MOBILE') && G5_IS_MOBILE ? 'mobile' : 'web'
    ));
}

if ($oid === '' || $oid !== $oid_raw)
    alert('결제 주문번호가 올바르지 않습니다.', G5_SHOP_URL);

$temp_row = sql_fetch(" select * from {$g5['g5_shop_order_data_table']}
                         where od_id = '".sql_escape_string($oid)."'
                           and dt_pg = 'inicis'
                         order by dt_time desc
                         limit 1 ");
if (empty($temp_row['od_id'])) {
    $exists_order = get_shop_order_data($oid);
    if (!empty($exists_order['od_tno'])) {
        inicis_pro_audit_order_saved($oid, $exists_order['od_tno'], 'order', 'system');
        set_session('ss_order_id', $oid);
        exists_inicis_shop_order($oid, array(), $exists_order['od_time'], $exists_order['od_ip']);
        exit;
    }

    $exists_personal = get_shop_order_data($oid, 'personal');
    if (!empty($exists_personal['pp_tno'])) {
        inicis_pro_audit_order_saved($oid, $exists_personal['pp_tno'], 'personal', 'system');
        set_session('ss_personalpay_id', $oid);
        set_session('ss_personalpay_hash', md5($exists_personal['pp_id'].$exists_personal['pp_price'].$exists_personal['pp_time']));
        exists_inicis_shop_order($oid, $exists_personal, $exists_personal['pp_time'], $exists_personal['pp_ip']);
        exit;
    }

    inicis_pro_return_error('임시 주문정보가 없어서 결제를 승인할 수 없습니다.', G5_SHOP_URL, '', $oid, 'validation', 'validation_failed', 'TEMP_ORDER_MISSING', array('source' => 'web'));
}

$order_data = @unserialize(base64_decode($temp_row['dt_data']));
if (!is_array($order_data))
    inicis_pro_return_error('임시 주문정보를 확인할 수 없습니다.', G5_SHOP_URL, '', $oid, 'validation', 'validation_failed', 'TEMP_ORDER_INVALID', array('source' => 'web'));

$is_personal = !empty($order_data['pp_id']);
if ($is_personal)
    $page_return_url = G5_SHOP_URL.'/personalpayform.php?pp_id='.$oid;
elseif (!empty($order_data['sw_direct']))
    $page_return_url .= '?sw_direct=1';

$settle_key = $is_personal ? 'pp_settle_case' : 'od_settle_case';
$settle_case = isset($order_data[$settle_key]) ? $order_data[$settle_key] : '';
// MID·HashKey는 PG가 생략할 수 있는 P_TYPE 대신 주문의 결제수단으로 확정해
// 결제요청 시점의 서명과 일치시킨다.
$pay_type = inicis_pro_pay_type($settle_case);
$expected_mid = inicis_pro_get_mid($pay_type);
$hashkey = inicis_pro_get_hashkey($pay_type);
$expected_amount = inicis_pro_expected_amount($order_data, $temp_row);
$device = inicis_pro_check_noti($noti, $oid, $expected_amount, $hashkey);
$audit_source = $device === 'MOBILE' ? 'mobile' : 'web';
$audit_values = array(
    'mid' => $mid,
    'auth_tid' => $auth_tid,
    'member_id' => isset($temp_row['mb_id']) ? $temp_row['mb_id'] : '',
    'amount' => $expected_amount,
    'pay_type' => $result_type !== '' ? $result_type : inicis_pro_pay_type($settle_case),
    'environment' => inicis_pro_environment(),
    'easy_pay' => inicis_pro_easypay_name($settle_case),
    'device' => $device,
    'order_type' => $is_personal ? 'personal' : 'order',
    'source' => $audit_source
);
$validation_audit_oid = ($device !== '' || $session_matches_oid) ? $oid : '';

if ($expected_mid === '' || $hashkey === '' || $mid !== $expected_mid)
    inicis_pro_return_error('가맹점 결제정보가 일치하지 않아 주문을 중단했습니다.', $page_return_url, '', $validation_audit_oid, 'validation', 'validation_failed', 'MID_MISMATCH', $audit_values);
if ($expected_amount <= 0 || !preg_match('/^[0-9]+$/', $amount_raw) || (int) $amount_raw !== (int) $expected_amount)
    inicis_pro_return_error('결제금액이 주문정보와 일치하지 않아 주문을 중단했습니다.', $page_return_url, '', $validation_audit_oid, 'validation', 'validation_failed', 'AMOUNT_MISMATCH', $audit_values);
if ($auth_tid === '' || $auth_tid !== $auth_tid_raw || inicis_pro_idc_host($idc) === '')
    inicis_pro_return_error('결제 승인 정보가 올바르지 않아 주문을 중단했습니다.', $page_return_url, '', $validation_audit_oid, 'validation', 'validation_failed', 'AUTH_INFO_INVALID', $audit_values);
if ($device === '')
    inicis_pro_return_error('주문 검증정보가 일치하지 않아 주문을 중단했습니다.', $page_return_url, '', $validation_audit_oid, 'validation', 'validation_failed', 'ORDER_SIGNATURE_MISMATCH', $audit_values);
// 일반 인증결과에서는 P_TYPE이 생략될 수 있으며 최종 결제수단은 승인결과에서 검증한다.
if ($result_type !== '' && !inicis_pro_result_type_matches($settle_case, $result_type))
    inicis_pro_return_error('결제수단이 주문정보와 일치하지 않아 주문을 중단했습니다.', $page_return_url, '', $validation_audit_oid, 'validation', 'validation_failed', 'PAY_TYPE_MISMATCH', $audit_values);

inicis_pro_audit_write($oid, 'authentication', 'authentication_received', $audit_values);

$lock_name = inicis_pro_lock($oid);
if ($lock_name === '')
    inicis_pro_return_error('주문 처리가 진행 중입니다. 잠시 후 주문내역을 확인해 주십시오.', $page_return_url, '', $oid, 'order', 'order_saving', 'ORDER_LOCK_BUSY', $audit_values);

$exists_order = get_shop_order_data($oid);
if (!empty($exists_order['od_tno'])) {
    inicis_pro_audit_order_saved($oid, $exists_order['od_tno'], 'order', 'system');
    inicis_pro_unlock($lock_name);
    set_session('ss_order_id', $oid);
    exists_inicis_shop_order($oid, array(), $exists_order['od_time'], $exists_order['od_ip']);
    exit;
}
$exists_personal = get_shop_order_data($oid, 'personal');
if (!empty($exists_personal['pp_tno'])) {
    inicis_pro_audit_order_saved($oid, $exists_personal['pp_tno'], 'personal', 'system');
    inicis_pro_unlock($lock_name);
    set_session('ss_personalpay_id', $oid);
    set_session('ss_personalpay_hash', md5($exists_personal['pp_id'].$exists_personal['pp_price'].$exists_personal['pp_time']));
    exists_inicis_shop_order($oid, $exists_personal, $exists_personal['pp_time'], $exists_personal['pp_ip']);
    exit;
}

$_POST = array();
foreach ($order_data as $key => $value) {
    if (!is_string($key) || !preg_match('/^[A-Za-z0-9_]+$/', $key))
        continue;
    $_POST[$key] = inicis_pro_restore_order_value($value, $key === 'od_memo');
}

$_POST['inicis_pro'] = '1';
$_POST['inicis_pro_oid'] = $oid;
$_POST['res_cd'] = '00';
// 모바일 주문/개인결제의 결제등록 완료 확인(P_HASH 존재 여부)을 PRO 흐름에서도 통과시킨다.
// 실제 서명값은 승인 완료 후 pay_result.php에서 다시 채워 넣는다.
$_POST['P_HASH'] = '1';

if ($is_personal) {
    $pp_row = get_shop_order_data($oid, 'personal');
    if (empty($pp_row['pp_id']))
        inicis_pro_return_error('개인결제 정보를 확인할 수 없습니다.', $page_return_url, $lock_name, $oid, 'validation', 'validation_failed', 'PERSONALPAY_MISSING', $audit_values);

    set_session('ss_personalpay_id', $oid);
    set_session('ss_personalpay_hash', md5($pp_row['pp_id'].$pp_row['pp_price'].$pp_row['pp_time']));
    set_session('ss_order_id', $oid);
} else {
    set_session('ss_order_id', $oid);
    set_session('ss_order_inicis_id', $oid);
    set_session('ss_direct', !empty($order_data['sw_direct']) ? 1 : 0);
    if (!empty($order_data['sw_direct']))
        set_session('ss_cart_direct', $temp_row['cart_id']);
    else
        set_session('ss_cart_id', $temp_row['cart_id']);
}

if (!empty($temp_row['mb_id'])) {
    $member = get_member($temp_row['mb_id']);
    $is_member = !empty($member['mb_id']);
    if (!$is_member)
        inicis_pro_return_error('주문 회원정보를 확인할 수 없습니다.', $page_return_url, $lock_name, $oid, 'validation', 'validation_failed', 'MEMBER_MISSING', $audit_values);
} else {
    if (!is_array($member))
        $member = array();
    $member['mb_id'] = '';
    $is_member = false;
}

$field_names = shop_order_data_fields($is_personal ? 1 : 0);
foreach ($field_names as $field_name)
    $$field_name = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

// 승인은 주문 저장 직전(inicis/pro/pay_result.php)에서 실행하도록 필요한 값을 넘긴다.
$GLOBALS['inicis_pro_approval_ctx'] = array(
    'oid' => $oid,
    'auth_tid' => $auth_tid,
    'idc' => $idc,
    'expected_mid' => $expected_mid,
    'expected_amount' => $expected_amount,
    'pay_type' => $pay_type,
    'result_type' => $result_type,
    'noti' => $noti,
    'device' => $device,
    'settle_case' => $settle_case,
    'is_personal' => $is_personal,
    'lock_name' => $lock_name,
    'page_return_url' => $page_return_url,
    'audit_values' => $audit_values
);

inicis_pro_audit_write($oid, 'order', 'order_saving', $audit_values);

if ($is_personal) {
    $pp_id = $oid;
    $good_mny = $expected_amount;
    $_POST['pp_id'] = $oid;
    $_POST['good_mny'] = $expected_amount;
    if ($device === 'MOBILE')
        include G5_MSHOP_PATH.'/personalpayformupdate.php';
    else
        include G5_SHOP_PATH.'/personalpayformupdate.php';
} else {
    $od_send_cost = isset($_POST['od_send_cost']) ? (int) $_POST['od_send_cost'] : 0;
    $od_send_cost2 = isset($_POST['od_send_cost2']) ? (int) $_POST['od_send_cost2'] : 0;
    if ($device === 'MOBILE')
        include G5_MSHOP_PATH.'/orderformupdate.php';
    else
        include G5_SHOP_PATH.'/orderformupdate.php';
}

inicis_pro_unlock($lock_name);
exit;
