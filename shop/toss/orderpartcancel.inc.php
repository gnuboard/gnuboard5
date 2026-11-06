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

$od_id = (string)$od['od_id'];
$payment_key = $od['od_tno'];
$refund_before = (int)$od['od_refund_price'];
$balance_before = (int)$od['od_receipt_price'] - $refund_before;
$amount = (int)$tax_mny + (int)$free_mny;
$balance_after = $balance_before - $amount;
$reconcile = 'PG 거래내역과 주문 환불액을 대조해 주십시오. 확인 전에는 취소를 다시 요청하지 마십시오.';

if (!$toss->getPaymentByOrderId($od_id)) {
    alert('결제정보를 가져올 수 없습니다.');
}
$payment = $toss->responseData;
if (!isset($payment['orderId'], $payment['paymentKey'], $payment['balanceAmount'])
    || $payment['orderId'] !== $od_id || $payment['paymentKey'] !== $payment_key
    || $payment['balanceAmount'] !== $balance_before) {
    alert($reconcile);
}

$info = get_order_info($od_id);
if (!$info || $amount > -(int)$info['od_misu']) {
    alert('주문 금액이 변경됐습니다. 취소 가능 금액을 다시 확인해 주십시오.');
}
// 한 환불 단계에서는 요청을 재전송해도 같은 키를 사용한다.
$toss->headers[] = 'Idempotency-Key: g5-part-'.hash('sha256', serialize(array($od_id, $payment_key, $refund_before)));
$toss->setCancelData(array(
    'paymentKey' => $payment_key,
    'cancelReason' => stripslashes($mod_memo),
    'cancelAmount' => $amount,
    'taxFreeAmount' => (int)$free_mny,
));
if (!$toss->cancelPayment()) {
    $code = isset($toss->responseData['code']) ? $toss->responseData['code'] : '';
    alert('토스 부분취소 결과를 확인할 수 없습니다. '.$code.' '.$reconcile);
}

$result = $toss->responseData;
$status = $balance_after === 0 ? 'CANCELED' : 'PARTIAL_CANCELED';
if (!isset($result['orderId'], $result['paymentKey'], $result['balanceAmount'], $result['status'], $result['cancels'])
    || $result['orderId'] !== $od_id || $result['paymentKey'] !== $payment_key
    || $result['balanceAmount'] !== $balance_after || $result['status'] !== $status
    || !is_array($result['cancels']) || !$result['cancels']) {
    alert($reconcile);
}
$cancel = array();
foreach ($result['cancels'] as $entry) {
    if (isset($entry['transactionKey'], $result['lastTransactionKey'])
        && $entry['transactionKey'] !== '' && $entry['transactionKey'] === $result['lastTransactionKey']) {
        $cancel = $entry;
        break;
    }
}
if (!isset($cancel['cancelAmount'], $cancel['refundableAmount'], $cancel['taxFreeAmount'], $cancel['cancelStatus'])
    || $cancel['cancelStatus'] !== 'DONE'
    || $cancel['cancelAmount'] !== $amount || $cancel['refundableAmount'] !== $balance_after
    || $cancel['taxFreeAmount'] !== (int)$free_mny) {
    alert($reconcile);
}

// get_order_info의 미수금은 환불액만큼 증가한다. 환불액과 요약을 한 번에 기록한다.
$misu_after = (int)$info['od_misu'] + $amount;
$memo_sql = sql_real_escape_string(stripslashes($mod_memo));
$key_sql = sql_real_escape_string($payment_key);
$sql = "update {$g5['g5_shop_order_table']}
           set od_refund_price = od_refund_price + '$amount',
               od_misu = '$misu_after',
               od_tax_mny = '{$info['od_tax_mny']}',
               od_vat_mny = '{$info['od_vat_mny']}',
               od_free_mny = '{$info['od_free_mny']}',
               od_shop_memo = concat(od_shop_memo, '$memo_sql')
         where od_id = '$od_id' and BINARY od_tno = '$key_sql'
           and od_refund_price = '$refund_before' and od_misu = '{$od['od_misu']}'
           and od_receipt_price = '{$od['od_receipt_price']}'";
if (!sql_query($sql, false) || get_sql_affected_rows() !== 1) {
    alert('PG 부분취소 후 주문을 갱신하지 못했습니다. '.$reconcile);
}
