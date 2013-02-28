<?
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

if ($is_member) {
    alert("이미 로그인중입니다.");
}

if (!chk_captcha()) {
    alert('스팸방지에 입력한 숫자가 틀렸습니다.');
}

include_once($member_skin_path.'/password_lost2.skin.php');
?>