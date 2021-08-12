<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// KAKAOPAY SIRK***** 전용아이디 사용시 ( KG 이니시스 )
if( ! $is_kakaopay_use || ('inicis' === $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}

if(!function_exists('get_inicis_app_scheme')){
    function get_inicis_app_scheme(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $iPod = stripos($user_agent,"iPod");
        $iPhone  = stripos($user_agent,"iPhone");
        $iPad    = stripos($user_agent,"iPad");

        if( $iPod || $iPhone || $iPad ){    //IOS 의 앱브라우저에서 ISP결제시 리다이렉트 safari로 돌아가는 문제가 있음
            if( preg_match('/NAVER\(inapp;/', $user_agent) ){       //네이버
                return 'app_scheme=naversearchapp://&';
            }
            else if( preg_match('/CriOS/', $user_agent) ){          //크롬
                return 'app_scheme=googlechromes://&';
            }
            else if( preg_match('/DaumDevice/', $user_agent) ){      //다음
                return 'app_scheme=daumapps://&';
            }
            else if( preg_match('/KAKAOTALK/', $user_agent) ){          //카카오톡
                return 'app_scheme=kakaotalk://&';
            }
            else if( preg_match('/(FBAN|FBAV)/', $user_agent) ){        //페이스북
                return 'app_scheme=fb://&';
            }
        }

        return '';
    }
}
?>

<form name="inicis_kakaopay_form" id="inicis_kakaopay_form" method="POST" action="" accept-charset="euc-kr">
<input type="hidden" name="P_OID"        value="<?php echo $od_id; ?>">
<input type="hidden" name="P_GOODS"      value="<?php echo $goods; ?>">
<input type="hidden" name="P_AMT"        value="<?php echo $tot_price; ?>">
<input type="hidden" name="P_UNAME"      value="">
<input type="hidden" name="P_MOBILE"     value="">
<input type="hidden" name="P_EMAIL"      value="">
<input type="hidden" name="P_MID"        value="<?php echo $default['de_kakaopay_mid']; ?>">
<input type="hidden" name="P_NEXT_URL"   value="<?php echo $next_url; ?>">
<input type="hidden" name="P_NOTI_URL"   value="<?php echo $noti_url; ?>">
<input type="hidden" name="P_RETURN_URL" value="">
<input type="hidden" name="P_HPP_METHOD" value="2">
<input type="hidden" name="P_RESERVED"   value="<?php echo get_inicis_app_scheme(); ?>bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow; ?>">
<input type="hidden" name="DEF_RESERVED" value="<?php echo get_inicis_app_scheme(); ?>bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow; ?>">
<input type="hidden" name="P_NOTI"       value="<?php echo $od_id; ?>">
<input type="hidden" name="P_QUOTABASE"  value="01:02:03:04:05:06:07:08:09:10:11:12"> <!-- 할부기간 설정 01은 일시불 -->
<input type="hidden" name="P_SKIP_TERMS"      value="Y">

<input type="hidden" name="good_mny"     value="<?php echo $tot_price; ?>" >

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="P_TAX"        value="">
<input type="hidden" name="P_TAXFREE"    value="">
<?php } ?>
</form>

<script type="text/javascript">

if( typeof g5_shop_url === 'undefined' ){
    var g5_shop_url = g5_url+"/shop";
}

function getTxnId(pf) {
    var inicis_kakaopay_form = document.inicis_kakaopay_form;

    var paymethod = "";
    var width = 330;
    var height = 480;
    var xpos = (screen.width - width) / 2;
    var ypos = (screen.width - height) / 2;
    var position = "top=" + ypos + ",left=" + xpos;
    var features = position + ", width=320, height=440";
    var p_reserved = inicis_kakaopay_form.DEF_RESERVED.value;
    inicis_kakaopay_form.P_RESERVED.value = p_reserved;
    
    paymethod = "wcard";
    
    inicis_kakaopay_form.P_RESERVED.value = inicis_kakaopay_form.P_RESERVED.value.replace("&useescrow=Y", "")+"&d_kakaopay=Y";

    if( ! jQuery("form[name='sm_form']").length ){
        alert("해당 폼이 존재 하지 않는 결제오류입니다.");
        return false;
    }

    inicis_kakaopay_form.P_AMT.value = inicis_kakaopay_form.good_mny.value = document.sm_form.good_mny.value; 
    inicis_kakaopay_form.P_UNAME.value = pf.od_name.value;
    inicis_kakaopay_form.P_MOBILE.value = pf.od_hp.value;
    inicis_kakaopay_form.P_EMAIL.value = pf.od_email.value;

    <?php if($default['de_tax_flag_use']) { ?>
    inicis_kakaopay_form.P_TAX.value = pf.comm_vat_mny.value;
    inicis_kakaopay_form.P_TAXFREE = pf.comm_free_mny.value;
    <?php } ?>

    inicis_kakaopay_form.P_RETURN_URL.value = "<?php echo $return_url.$od_id; ?>";
    inicis_kakaopay_form.action = "https://mobile.inicis.com/smart/" + paymethod + "/";

    // 주문 정보 임시저장
    var order_data = $(pf).serialize();
    var save_result = "";
    $.ajax({
        type: "POST",
        data: order_data,
        url: g5_shop_url+"/ajax.orderdatasave.php",
        cache: false,
        async: false,
        success: function(data) {
            save_result = data;
        }
    });

    if(save_result) {
        alert(save_result);
        return;
    }

    inicis_kakaopay_form.submit();

    return false;
}
</script>