<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// kcp 휴대폰인증파일
include_once(G4_BBS_PATH.'/kcp/kcpcert_config.php');
if(!$ordr_idxx = get_session('ss_uniqid'))
    $ordr_idxx = get_uniqid();
?>

<form name="form_auth" method="post" target="auth_popup" action="<?=$cert_url?>">
<!-- 유저네임 -->
<input type="hidden" name="user_name"    value="" />
<!-- 주문번호 -->
<input type="hidden" name="ordr_idxx" value="<?=$ordr_idxx?>">
<!-- 요청종류 -->
<input type="hidden" name="req_tx"       value="cert"/>
<!-- 인증종류 -->
<input type="hidden" name="cert_type"    value="01"/>
<!-- 웹사이트아이디 -->
<input type="hidden" name="web_siteid"   value=""/>
<!-- 노출 통신사 default 처리시 아래의 주석을 해제하고 사용하십시요
     SKT : SKT , KT : KTF , LGU+ : LGT
<input type="hidden" name="fix_commid"      value="KTF"/>
-->
<!-- 사이트코드 -->
<input type="hidden" name="site_cd"      value="<?= $site_cd ?>" />
<!-- Ret_URL : 인증결과 리턴 페이지 ( 가맹점 URL 로 설정해 주셔야 합니다. ) -->
<input type="hidden" name="Ret_URL"      value="<?=G4_BBS_URL?>/kcp/kcpcert_result.php" />
<!-- cert_otp_use 필수 ( 메뉴얼 참고)
     Y : 실명 확인 + OTP 점유 확인 , N : 실명 확인 only
-->
<input type="hidden" name="cert_otp_use" value="Y"/>
<!-- cert_enc_use 필수 (고정값 : 메뉴얼 참고) -->
<input type="hidden" name="cert_enc_use" value="Y"/>

<input type="hidden" name="res_cd"       value=""/>
<input type="hidden" name="res_msg"      value=""/>

<!-- up_hash 검증 을 위한 필드 -->
<input type="hidden" name="veri_up_hash" value=""/>

<!-- 가맹점 사용 필드 (인증완료시 리턴)-->
<input type="hidden" name="param_opt_1"  value="opt1"/>
<input type="hidden" name="param_opt_2"  value="opt2"/>
<input type="hidden" name="param_opt_3"  value="opt3"/>
</form>

<script>
// 인증창 호출 함수
function auth_type_check(user_name)
{
    var auth_form = document.form_auth;
    auth_form.user_name.value = encodeURIComponent(user_name);

    if( auth_form.ordr_idxx.value == "" )
    {
        alert( "주문번호는 필수 입니다." );

        return false;
    }
    else
    {
        if( ( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 ) == false ) // 스마트폰이 아닌경우
        {
            var return_gubun;
            var width  = 410;
            var height = 500;

            var leftpos = screen.width  / 2 - ( width  / 2 );
            var toppos  = screen.height / 2 - ( height / 2 );

            var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
            var position = ",left=" + leftpos + ", top="    + toppos;
            var AUTH_POP = window.open('','auth_popup', winopts + position);
        }


        auth_form.submit();
    }
}
</script>