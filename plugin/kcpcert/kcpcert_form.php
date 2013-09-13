<?php
include_once('./_common.php');

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'hp');

// kcp 휴대폰인증파일
include_once(G5_KCPCERT_PATH.'/kcpcert_config.php');

$ordr_idxx = get_session('ss_uniqid');
if(!$ordr_idxx)
    $ordr_idxx = get_uniqid();
?>

<form name="form_auth" method="post" target="auth_popup" action="<?php echo $cert_url ?>">
<!-- 유저네임 -->
<input type="hidden" name="user_name"    value="" />
<!-- 주문번호 -->
<input type="hidden" name="ordr_idxx"    value="<?php echo $ordr_idxx ?>">
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
<input type="hidden" name="site_cd"      value="<?php echo $site_cd; ?>" />
<!-- Ret_URL : 인증결과 리턴 페이지 ( 가맹점 URL 로 설정해 주셔야 합니다. ) -->
<input type="hidden" name="Ret_URL"      value="<?php echo G5_KCPCERT_URL; ?>/kcpcert_result.php" />
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
document.form_auth.submit();
</script>