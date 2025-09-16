<?php
include_once('./_common.php');

// 토스페이먼츠 class
require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

$toss = new TossPayments(
    $config['cf_toss_client_key'],
    $config['cf_toss_secret_key'],
    $config['cf_lg_mid']
);

$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$amount = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
$paymentKey = isset($_REQUEST['paymentKey']) ? $_REQUEST['paymentKey'] : '';

$toss->setPaymentData([
    'orderId' => $orderId,
    'amount' => $amount,
    'paymentKey' => $paymentKey,
]);

// 장바구니 ID 설정 (바로구매 여부 확인)
$ss_cart_id = get_session('ss_direct') ? get_session('ss_cart_direct') : get_session('ss_cart_id');

// 임시데이터에 결제 데이터 저장
$addQuery = "";
if (isset($orderId)) {
    $addQuery .= " AND od_id = '$orderId'";
}
if (isset($ss_cart_id)) {
    $addQuery .= " AND cart_id = '$ss_cart_id'";
}
if (isset($member['mb_id'])) {
    $addQuery .= " AND mb_id = '{$member['mb_id']}'";
}

if (empty($orderId) && empty($ss_cart_id)) {
    alert('주문정보가 올바르지 않습니다.');
    exit;
}

// 기존 dt_data 가져오기
$sql = "
    SELECT * FROM {$g5['g5_shop_order_data_table']}
    WHERE 1=1
        {$addQuery}
    LIMIT 1
";
$res = sql_fetch($sql);
$dt_data = [];
if (isset($res['dt_data'])) {
    $dt_data = unserialize(base64_decode($res['dt_data']));
}

// dt_data 에 결제 키 추가
if (isset($paymentKey)) {
    $dt_data['paymentKey'] = $paymentKey;
    $dt_data_new = base64_encode(serialize($dt_data));

    // 업데이트
    $sql = "
        UPDATE {$g5['g5_shop_order_data_table']} SET
            dt_data = '".$dt_data_new."'
            WHERE od_id = '$orderId'
            {$addQuery}
    ";
    sql_query($sql);
}

if(isset($payReqMap['pp_id']) && $payReqMap['pp_id']) {
    $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$payReqMap['pp_id'];
} else {
    $page_return_url  = G5_SHOP_URL.'/orderform.php';
    if ($_SESSION['ss_direct']) {
        $page_return_url .= '?sw_direct=1';
    }
}
?>
