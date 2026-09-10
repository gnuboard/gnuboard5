<?php
if (!defined('_GNUBOARD_')) exit;

// 레거시 모바일 승인/통보/복귀 경로는 주문 검증 전에 이미 승인되었을 수 있다.
// POST의 거래번호를 사용하지 않고 기존 pay_result.php와 같은 세션 검증을 적용한다.
include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');
$cart_cancel_tid = get_session('P_TID');
$cart_cancel_amount = get_session('P_AMT');
$cart_cancel_hash = isset($_POST['P_HASH']) && is_string($_POST['P_HASH']) ? $_POST['P_HASH'] : '';
if (!$cart_cancel_tid || (int) $cart_cancel_amount <= 0 ||
    $cart_cancel_hash !== md5($cart_cancel_tid.$default['de_inicis_mid'].$cart_cancel_amount))
    return;

include_once(G5_SHOP_PATH.'/inicis/libs/inicis_youngcart_fn.php');
$cart_cancel_method = get_type_inicis_paymethod($od_settle_case);
$cart_cancel_response = $cart_cancel_method ? inicis_tid_cancel(array(
    'paymethod' => $cart_cancel_method,
    'tid' => $cart_cancel_tid,
    'msg' => '장바구니 검증 실패',
    'audit' => false
)) : '';
$cart_cancel_result = json_decode($cart_cancel_response, true);
$cart_cancel_success = is_array($cart_cancel_result) && isset($cart_cancel_result['resultCode']) && $cart_cancel_result['resultCode'] === '00';
$cart_cancel_status = $cart_cancel_success ? 'cancel' : 'cancel_failed';

// 성공을 확인하기 전에 취소 완료로 기록하지 않는다. 실패 거래는 관리자 확인용으로 남긴다.
$cart_cancel_oid = sql_escape_string(get_session('ss_order_id'));
$cart_cancel_log_tid = sql_escape_string($cart_cancel_tid);
$cart_cancel_log_message = $cart_cancel_success ? '장바구니 검증 실패: 승인 취소 완료' : '장바구니 검증 실패: 승인 취소 결과 확인 필요';
$cart_cancel_time = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$cart_cancel_mid = sql_escape_string($default['de_inicis_mid']);
$cart_cancel_log_amount = (int) $cart_cancel_amount;
$cart_cancel_old_log = sql_fetch(" select oid from {$g5['g5_shop_inicis_log_table']} where oid = '$cart_cancel_oid' ");
if (!empty($cart_cancel_old_log['oid'])) {
    sql_query(" update {$g5['g5_shop_inicis_log_table']} set P_STATUS = '$cart_cancel_status', P_RMESG1 = '$cart_cancel_log_message', P_AUTH_DT = '$cart_cancel_time' where oid = '$cart_cancel_oid' and P_TID = '$cart_cancel_log_tid' ", false);
} else {
    sql_query(" insert into {$g5['g5_shop_inicis_log_table']} (oid, P_TID, P_MID, P_AMT, P_STATUS, P_RMESG1, P_AUTH_DT, post_data) values ('$cart_cancel_oid', '$cart_cancel_log_tid', '$cart_cancel_mid', '$cart_cancel_log_amount', '$cart_cancel_status', '$cart_cancel_log_message', '$cart_cancel_time', '') ", false);
}
error_log('KVE-2026-2345: inicis '.$cart_cancel_status.' oid='.get_session('ss_order_id').' tid='.$cart_cancel_tid);
if (function_exists('add_order_post_log')) add_order_post_log($cart_cancel_log_message);
if ($cart_cancel_success) {
    set_session('P_TID', '');
    set_session('P_AMT', '');
    set_session('P_HASH', '');
    $cart_validation_error .= ' 결제 승인은 취소되었습니다.';
} else {
    $cart_validation_error .= ' 결제 취소 결과를 확인하지 못했습니다. 재결제 전에 상점에 문의해 주십시오.';
}
