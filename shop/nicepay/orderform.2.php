<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="comm_tax_mny"	  value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
<input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php } ?>

<input type="hidden" name="TrKey" value=""/>                                                        <!-- 필드만 필요 -->
<input type="hidden" name="EdiDate" value="<?php echo date("YmdHis"); ?>"/>                         <!-- 전문 생성일시 -->
<input type="hidden" name="EncryptData" value=""/>                                                  <!-- 해쉬값	-->

<input type="hidden" name="pay_method"  value="">
<input type="hidden" name="GoodsName"   value="<?php echo $od_id; ?>">
<input type="hidden" name="GoodsCnt"    value="<?php echo $goods; ?>">
<input type="hidden" name="Amt"         value="<?php echo $tot_price; ?>">
<input type="hidden" name="BuyerName"   value="">
<input type="hidden" name="BuyerEmail"  value="">
<input type="hidden" name="BuyerTel"    value="">

<input type="hidden" name="RcvrName"    value="">
<input type="hidden" name="RcvrTel"     value="">
<input type="hidden" name="RcvrMail"    value="">
<input type="hidden" name="RcvrZipx"    value="">
<input type="hidden" name="RcvrAddr"    value="">

<input type="hidden" name="timestamp"   value="">
<input type="hidden" name="returnUrl"   value="<?php echo $returnUrl; ?>">
<input type="hidden" name="mKey"        value="">
<input type="hidden" name="charset"     value="UTF-8">
<input type="hidden" name="closeUrl"    value="<?php echo $closeUrl; ?>">
<input type="hidden" name="popupUrl"    value="<?php echo $popupUrl; ?>">

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="tax"         value="<?php echo $comm_vat_mny; ?>">
<input type="hidden" name="taxfree"     value="<?php echo $comm_free_mny; ?>">
<?php }