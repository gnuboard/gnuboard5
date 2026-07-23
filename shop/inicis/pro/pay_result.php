<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

// === INIpay PRO 승인 실행 위치: 주문 저장 직전 (return.php에서 이관) ===
$inicis_pro_ctx = (isset($GLOBALS['inicis_pro_approval_ctx']) && is_array($GLOBALS['inicis_pro_approval_ctx'])) ? $GLOBALS['inicis_pro_approval_ctx'] : array();
if ($inicis_pro_ctx) {
    unset($GLOBALS['inicis_pro_approval_ctx']);
    $oid = isset($inicis_pro_ctx['oid']) ? $inicis_pro_ctx['oid'] : '';
    $auth_tid = isset($inicis_pro_ctx['auth_tid']) ? $inicis_pro_ctx['auth_tid'] : '';
    $idc = isset($inicis_pro_ctx['idc']) ? $inicis_pro_ctx['idc'] : '';
    $expected_mid = isset($inicis_pro_ctx['expected_mid']) ? $inicis_pro_ctx['expected_mid'] : '';
    $expected_amount = isset($inicis_pro_ctx['expected_amount']) ? $inicis_pro_ctx['expected_amount'] : 0;
    $pay_type = isset($inicis_pro_ctx['pay_type']) ? $inicis_pro_ctx['pay_type'] : '';
    $result_type = isset($inicis_pro_ctx['result_type']) ? $inicis_pro_ctx['result_type'] : '';
    $noti = isset($inicis_pro_ctx['noti']) ? $inicis_pro_ctx['noti'] : '';
    $device = isset($inicis_pro_ctx['device']) ? $inicis_pro_ctx['device'] : '';
    $settle_case = isset($inicis_pro_ctx['settle_case']) ? $inicis_pro_ctx['settle_case'] : '';
    $is_personal = !empty($inicis_pro_ctx['is_personal']);
    $lock_name = isset($inicis_pro_ctx['lock_name']) ? $inicis_pro_ctx['lock_name'] : '';
    $page_return_url = isset($inicis_pro_ctx['page_return_url']) ? $inicis_pro_ctx['page_return_url'] : G5_SHOP_URL;
    $audit_values = (isset($inicis_pro_ctx['audit_values']) && is_array($inicis_pro_ctx['audit_values'])) ? $inicis_pro_ctx['audit_values'] : array();

    $approval = array();
    $old_log = inicis_pro_get_log($oid);
    $old_data = isset($old_log['pro_data']) && is_array($old_log['pro_data']) ? $old_log['pro_data'] : array();
    $old_is_pro = isset($old_data['__pro']) && $old_data['__pro'] === '1';
    $old_auth_tid = isset($old_data['P_AUTH_TID']) ? $old_data['P_AUTH_TID'] : '';

    if ($old_is_pro && isset($old_log['P_STATUS']) && $old_log['P_STATUS'] === '00') {
        if ($old_auth_tid !== $auth_tid) {
            $duplicate_cancel = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'duplicate authorization', $pay_type);
            $audit_values['event_only'] = '1';
            inicis_pro_return_error('이미 처리된 주문과 다른 승인정보가 확인되어 새 결제를 중단했습니다.', $page_return_url, $lock_name, $oid, 'cancel', !empty($duplicate_cancel['success']) ? 'canceled' : 'cancel_failed', 'DUPLICATE_AUTH', $audit_values);
        }
        $approval = $old_data;
    } elseif ($old_is_pro && isset($old_log['P_STATUS']) && $old_log['P_STATUS'] === 'processing' && $old_auth_tid === $auth_tid) {
        $cancel_result = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'interrupted approval', $pay_type);
        if (!empty($cancel_result['success']))
            sql_query(" update {$g5['g5_shop_inicis_log_table']} set P_STATUS = 'cancel', P_RMESG1 = 'interrupted approval net-cancel' where oid = '".sql_escape_string($oid)."' ", false);
        inicis_pro_return_error('이전 승인 처리가 중단된 기록이 있어 안전을 위해 결제를 취소했습니다. 주문내역을 확인한 후 다시 시도해 주십시오.', $page_return_url, $lock_name, $oid, 'cancel', !empty($cancel_result['success']) ? 'canceled' : 'cancel_failed', 'INTERRUPTED_APPROVAL', $audit_values);
    } elseif ($old_is_pro && isset($old_log['P_STATUS']) && $old_log['P_STATUS'] === 'processing') {
        $old_idc = isset($old_data['P_IDCNAME']) ? strtolower($old_data['P_IDCNAME']) : '';
        $old_amount = isset($old_data['P_AMT']) ? (int) $old_data['P_AMT'] : 0;
        $old_cancel_ok = true;
        if ($old_auth_tid !== '' && $old_amount > 0) {
            $old_cancel = inicis_pro_net_cancel($old_idc, $expected_mid, $old_auth_tid, $old_amount, $oid, 'stale authorization', $pay_type);
            $old_cancel_ok = !empty($old_cancel['success']);
        }
        $new_cancel = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'duplicate authorization', $pay_type);
        inicis_pro_return_error('동일 주문의 다른 승인 처리가 확인되어 안전을 위해 결제를 중단했습니다.', $page_return_url, $lock_name, $oid, 'cancel', $old_cancel_ok && !empty($new_cancel['success']) ? 'canceled' : 'cancel_failed', 'CONCURRENT_AUTH', $audit_values);
    }

    if (empty($approval)) {
        inicis_pro_audit_write($oid, 'approval', 'approval_started', $audit_values);
        $processing = array(
            'P_STATUS' => 'processing',
            'P_RMESG' => 'approval request started',
            'P_MID' => $expected_mid,
            'P_OID' => $oid,
            'P_AUTH_TID' => $auth_tid,
            'P_AMT' => (string) $expected_amount,
            'P_TYPE' => $result_type,
            'P_NOTI' => $noti,
            'P_IDCNAME' => $idc,
            '__pro' => '1',
            '__device' => $device,
            '__environment' => inicis_pro_environment(),
            '__easy_pay' => inicis_pro_easypay_name($settle_case),
            '__order_type' => $is_personal ? 'personal' : 'order'
        );
        if (!inicis_pro_save_log($processing))
            inicis_pro_return_error('결제 승인 준비정보를 저장하지 못했습니다.', $page_return_url, $lock_name, $oid, 'approval', 'approval_failed', 'APPROVAL_LOG_FAILED', $audit_values);

        $approval_response = inicis_pro_http_post(
            'https://'.inicis_pro_idc_host($idc).'/payment/v1/rest/payAppl.ini',
            array(
                'P_MID' => $expected_mid,
                'P_AUTH_TID' => $auth_tid,
                'P_AMT' => (string) $expected_amount,
                'P_CHARSET' => 'UTF-8'
            )
        );
        if (!$approval_response['success']) {
            $communication_cancel = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'approval communication failure', $pay_type);
            $communication_status = !empty($communication_cancel['success']) ? 'cancel' : 'fail';
            sql_query(" update {$g5['g5_shop_inicis_log_table']} set P_STATUS = '$communication_status', P_RMESG1 = 'approval communication failure' where oid = '".sql_escape_string($oid)."' ", false);
            inicis_pro_return_error('KG이니시스 승인 서버와 통신하지 못했습니다. 결제 여부를 관리자에게 문의해 주십시오.', $page_return_url, $lock_name, $oid, 'approval', !empty($communication_cancel['success']) ? 'canceled' : 'communication_failed', 'APPROVAL_COMMUNICATION', $audit_values);
        }

        $approval = inicis_pro_parse_nvp($approval_response['body']);
        $approval_message = isset($approval['P_RMESG']) ? inicis_pro_cut($approval['P_RMESG'], 150) : '';
        if (!isset($approval['P_STATUS']) || $approval['P_STATUS'] !== '00') {
            $processing['P_STATUS'] = isset($approval['P_STATUS']) ? $approval['P_STATUS'] : 'fail';
            $processing['P_RMESG'] = $approval_message !== '' ? $approval_message : 'approval failed';
            inicis_pro_save_log($processing);
            inicis_pro_return_error('결제 승인 실패: '.$processing['P_RMESG'], $page_return_url, $lock_name, $oid, 'approval', 'approval_failed', $processing['P_STATUS'], $audit_values);
        }

        $approved_tid = isset($approval['P_APPL_TID']) ? inicis_pro_clean_tid($approval['P_APPL_TID']) : '';
        $approved_type = isset($approval['P_TYPE']) ? strtoupper($approval['P_TYPE']) : '';
        $approved_amount = isset($approval['P_AMT']) ? $approval['P_AMT'] : '';
        $approved_oid = isset($approval['P_OID']) ? inicis_pro_clean_oid($approval['P_OID']) : '';
        $approved_auth_tid = isset($approval['P_AUTH_TID']) ? inicis_pro_clean_tid($approval['P_AUTH_TID']) : '';
        $approval_valid = isset($approval['P_MID']) && $approval['P_MID'] === $expected_mid
            && $approved_oid === $oid && isset($approval['P_OID']) && $approval['P_OID'] === $approved_oid
            && preg_match('/^[0-9]+$/', $approved_amount) && (int) $approved_amount === (int) $expected_amount
            && $approved_auth_tid === $auth_tid && isset($approval['P_AUTH_TID']) && $approval['P_AUTH_TID'] === $approved_auth_tid
            && $approved_tid !== '' && isset($approval['P_APPL_TID']) && $approval['P_APPL_TID'] === $approved_tid
            && inicis_pro_result_type_matches($settle_case, $approved_type);

        if (!$approval_valid) {
            $mismatch_cancel = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'approval result mismatch', $pay_type);
            $processing['P_STATUS'] = !empty($mismatch_cancel['success']) ? 'cancel' : 'mismatch';
            $processing['P_RMESG'] = 'approval result mismatch';
            inicis_pro_save_log($processing);
            inicis_pro_return_error('승인 결과가 결제요청 정보와 일치하지 않아 주문을 중단했습니다. 취소 여부는 관리자에게 문의해 주십시오.', $page_return_url, $lock_name, $oid, 'validation', !empty($mismatch_cancel['success']) ? 'canceled' : 'cancel_failed', 'APPROVAL_RESULT_MISMATCH', $audit_values);
        }

        $approval['P_NOTI'] = $noti;
        $approval['P_IDCNAME'] = $idc;
        $approval['__pro'] = '1';
        $approval['__device'] = $device;
        $approval['__environment'] = inicis_pro_environment();
        $approval['__easy_pay'] = inicis_pro_easypay_name($settle_case);
        $approval['__order_type'] = $is_personal ? 'personal' : 'order';
        if (!inicis_pro_save_log($approval)) {
            $approval_log_cancel = inicis_pro_net_cancel($idc, $expected_mid, $auth_tid, $expected_amount, $oid, 'approval log failure', $pay_type);
            inicis_pro_return_error('승인정보를 저장하지 못해 결제를 취소했습니다.', $page_return_url, $lock_name, $oid, 'cancel', !empty($approval_log_cancel['success']) ? 'canceled' : 'cancel_failed', 'APPROVAL_LOG_FAILED', $audit_values);
        }
    }

    $audit_values['tid'] = isset($approval['P_APPL_TID']) ? $approval['P_APPL_TID'] : '';
    $audit_values['pay_type'] = isset($approval['P_TYPE']) ? $approval['P_TYPE'] : $audit_values['pay_type'];
    $audit_values['code'] = isset($approval['P_STATUS']) ? $approval['P_STATUS'] : '';
    $audit_values['message'] = isset($approval['P_RMESG']) ? $approval['P_RMESG'] : 'KG이니시스 승인 완료';

    $final_tid = isset($approval['P_APPL_TID']) ? inicis_pro_clean_tid($approval['P_APPL_TID']) : '';
    if ($final_tid === '' || !isset($approval['P_APPL_TID']) || $approval['P_APPL_TID'] !== $final_tid
        || !isset($approval['P_STATUS']) || $approval['P_STATUS'] !== '00'
        || !isset($approval['P_MID']) || $approval['P_MID'] !== $expected_mid
        || !isset($approval['P_OID']) || $approval['P_OID'] !== $oid
        || !isset($approval['P_AMT']) || (int) $approval['P_AMT'] !== (int) $expected_amount
        || !isset($approval['P_AUTH_TID']) || $approval['P_AUTH_TID'] !== $auth_tid
        || !isset($approval['P_NOTI']) || !inicis_pro_equals($approval['P_NOTI'], $noti)
        || !isset($approval['P_IDCNAME']) || $approval['P_IDCNAME'] !== $idc
        || !isset($approval['__device']) || $approval['__device'] !== $device
        || !isset($approval['P_TYPE']) || !inicis_pro_result_type_matches($settle_case, $approval['P_TYPE'])) {
        inicis_pro_return_error('저장된 승인정보를 검증하지 못했습니다.', $page_return_url, $lock_name, $oid, 'validation', 'validation_failed', 'SAVED_APPROVAL_INVALID', $audit_values);
    }

    inicis_pro_audit_write($oid, 'approval', 'approved', $audit_values);

    $_POST['P_TYPE'] = isset($approval['P_TYPE']) ? $approval['P_TYPE'] : '';
    $_POST['P_AUTH_DT'] = (isset($approval['P_APPL_DT']) ? $approval['P_APPL_DT'] : '').(isset($approval['P_APPL_TM']) ? $approval['P_APPL_TM'] : '');
    $_POST['P_AUTH_NO'] = isset($approval['P_APPL_NO']) ? $approval['P_APPL_NO'] : '';
    $_POST['P_HASH'] = md5($final_tid.$expected_mid.$expected_amount);

    set_session('P_TID', $final_tid);
    set_session('P_AMT', (string) $expected_amount);
    set_session('P_HASH', $_POST['P_HASH']);
    set_session('ss_inicis_pro_oid', $oid);
}


