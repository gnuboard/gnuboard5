<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

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

$od_id = isset($od['od_id']) ? $od['od_id'] : (isset($pp['pp_id']) ? $pp['pp_id'] : '');

if (!$toss->getPaymentByOrderId($od_id)) {
    alert('결제정보를 가져올 수 없습니다.');
}

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