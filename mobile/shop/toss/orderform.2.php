<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>
<input type="hidden" name="method"    value="">
<input type="hidden" name="orderId"    value="<?php echo isset($od_id) ? $od_id : ''; ?>">
<input type="hidden" name="orderName"    value="<?php echo isset($goods) ? $goods : ''; ?>">
<input type="hidden" name="customerName"    value="<?php echo isset($od_name) ? $od_name : ''; ?>">
<input type="hidden" name="customerEmail"    value="<?php echo isset($od_email) ? $od_email : ''; ?>">
<input type="hidden" name="customerMobilePhone"    value="<?php echo isset($od_hp) ? $od_hp : ''; ?>">
<input type="hidden" name="cardUseEscrow"    value="false">
<input type="hidden" name="escrowProducts"    value=''>
<input type="hidden" name="cardflowMode"    value="DEFAULT">
<input type="hidden" name="cardeasyPay"    value="PAYCO">
<input type="hidden" name="cardUseCardPoint"    value="false">
<input type="hidden" name="cardUseAppCardOnly"    value="false">
<input type="hidden" name="amountCurrency"    value="KRW">
<input type="hidden" name="amountValue"    value="<?php echo isset($tot_price) ? $tot_price : 0; ?>">
<input type="hidden" name="taxFreeAmount"    value="<?php echo isset($comm_free_mny) ? $comm_free_mny : 0; ?>">
<input type="hidden" name="windowTarget"    value="iframe">

<input type="hidden" name="good_mny"    value="<?php echo $tot_price; ?>">
<?php
if($default['de_tax_flag_use']) {
?>
    <input type="hidden" name="comm_tax_mny"	  value="<?php echo isset($comm_tax_mny) ? $comm_tax_mny : 0; ?>">         <!-- 과세금액    -->
    <input type="hidden" name="comm_vat_mny"      value="<?php echo isset($comm_vat_mny) ? $comm_vat_mny : 0; ?>">         <!-- 부가세	    -->
    <input type="hidden" name="comm_free_mny"     value="<?php echo isset($comm_free_mny) ? $comm_free_mny : 0; ?>">        <!-- 비과세 금액 -->
<?php
}
?>

<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록요청" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check();" value="주문하기" class="btn_submit"></span>
    <a href="<?php echo G5_SHOP_URL; ?>" class="btn_cancel">취소</a>
</div>
