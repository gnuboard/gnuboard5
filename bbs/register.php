<?
include_once('./_common.php');

// 로그인중인 경우 회원가입 할 수 없습니다.
if ($is_member) {
    goto_url(G4_PATH);
}

// 세션을 지웁니다.
set_session("ss_mb_reg", "");

if (G4_HTTPS_URL) {
    $register_action_url = G4_HTTPS_URL.'/bbs/register_form.php';
} else {
    $register_action_url = G4_BBS_URL.'/register_form.php';
}

$g4['title'] = '회원가입약관';
include_once('./_head.php');
include_once($member_skin_path.'/register.skin.php');
include_once('./_tail.php');
?>
