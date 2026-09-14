<?php
include_once('./_common.php');

include_once(G5_LIB_PATH.'/shop_order_access.lib.php');
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');

if (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'fail') shop_order_state_abort_pending();
$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$paymentKey = isset($_REQUEST['paymentKey']) ? $_REQUEST['paymentKey'] : '';
$amount = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
$data = shop_order_access_payment($orderId, $paymentKey, $amount);

$g5['title'] = '토스페이먼츠 결제인증 완료처리';
$g5['body_script'] = ' onload="setTossResult();"';
include_once(G5_PATH.'/head.sub.php');

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