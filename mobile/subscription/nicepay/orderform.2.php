<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'nicepay') {
    return;
}
?>
<input type="hidden" name="PayMethod" value="">
<input type="hidden" name="oid"         value="<?php echo $od_id; ?>">
<input type="hidden" name="cardNo" value="">
<input type="hidden" name="expMonth" value="">
<input type="hidden" name="expYear" value="">
<input type="hidden" name="idNo" value="">
<input type="hidden" name="cardPw" value="">

<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check();" value="주문하기" class="btn_submit"></span>
    <a href="<?php echo G5_SUBSCRIPTION_URL; ?>" class="btn_cancel">취소</a>
</div>