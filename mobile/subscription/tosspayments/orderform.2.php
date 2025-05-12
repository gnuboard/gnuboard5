<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>

<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check();" value="주문하기" class="btn_submit"></span>
    <a href="<?php echo G5_SUBSCRIPTION_URL; ?>" class="btn_cancel">취소</a>
</div>