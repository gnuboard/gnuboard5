<?php
include_once('./_common.php');

include_once(G5_LIB_PATH.'/shop_order_access.lib.php');
$ss_order_id = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$payReqMap = shop_order_access_load($ss_order_id, 'toss');

if(isset($payReqMap['pp_id']) && $payReqMap['pp_id']) {
    $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$payReqMap['pp_id'];
} else {
    $page_return_url  = G5_SHOP_URL.'/orderform.php';
    if ($_SESSION['ss_direct']) {
        $page_return_url .= '?sw_direct=1';
    }
}

$g5['title'] = '토스페이먼츠 eCredit서비스 결제';
$g5['body_script'] = ' onload="launchCrossPlatform(frm);"';
include_once(G5_PATH.'/head.sub.php');
?>

<form name="forderform">
<?php
if (isset($payReqMap['escrowProducts']) && is_string($payReqMap['escrowProducts'])) {
    $payReqMap['escrowProducts'] = stripslashes($payReqMap['escrowProducts']);
}
echo make_order_field($payReqMap, array());
?>
</form>

<script src="<?php echo G5_JS_URL; ?>/shop.order-state.js"></script>
<script language="javascript" src="https://js.tosspayments.com/v2/standard"></script>

<script type="text/javascript">

/*
* 수정불가.
*/
const clientKey = "<?php echo $config['cf_toss_client_key']; ?>";
const customerKey = "<?php echo $is_member ? $member['mb_id'] : md5(get_session('ss_order_id')); ?>";
const tossPayments = TossPayments(clientKey);

const payment = tossPayments.payment({ customerKey });

const frm = document.forderform;


/*
* 수정불가
*/
async function launchCrossPlatform(frm) {
    // 필수 값들 체크
    if (!frm.amountValue || !frm.amountValue.value) {
        alert('결제 금액이 설정되지 않았습니다.');
        return;
    }

    const amount = parseInt(frm.amountValue.value);
    if (isNaN(amount) || amount <= 0) {
        alert('올바른 결제 금액을 입력해주세요.');
        return;
    }

    // 기본 결제 옵션
    const paymentOptions = {
        method: frm.method.value,
        amount: {
            currency: "KRW",
            value: parseInt(frm.amountValue.value),
        },
        taxFreeAmount: parseInt(frm.taxFreeAmount.value),
        orderId: frm.orderId.value, // 고유 주문번호
        orderName: frm.orderName.value,
        successUrl: g5_order_state_url("<?php echo G5_MSHOP_URL;?>/toss/returnurl.php", frm), // 결제 요청이 성공하면 리다이렉트되는 URL
        failUrl: g5_order_state_url("<?php echo G5_MSHOP_URL;?>/toss/returnurl.php?mode=fail", frm), // 결제 요청이 실패하면 리다이렉트되는 URL
        customerEmail: frm.customerEmail.value,
        customerName: frm.customerName.value,
        customerMobilePhone: frm.customerMobilePhone.value,
    };

    // escrowProducts 추가 함수
    function addEscrowProducts(paymentMethodOptions) {
        if (frm.cardUseEscrow.value === "true") {
            if (frm.escrowProducts && frm.escrowProducts.value) {
                paymentMethodOptions.escrowProducts = JSON.parse(frm.escrowProducts.value);
            }
        }
    }

    // 결제 방법에 따른 추가 옵션
    if (frm.method.value == 'CARD') {
        // 신용카드
        paymentOptions.card = {
            flowMode: frm.cardflowMode.value, // 통합결제창 여는 옵션
            useCardPoint: frm.cardUseCardPoint.value == "true" ? true : false,
            useAppCardOnly: frm.cardUseAppCardOnly.value == "true" ? true : false,
            useEscrow: frm.cardUseEscrow.value == "true" ? true : false,
        };

        if (frm.cardflowMode.value === 'DIRECT' && frm.cardeasyPay.value) {
            paymentOptions.card.easyPay = frm.cardeasyPay.value;
        }
        // escrowProducts 추가
        addEscrowProducts(paymentOptions.card);
    } else if (frm.method.value == 'VIRTUAL_ACCOUNT') {
        // 가상계좌
        paymentOptions.virtualAccount = {
            cashReceipt: {
                type: "소득공제",
            },
            useEscrow: frm.cardUseEscrow.value == "true" ? true : false,
            validHours: 168,
        };

        // escrowProducts 추가
        addEscrowProducts(paymentOptions.virtualAccount);
    } else if (frm.method.value == 'TRANSFER') {
        // 계좌이체
        paymentOptions.transfer = {
            cashReceipt: {
                type: "소득공제",
            },
            useEscrow: frm.cardUseEscrow.value == "true" ? true : false,
        };

        // escrowProducts 추가
        addEscrowProducts(paymentOptions.transfer);
    } 

    await payment.requestPayment(paymentOptions);
}
/*
* FORM 명만  수정 가능
*/
function getFormObject() {
    return document.getElementById("forderform");
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
