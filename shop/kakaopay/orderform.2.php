<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($is_kakaopay_use) {
    $remoteaddr = $_SERVER['REMOTE_ADDR'];
    $serveraddr = $_SERVER['SERVER_ADDR'];
?>

<div id="kakaopay_request">
<input type="hidden" name="merchantTxnNum" value="<?php echo $od_id; ?>">
<input type="hidden" name="GoodsName"      value="<?php echo $goods; ?>">
<input type="hidden" name="Amt"            value="<?php echo $tot_price; ?>">
<input type="hidden" name="GoodsCnt"       value="<?php echo ($goods_count + 1); ?>">
<input type="hidden" name="BuyerEmail"     value="">
<input type="hidden" name="BuyerName"      value="">
<input type="hidden" name="prType"         value="<?php echo (is_mobile() ? 'MPM' : 'WPM'); ?>">
<input type="hidden" name="channelType"    value="4">
<input type="hidden" name="TransType"      value="0">
<input type="hidden" name="resultCode"     value="" id="resultCode">
<input type="hidden" name="resultMsg"      value="" id="resultMsg">
<input type="hidden" name="txnId"          value="" id="txnId">
<input type="hidden" name="prDt"           value="" id="prDt">
<input type="hidden" name="SPU"            value="">
<input type="hidden" name="SPU_SIGN_TOKEN" value="">
<input type="hidden" name="MPAY_PUB"       value="">
<input type="hidden" name="NON_REP_TOKEN"  value="">
<input type="hidden" name="EdiDate"        value="<?php echo($ediDate); ?>">
<input type="hidden" name="EncryptData"    value="">
<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="SupplyAmt"     value="<?php echo ((int)$comm_tax_mny + (int)$comm_free_mny); ?>">
<input type="hidden" name="GoodsVat"      value="<?php echo $comm_vat_mny; ?>">
<input type="hidden" name="ServiceAmt"    value="0">
<?php } ?>
</div>

<?php
}
?>