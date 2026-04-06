<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'toss') return;

// 토스 공통 설정
require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

if (empty($mod_memo)) {
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
    'cancelReason' => $mod_memo,
    'cancelAmount' => (int)$tax_mny + (int)$free_mny,
    'taxFreeAmount' => (int)$free_mny,
));
if (!$toss->cancelPayment()) {
    $msg = '결제 부분취소 요청이 실패하였습니다.\\n\\n';
    if (isset($toss->responseData['message'])) {
        $msg .= '사유 : ' . $toss->responseData['message'] . '\\n';
    }
    if (isset($toss->responseData['code'])) {
        $msg .= '코드 : ' . $toss->responseData['code'];
    }
    alert($msg);
}

// 환불금액 기록
$mod_mny = (int)$tax_mny + (int)$free_mny;
$sql = " update {$g5['g5_shop_order_table']}
            set od_refund_price = od_refund_price + '$mod_mny',
                od_shop_memo = concat(od_shop_memo, \"$mod_memo\")
            where od_id = '{$od['od_id']}'";
sql_query($sql);

// 미수금 등의 정보 업데이트
$info = get_order_info($od_id);

$sql = " update {$g5['g5_shop_order_table']}
            set od_misu     = '{$info['od_misu']}',
                od_tax_mny  = '{$info['od_tax_mny']}',
                od_vat_mny  = '{$info['od_vat_mny']}',
                od_free_mny = '{$info['od_free_mny']}'
            where od_id = '$od_id' ";
sql_query($sql);