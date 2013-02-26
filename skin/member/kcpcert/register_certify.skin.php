<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($w == '') {
    // 본인인증체크
    $kcpcert_no = trim($_POST['kcpcert_no']);
    if(!$kcpcert_no)
        alert('휴대폰인증이 되지 않았습니다. 휴대폰인증을 해주세요.', "", true, true);

    // 본인인증 hash 체크
    $reg_hp = preg_replace("/[^0-9]/", "", $mb_hp);
    $reg_hash = md5($reg_hp.$mb_name.$kcpcert_no);
    if(get_session('ss_kcpcert_hash') != $reg_hash)
        alert('휴대폰인증 정보가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.', "", true, true);
}
?>