<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//삼성페이 또는 L.pay 사용시에만 해당함
if( ! is_inicis_simple_pay() || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}
?>

<form name="samsungpay_form" id="samsungpay_form" method="POST" action="" accept-charset="euc-kr">
<input type="hidden" name="P_OID"        value="<?php echo $od_id; ?>">
<input type="hidden" name="P_GOODS"      value="<?php echo $goods; ?>">
<input type="hidden" name="P_AMT"        value="<?php echo $tot_price; ?>">
<input type="hidden" name="P_UNAME"      value="">
<input type="hidden" name="P_MOBILE"     value="">
<input type="hidden" name="P_EMAIL"      value="">
<input type="hidden" name="P_MID"        value="<?php echo $default['de_inicis_mid']; ?>">
<input type="hidden" name="P_NEXT_URL"   value="<?php echo $next_url; ?>">
<input type="hidden" name="P_NOTI_URL"   value="<?php echo $noti_url; ?>">
<input type="hidden" name="P_RETURN_URL" value="">
<input type="hidden" name="P_HPP_METHOD" value="2">
<input type="hidden" name="P_RESERVED"   value="bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow; ?>">
<input type="hidden" name="DEF_RESERVED" value="bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow; ?>">
<input type="hidden" name="P_NOTI"       value="<?php echo $od_id; ?>">
<input type="hidden" name="P_QUOTABASE"  value="01:02:03:04:05:06:07:08:09:10:11:12"> <!-- 할부기간 설정 01은 일시불 -->
<input type="hidden" name="P_SKIP_TERMS"      value="Y">

<input type="hidden" name="good_mny"     value="<?php echo $tot_price; ?>" >

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="P_TAX"        value="">
<input type="hidden" name="P_TAXFREE"    value="">
<?php } ?>
</form>