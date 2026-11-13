<?php
include_once('./_common.php');

// 본인인증 비밀번호 재설정은 재설정 화면에서 발급한 1회용 토큰을 가진
// 같은 출처의 POST 요청만 허용한다. (KVE-2026-3199)
if ($is_member) alert('이미 로그인중입니다.', G5_URL);
if ($config['cf_cert_find'] != 1) alert('본인인증을 이용하여 아이디/비밀번호 찾기를 할 수 없습니다. 관리자에게 문의 하십시오.', G5_URL);
if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') alert('올바른 방법으로 이용해 주십시오.', G5_URL);
check_request_origin();

$mb_id = g5_cert_find_member_id();
$token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
$token_valid = g5_check_password_reset_token($mb_id, $token);
$mb_dupinfo = get_session('ss_cert_dupinfo');
$mb_dupinfo = is_string($mb_dupinfo) ? trim($mb_dupinfo) : '';

if(!$mb_id) alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.', G5_URL);

if(!$token_valid) alert('올바른 방법으로 이용해 주십시오. 비밀번호 찾기를 다시 진행해 주십시오.', G5_URL);

if(!$mb_dupinfo) alert('잘못된 접근입니다.', G5_URL);

$sql_mb_id = sql_escape_string($mb_id);
$sql_mb_dupinfo = sql_escape_string($mb_dupinfo);
$mb_check = sql_fetch("select * from {$g5['member_table']} where mb_id = '{$sql_mb_id}' AND mb_dupinfo = '{$sql_mb_dupinfo}'");

if(!$mb_check) alert('잘못된 접근입니다.', G5_URL);

$mb_password    = isset($_POST['mb_password']) && is_string($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_password_re = isset($_POST['mb_password_re']) && is_string($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';


if (!$mb_password)
    alert('비밀번호가 넘어오지 않았습니다.');
if ($mb_password != $mb_password_re)
    alert('비밀번호가 일치하지 않습니다.');

$sql_password = "mb_password = '".get_encrypt_string($mb_password)."' ";

sql_query("update {$g5['member_table']} set {$sql_password} where mb_id = '{$sql_mb_id}' AND mb_dupinfo = '{$sql_mb_dupinfo}'");

// 비밀번호를 바꾸면 기존 자동 로그인을 해제한다.
if (isset($g5['member_auto_login_table'])) {
    sql_query(" delete from {$g5['member_auto_login_table']} where mb_id = '{$sql_mb_id}' ", false);
}

g5_clear_cert_find();

goto_url(G5_BBS_URL.'/login.php');