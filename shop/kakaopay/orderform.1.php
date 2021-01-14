<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! $is_kakaopay_use) return;

if( $is_mobile_order ){
    include_once(G5_SHOP_PATH.'/kakaopay/mobile_orderform.1.php');
    return;
}

// PC 결제에서는 이니시스 결제를 같이 설정하면 중복 오류 문제가 일어나므로 SIRK***** 를 사용하는 카카오페이( 이니시스결제 )를 활성화하지 않습니다.
if( $default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use'] || ('inicis' == $default['de_pg_service']) ){
?>
<script>
function getTxnId(frm) {
    alert('결제 설정에 문제가 있습니다. ( 중복설정문제 )');
    return false;
}
</script>
<?php
    return;
}

include_once(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
add_javascript('<script language="javascript" type="text/javascript" src="'.$stdpay_js_url.'" charset="UTF-8"></script>', 10);
?>

<form id="inicis_kakaopay_request" name="inicis_kakaopay_request" method="POST">

<?php /* 주문폼 자바스크립트 에러 방지를 위해 추가함 */ ?>
<input type="hidden" name="good_mny"    value="">
<?php
if($default['de_tax_flag_use']) {
?>
<input type="hidden" name="comm_tax_mny"	  value="">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="">         <!-- 부가세	    -->
<input type="hidden" name="comm_free_mny"     value="">        <!-- 비과세 금액 -->
<?php
}
?>

<input type="hidden" name="version" value="1.0" >
<input type="hidden" name="mid" value="<?php echo $default['de_kakaopay_mid']; ?>">
<input type="hidden" name="goodname" value="<?php echo isset($goods) ? get_text($goods) : ''; ?>">
<input type="hidden" name="oid" value="<?php echo $od_id; ?>">
<input type="hidden" name="price" value="<?php echo $tot_price; ?>" >
<input type="hidden" name="currency" value="WON" >

<input type="hidden" name="buyername"   value="">
<input type="hidden" name="buyeremail"  value="">
<input type="hidden" name="parentemail" value="">
<input type="hidden" name="buyertel"    value="">
<input type="hidden" name="recvname"    value="">
<input type="hidden" name="recvtel"     value="">
<input type="hidden" name="recvaddr"    value="">
<input type="hidden" name="recvpostnum" value="">

<input type="hidden" name="timestamp"   value="">
<input type="hidden" name="signature"   value="">
<input type="hidden" name="returnUrl"   value="<?php echo $returnUrl; ?>">
<input type="hidden" name="mKey"        value="" >
<input type="hidden" name="gopaymethod" value="">
<input type="hidden" name="acceptmethod" value="<?php echo $acceptmethod; ?>">
<input type="hidden" name="charset"     value="UTF-8">
<input type="hidden" name="payViewType" value="overlay">
<input type="hidden" name="closeUrl"    value="<?php echo $closeUrl; ?>">
<input type="hidden" name="popupUrl"    value="<?php echo $popupUrl; ?>">
<input type="hidden" name="nointerest"  value="<?php echo $cardNoInterestQuota ?>" >
<input type="hidden" name="quotabase"   value="<?php echo $cardQuotaBase ?>" >	
<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="tax"         value="">
<input type="hidden" name="taxfree"     value="">
<?php } ?>
</form>

<script type="text/javascript">

    //var inicis_kakaopay_request = jQuery("#inicis_pay_form").length ? "inicis_pay_form" : "inicis_kakaopay_request";
    var inicis_kakaopay_request = "inicis_kakaopay_request";

    if( typeof g5_shop_url === 'undefined' ){
        var g5_shop_url = g5_url+"/shop";
    }

    function inicis_kakao_signature(frm)
    {
        // 데이터 암호화 처리
        var result = true;
        //var ajax_str_url = (inicis_kakaopay_request == "inicis_pay_form") ? "/inicis/makesignature.php" : "/kakaopay/makesignature.php";
        var ajax_str_url = "/kakaopay/makesignature.php";

        $.ajax({
            url: g5_shop_url+ajax_str_url,
            type: "POST",
            data: {
                price : frm.good_mny.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                if(data.error == "") {
                    frm.timestamp.value = data.timestamp;
                    frm.signature.value = data.sign;
                    frm.mKey.value = data.mKey;
                } else {
                    alert(data.error);
                    result = false;
                }
            }
        });

        return result;
    }

	function getTxnId(frm) {

        var pf = document.forderform,
            inicis_kk_form = document.forms[inicis_kakaopay_request];
        
        inicis_kk_form.removeAttribute("target");
        inicis_kk_form.gopaymethod.value = "onlykakaopay";
        inicis_kk_form.acceptmethod.value = "cardonly";

        inicis_kk_form.price.value = inicis_kk_form.good_mny.value = pf.good_mny.value;
        inicis_kk_form.goodname.value = (typeof pf.od_goods_name != "undefined") ? pf.od_goods_name.value : "";
        
        if( inicis_kk_form.goodname.value == "" ){
            if( jQuery("#LGD_PRODUCTINFO").length ){
                inicis_kk_form.goodname.value = jQuery("#LGD_PRODUCTINFO").val();
            } else if( jQuery("input[name=good_name]").length ){
                inicis_kk_form.goodname.value = jQuery("input[name=good_name]").val();
            }
        }

        inicis_kk_form.buyername.value   = pf.od_name.value;
        inicis_kk_form.buyeremail.value  = pf.od_email.value;
        inicis_kk_form.buyertel.value    = pf.od_hp.value ? pf.od_hp.value : pf.od_tel.value;
        inicis_kk_form.recvname.value    = pf.od_b_name.value;
        inicis_kk_form.recvtel.value     = pf.od_b_hp.value ? pf.od_b_hp.value : pf.od_b_tel.value;
        inicis_kk_form.recvpostnum.value = pf.od_b_zip.value;
        inicis_kk_form.recvaddr.value    = pf.od_b_addr1.value + " " +pf.od_b_addr2.value;

        <?php if($default['de_tax_flag_use']) { ?>
            inicis_kk_form.comm_tax_mny.value = pf.comm_tax_mny.value;
            inicis_kk_form.comm_vat_mny.value = pf.comm_vat_mny.value;
            inicis_kk_form.comm_free_mny.value = pf.comm_free_mny.value;
            inicis_kk_form.tax.value = pf.comm_vat_mny.value;
            inicis_kk_form.taxfree.value = pf.comm_free_mny.value;
        <?php } ?>

        // 주문 정보 임시저장
        var order_data = $(pf).serialize();
        var save_result = "";
        $.ajax({
            type: "POST",
            data: order_data,
            url: g5_url+"/shop/ajax.orderdatasave.php",
            cache: false,
            async: false,
            success: function(data) {
                save_result = data;
            }
        });

        if(save_result) {
            alert(save_result);
            return false;
        }
        
        if(inicis_kakao_signature(inicis_kk_form)) {
            setTimeout(function(){
                INIStdPay.pay(inicis_kakaopay_request);
            }, 1);
        }

        return false;
	}
</script>