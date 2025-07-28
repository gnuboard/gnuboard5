<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<!-- SDK 추가 -->
<script src="https://js.tosspayments.com/v2/standard"></script>

<script>
// ------  SDK 초기화 ------
<?php // @docs https://docs.tosspayments.com/sdk/v2/js#토스페이먼츠-초기화 ?>
const clientKey = "<?php echo get_subs_option('su_tosspayments_api_clientkey'); ?>";
const customerKey = "<?php echo $od_id; ?>";
const tossPayments = TossPayments(clientKey);

// 회원 결제
<?php // @docs https://docs.tosspayments.com/sdk/v2/js#tosspaymentspayment ?>
const payment = tossPayments.payment({ customerKey });

// ------ '카드 등록하기' 버튼 누르면 결제창 띄우기 ------
<?php // @docs https://docs.tosspayments.com/sdk/v2/js#paymentrequestpayment ?>
      
async function requestBillingAuth(params) {
    await payment.requestBillingAuth({
        method: "CARD", // 자동결제(빌링)은 카드만 지원합니다
        successUrl: "<?php echo G5_SUBSCRIPTION_URL; ?>/tosspayments/billing.php", // 요청이 성공하면 리다이렉트되는 URL
        failUrl: "<?php echo G5_SUBSCRIPTION_URL; ?>/tosspayments/fail.php",    // 요청이 실패하면 리다이렉트되는 URL
        customerEmail: params.customerEmail,
        customerName: params.customerName
    });
}

 // function generateRandomString() {
 //   return window.btoa(Math.random()).slice(0, 20);
 // }
</script>