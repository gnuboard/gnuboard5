<?php
include_once('./_common.php');

if($w == '')
    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
else
    alert('잘못된 접근입니다', G5_URL);

if(!$mb_id)
    alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password_re']) : '';
$mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';


if ($w == '' && !$mb_password)
alert('비밀번호가 넘어오지 않았습니다.');
if($w == '' && $mb_password != $mb_password_re)
alert('비밀번호가 일치하지 않습니다.');

$sql_password = "";
if ($mb_password)
    $sql_password = "mb_password = '".get_encrypt_string($mb_password)."' ";

sql_query("update {$g5['member_table']} set {$sql_password} where mb_id = '{$mb_id}'");

goto_url(G5_BBS_URL.'/login.php');