<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($is_kakaopay_use) {
?>

<div id="kakaopay_request">
<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="SupplyAmt"     value="<?php echo ((int)$comm_tax_mny + (int)$comm_free_mny); ?>">
<input type="hidden" name="GoodsVat"      value="<?php echo $comm_vat_mny; ?>">
<input type="hidden" name="ServiceAmt"    value="0">
<?php } ?>
<?php if($is_mobile_order){ ?>
<input type="hidden" name="is_inicis_mobile_kakaopay" value="mobile" >
<?php } ?>
</div>

<?php
}