$pro_oid = isset($_POST['inicis_pro_oid']) ? inicis_pro_clean_oid($_POST['inicis_pro_oid']) : '';
$pro_log = inicis_pro_get_log($pro_oid);
$pro_data = isset($pro_log['pro_data']) && is_array($pro_log['pro_data']) ? $pro_log['pro_data'] : array();

if ($pro_oid === '' || empty($pro_data['__pro']) || $pro_data['__pro'] !== '1' || !isset($pro_log['P_STATUS']) || $pro_log['P_STATUS'] !== '00')
    alert('승인된 INIpay PRO 결제정보를 확인할 수 없습니다.');

$pro_tid = isset($pro_data['P_APPL_TID']) ? inicis_pro_clean_tid($pro_data['P_APPL_TID']) : '';
$pro_mid = isset($pro_data['P_MID']) ? $pro_data['P_MID'] : '';
$pro_amount = isset($pro_data['P_AMT']) ? (int) $pro_data['P_AMT'] : 0;
$pro_hash = md5($pro_tid.$pro_mid.$pro_amount);
$post_hash = isset($_POST['P_HASH']) ? (string) $_POST['P_HASH'] : '';

if ($pro_tid === '' || $pro_mid !== inicis_pro_get_mid(isset($pro_data['P_TYPE']) ? $pro_data['P_TYPE'] : null) || !isset($pro_data['P_OID']) || $pro_data['P_OID'] !== $pro_oid
    || !inicis_pro_equals($pro_hash, $post_hash) || !inicis_pro_equals($pro_hash, (string) get_session('P_HASH'))
    || (string) get_session('P_TID') !== $pro_tid || (int) get_session('P_AMT') !== $pro_amount
    || (string) get_session('ss_inicis_pro_oid') !== $pro_oid)
    alert('INIpay PRO 승인정보가 주문정보와 일치하지 않습니다.');

