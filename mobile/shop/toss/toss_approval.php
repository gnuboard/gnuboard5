<?php
include_once('./_common.php');

// 토스페이먼츠 class
require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

// 개인결제 ID와 주문 ID 설정
$ss_order_id       = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$ss_personalpay_id = get_session('ss_personalpay_id');

// 장바구니 ID 설정 (바로구매 여부 확인)
$ss_cart_id = get_session('ss_direct') ? get_session('ss_cart_direct') : get_session('ss_cart_id');

// WHERE 조건 추가용 변수
$addQuery = "";
if (!empty($ss_order_id)) {
    $addQuery .= " AND od_id = '{$ss_order_id}'";
}
if (isset($member['mb_id']) && $member['mb_id'] !== '') {
    $addQuery .= " AND mb_id = '{$member['mb_id']}'";
}

// 개인결제가 아닌 경우 장바구니 ID 조건 추가
if (empty($ss_personalpay_id)) {
    if (!empty($ss_cart_id)) {
        $addQuery .= " AND cart_id = '{$ss_cart_id}'";
    }
}

// 최종 검증 (원래 로직 유지)
if (empty($ss_order_id) || (empty($ss_personalpay_id) && empty($ss_cart_id))) {
    alert('주문정보가 올바르지 않습니다.');
    exit;
}

// 기존 dt_data 가져오기
$sql = "
    SELECT * FROM {$g5['g5_shop_order_data_table']}
    WHERE 1=1
        {$addQuery}
";
$res = sql_fetch($sql);
$dt_data = [];
if (isset($res['dt_data'])) {
    $dt_data = unserialize(base64_decode($res['dt_data']));
}

$payReqMap = $dt_data;

$_SESSION['PAYREQ_MAP'] = $payReqMap;

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
foreach($payReqMap as $key => $value) {
    if (isset($_REQUEST[$key]) && $_REQUEST[$key]) {
        $value = $_REQUEST[$key];
    }
    if (is_array($value)) {
        $value = implode(',', $value);
    }
    if ($key === 'escrowProducts') {
        $value = str_replace("\\", "", $value);
        echo '<input type="hidden" name="'.$key.'" value=\''.$value.'\'>'.PHP_EOL;
    } else {
        echo '<input type="hidden" name="'.$key.'" value="'.$value.'">'.PHP_EOL;
    }
}
?>
</form>

<script language="javascript" src="https://js.tosspayments.com/v2/standard"></script>

<script type="text/javascript">

/*
* 수정불가.
*/
const clientKey = "<?php echo $config['cf_toss_client_key']; ?>";
const customerKey = "<?php echo isset($member['mb_id']) ? $member['mb_id'] : ''; ?>";
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
        successUrl: "<?php echo G5_MSHOP_URL;?>/toss/returnurl.php", // 결제 요청이 성공하면 리다이렉트되는 URL
        failUrl: "<?php echo G5_MSHOP_URL;?>/toss/returnurl.php?mode=fail", // 결제 요청이 실패하면 리다이렉트되는 URL
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

<?php
include_once(G5_PATH.'/tail.sub.php');