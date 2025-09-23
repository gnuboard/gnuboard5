<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
?>

<script language="javascript" src="https://js.tosspayments.com/v2/standard"></script>

<script type="text/javascript">

/*
* 수정불가.
*/
const clientKey = "<?php echo $config['cf_toss_client_key']; ?>";
const customerKey = "<?php echo isset($member['mb_id']) ? $member['mb_id'] : ''; ?>";
const tossPayments = TossPayments(clientKey);

const payment = tossPayments.payment({ customerKey });


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
        successUrl: "<?php echo G5_SHOP_URL;?>/toss/returnurl.php", // 결제 요청이 성공하면 리다이렉트되는 URL
        failUrl: "<?php echo G5_SHOP_URL;?>/toss/returnurl.php?mode=fail", // 결제 요청이 실패하면 리다이렉트되는 URL
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
            easyPay: frm.cardeasyPay.value,
            useCardPoint: frm.cardUseCardPoint.value == "true" ? true : false,
            useAppCardOnly: frm.cardUseAppCardOnly.value == "true" ? true : false,            
            useEscrow: frm.cardUseEscrow.value == "true" ? true : false,
        };

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
<?php }