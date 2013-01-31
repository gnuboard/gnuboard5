<?
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

if ($is_member) {
    alert("이미 로그인중입니다.");
}

$g4['title'] = '회원아이디/패스워드 찾기';
include_once(G4_PATH.'/head.sub.php');

if ($g4['https_url'])
    //$action_url = "{$g4['https_url']}/{$g4['bbs']}/password_lost2.php";
    $action_url = G4_BBS_URL."/password_lost2.php";
else
    $action_url = G4_BBS_URL."/password_lost2.php";

include_once($member_skin_path.'/password_lost.skin.php');

include_once(G4_PATH.'/tail.sub.php');
?>