<?php
include_once('./_common.php');
//include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

if ($is_member) { alert("이미 로그인중입니다."); goto_url(G5_URL); }

$ss_cert_mb_id = isset($_SESSION['ss_cert_mb_id']) ? trim(get_session('ss_cert_mb_id')) : '';
if(!(isset($_POST['mb_id']) && $_POST['mb_id'] === $ss_cert_mb_id)) { alert("잘못된 접근입니다."); goto_url(G5_URL); }

if($config['cf_cert_find'] != 1) alert("본인인증을 이용하여 아이디/비밀번호 찾기를 할 수 없습니다. 관리자에게 문의 하십시오.");

$g5['title'] = '패스워드 변경';
include_once(G5_PATH.'/_head.php');

$action_url = G5_HTTPS_BBS_URL."/password_reset_update.php";
include_once($member_skin_path.'/password_reset.skin.php');

include_once(G5_PATH.'/_tail.php');