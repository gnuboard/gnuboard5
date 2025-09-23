<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}

$param_opt_1 = isset($_REQUEST['param_opt_1']) ? clean_xss_tags($_REQUEST['param_opt_1'], 1, 1) : '';
$param_opt_2 = isset($_REQUEST['param_opt_2']) ? clean_xss_tags($_REQUEST['param_opt_2'], 1, 1) : '';
$param_opt_3 = isset($_REQUEST['param_opt_3']) ? clean_xss_tags($_REQUEST['param_opt_3'], 1, 1) : '';
?>

<form name="pay_form" method="post" action="<?php echo G5_MSUBSCRIPTION_URL; ?>/kcp/kcp_api_trade_reg.php">
    <input type="hidden" name="ordr_idxx"      value="<?php echo sanitize_input($od_id); ?>">            <!-- 주문번호           -->
    <input type="hidden" name="good_mny"       value="<?php echo sanitize_input($tot_price); ?>">             <!-- 휴대폰 결제금액    -->
    <input type="hidden" name="good_name"      value="<?php echo sanitize_input($goods); ?>">            <!-- 상품명             -->
    <input type="hidden" name="buyr_name"      value="">            <!-- 주문자명           -->
    <input type="hidden" name="buyr_tel1"      value="">            <!-- 주문자 전화번호    -->
    <input type="hidden" name="buyr_tel2"      value="">            <!-- 주문자 휴대폰번호  -->
    <input type="hidden" name="buyr_mail"      value="">            <!-- 주문자 E-mail      -->
    <input type="hidden" name="settle_method"      value="">
    <!-- 추가 파라미터 -->
    <input type="hidden" name="param_opt_1"  value="<?php echo sanitize_input($param_opt_1); ?>"/>
    <input type="hidden" name="param_opt_2"  value="<?php echo sanitize_input($param_opt_2); ?>"/>
    <input type="hidden" name="param_opt_3"  value="<?php echo sanitize_input($param_opt_3); ?>"/>
</form>