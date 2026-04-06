<?php
include_once('./_common.php');

// 결제 실패 처리인 경우
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'fail') {
    $code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
    $message = isset($_REQUEST['message']) ? trim($_REQUEST['message']) : '';

    alert('결제에 실패하였습니다.\\n\\n[' . $code . '] ' . $message, G5_SHOP_URL . '/orderform.php');
    exit;
}

$g5['title'] = '토스페이먼츠 결제인증 완료처리';
$g5['body_script'] = ' onload="setTossResult();"';
include_once(G5_PATH.'/head.sub.php');

// 토스페이먼츠 결제인증 성공시 인증키 주문 임시데이터에 업데이트
$paymentKey = isset($_REQUEST['paymentKey']) ? trim($_REQUEST['paymentKey']) : '';
$orderId = isset($_REQUEST['orderId']) ? trim($_REQUEST['orderId']) : '';
$amount = isset($_REQUEST['amount']) ? trim($_REQUEST['amount']) : '';

if (empty($paymentKey) || empty($orderId)) {
    alert('결제정보가 올바르지 않습니다.', G5_SHOP_URL);
    exit;
}

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$orderId' ";
$row = sql_fetch($sql);

$data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();


// 주문 임시데이터에 paymentKey 업데이트
$data['paymentKey'] = $paymentKey;
$data_new = base64_encode(serialize($data));
$sql = " update {$g5['g5_shop_order_data_table']} set dt_data = '$data_new' where od_id = '$orderId' limit 1 ";
sql_query($sql);

if(isset($data['pp_id']) && $data['pp_id']) {
    $order_action_url = G5_HTTPS_SHOP_URL.'/personalpayformupdate.php';
} else {
    $order_action_url = G5_HTTPS_SHOP_URL.'/orderformupdate.php';
}
?>

<?php
$exclude = array();

echo '<form name="forderform" method="post" action="'.$order_action_url.'" autocomplete="off">'.PHP_EOL;

echo make_order_field($data, $exclude);

echo '</form>'.PHP_EOL;
?>

<script type="text/javascript">
function setTossResult() {
    try {
        document.forderform.submit();
    } catch (e) {
        alert(e.message);
    }
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');