$tno = $pro_tid;
$amount = $pro_amount;
$app_time = (isset($pro_data['P_APPL_DT']) ? preg_replace('/[^0-9]/', '', $pro_data['P_APPL_DT']) : '')
    .(isset($pro_data['P_APPL_TM']) ? preg_replace('/[^0-9]/', '', $pro_data['P_APPL_TM']) : '');
$pay_method = isset($pro_data['P_TYPE']) ? strtoupper($pro_data['P_TYPE']) : '';
$pay_type = '';
switch ($pay_method) {
    case 'CARD':
        $pay_type = '신용카드';
        break;
    case 'BANK':
        $pay_type = '계좌이체';
        break;
    case 'VBANK':
        $pay_type = '가상계좌';
        break;
    case 'HPP':
    case 'MOBILE':
        $pay_type = '휴대폰';
        break;
}

$depositor = isset($pro_data['P_VACT_NAME']) ? $pro_data['P_VACT_NAME'] : (isset($pro_data['P_UNAME']) ? $pro_data['P_UNAME'] : '');
$commid = isset($pro_data['P_HPP_CORP']) ? $pro_data['P_HPP_CORP'] : (isset($pro_data['P_FN_NM']) ? $pro_data['P_FN_NM'] : '');
$mobile_no = isset($pro_data['P_HPP_NUM']) ? $pro_data['P_HPP_NUM'] : (isset($pro_data['P_APPL_NUM']) ? $pro_data['P_APPL_NUM'] : '');
$app_no = isset($pro_data['P_APPL_NO']) ? $pro_data['P_APPL_NO'] : '';
$card_name = isset($pro_data['P_CARD_ISSUER_NAME']) && $pro_data['P_CARD_ISSUER_NAME'] !== '' ? $pro_data['P_CARD_ISSUER_NAME'] : (isset($pro_data['P_FN_NM']) ? $pro_data['P_FN_NM'] : '');
$bank_name = isset($pro_data['P_FN_NM']) ? $pro_data['P_FN_NM'] : '';
$bankname = $bank_name;
$account = isset($pro_data['P_VACT_NUM']) ? $pro_data['P_VACT_NUM'] : '';
if (!empty($pro_data['P_VACT_NAME']))
    $account .= ' '.$pro_data['P_VACT_NAME'];
if ($pay_method === 'VBANK' && !empty($pro_data['P_VACT_NUM']))
    $app_no = $pro_data['P_VACT_NUM'];

if (!empty($default['de_escrow_use']) && ($pay_method === 'BANK' || $pay_method === 'VBANK'))
    $escw_yn = 'Y';

set_session('P_TID', '');
set_session('P_AMT', '');
set_session('P_HASH', '');
set_session('ss_inicis_pro_oid', '');
