<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (function_exists('shop_order_state_cancel')) shop_order_state_cancel('cancel_pending');

// 토스 공통 설정
require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

if (empty($cancel_msg)) {
    alert('취소사유를 입력해 주세요.');
}

$toss = new TossPayments(
    $config['cf_toss_client_key'],
    $config['cf_toss_secret_key'],
    $config['cf_lg_mid']
);

$toss->setPaymentHeader();

$cancel_runtime = function_exists('shop_order_runtime') ? shop_order_runtime() : array('active'=>'');
$cancel_order_id = $cancel_runtime['active'] !== '' ? $cancel_runtime['active'] :
    (isset($od['od_id']) ? (string)$od['od_id'] : (isset($pp['pp_id']) ? (string)$pp['pp_id'] : (string)get_session('ss_order_id')));

if (!$toss->getPaymentByOrderId($cancel_order_id)) {
    alert('결제정보를 가져올 수 없습니다.');
}
if ($cancel_runtime['active'] !== '') {
    $cancel_state = shop_order_state_row($cancel_order_id);
    if (!isset($toss->responseData['orderId'],$toss->responseData['paymentKey']) ||
        $toss->responseData['orderId'] !== $cancel_order_id || $toss->responseData['paymentKey'] !== $cancel_state['payment_key']) shop_order_access_fail();
}
if (isset($toss->responseData['status']) && $toss->responseData['status'] === 'CANCELED') {
    if (function_exists('shop_order_state_cancel')) shop_order_state_cancel('cancelled');
    return;
}
$toss->headers[] = 'Idempotency-Key: g5-cancel-'.hash('sha256', $cancel_order_id.$toss->responseData['paymentKey']);

$toss->setCancelData(array(
    'paymentKey' => $toss->responseData['paymentKey'],
    'cancelReason' => $cancel_msg,
));
if (!$toss->cancelPayment()) {
    $msg = '결제 취소에 실패하였습니다.\\n';
    if (isset($toss->responseData['message'])) {
        $msg .= '사유 : ' . $toss->responseData['message'] . '\\n';
    }
    if (isset($toss->responseData['code'])) {
        $msg .= '코드 : ' . $toss->responseData['code'];
    }
    alert($msg);
}
if (function_exists('shop_order_state_cancel')) shop_order_state_cancel('cancelled');
