<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

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

<form name="sm_form" method="POST" action="" accept-charset="euc-kr">
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
<input type="hidden" name="P_RESERVED"   value="<?php echo get_inicis_app_scheme(); ?>bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow.$inicis_cardpoint; ?>">
<input type="hidden" name="DEF_RESERVED" value="<?php echo get_inicis_app_scheme(); ?>bank_receipt=N&twotrs_isp=Y&block_isp=Y<?php echo $useescrow.$inicis_cardpoint; ?>">
<input type="hidden" name="P_NOTI"       value="<?php echo $od_id; ?>">
<input type="hidden" name="P_QUOTABASE"  value="01:02:03:04:05:06:07:08:09:10:11:12"> <!-- 할부기간 설정 01은 일시불 -->
<input type="hidden" name="P_SKIP_TERMS"      value="">

<input type="hidden" name="good_mny"     value="<?php echo $tot_price; ?>" >

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="P_TAX"        value="">
<input type="hidden" name="P_TAXFREE"    value="">
<?php } ?>
</form>