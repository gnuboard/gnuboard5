<?
define('_CAPTCHA_', 1);
include_once('./_common.php');

if ($is_member) {
    alert("이미 로그인중입니다.");
}

$g4['title'] = '회원아이디/패스워드 찾기';
include_once($g4['path'].'/head.sub.php');

if ($g4['https_url'])
    $action_url = "{$g4['https_url']}/{$g4['bbs']}/password_lost2.php";
else
    $action_url = "{$g4['bbs_url']}/password_lost2.php";

$member_skin_path = $g4['path'].'/skin/member/'.$config['cf_member_skin'];
include_once($member_skin_path.'/password_lost.skin.php');

include_once($g4['path'].'/tail.sub.php');
?>