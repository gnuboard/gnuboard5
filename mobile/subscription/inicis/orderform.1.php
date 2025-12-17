<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$dateVal = date('YmdHis', G5_SERVER_TIME);

$hashData = hash("sha256",(string)$mid.(string)$od_id.(string)$dateVal.(string)$merchantKey);

$returnurl = G5_MSUBSCRIPTION_URL.'/inicis/inibill_mo_return.php';
?>

<form name="pay_form" method="POST" action="">
<input type="hidden" name="mid" value="<?php echo sanitize_input(get_subs_option('su_inicis_mid')); ?>">
<input type="hidden" name="orderid"       value="<?php echo $od_id; ?>">
<input type="hidden" name="price"        value="<?php echo sanitize_input($tot_price); ?>">
<input type="hidden" name="timestamp"      value="<?php echo sanitize_input($dateVal); ?>">
<input type="hidden" name="hashdata"     value="<?php echo sanitize_input($hashData); ?>">
<input type="hidden" name="goodname"      value="<?php echo sanitize_input($goods); ?>">
<input type="hidden" name="buyername"      value="">
<input type="hidden" name="buyertel"        value="">
<input type="hidden" name="buyeremail"   value="">
<input type="hidden" name="returnurl"   value="<?php echo sanitize_input($returnurl); ?>">
<input type="hidden" name="carduse" value="">
<input type="hidden" name="good_mny"          value="<?php echo $tot_price ?>" >
<input type="hidden" name="gopaymethod" value="" >

<?php if(get_subs_option('su_tax_flag_use')) { ?>
<input type="hidden" name="P_TAX"        value="">
<input type="hidden" name="P_TAXFREE"    value="">
<?php } ?>
</form>