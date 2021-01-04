<?php
include_once('./_common.php');

// 봇의 메일 링크 크롤링을 방지합니다.
if(function_exists('check_mail_bot')){ check_mail_bot($_SERVER['REMOTE_ADDR']); }

run_event('password_lost_certify_before');

// 오류시 공히 Error 라고 처리하는 것은 회원정보가 있는지? 비밀번호가 틀린지? 를 알아보려는 해킹에 대비한것

$mb_no = isset($_GET['mb_no']) ? preg_replace('#[^0-9]#', '', trim($_GET['mb_no'])) : 0;
$mb_nonce = isset($_GET['mb_nonce']) ? trim($_GET['mb_nonce']) : '';

// 회원아이디가 아닌 회원고유번호로 회원정보를 구한다.
$sql = " select mb_id, mb_lost_certify from {$g5['member_table']} where mb_no = '$mb_no' ";
$mb  = sql_fetch($sql);
if (strlen($mb['mb_lost_certify']) < 33)
    die("Error");

// 인증 링크는 한번만 처리가 되게 한다.
sql_query(" update {$g5['member_table']} set mb_lost_certify = '' where mb_no = '$mb_no' ");

// 인증을 위한 난수가 제대로 넘어온 경우 임시비밀번호를 실제 비밀번호로 바꿔준다.
if ($mb_nonce === substr($mb['mb_lost_certify'], 0, 32)) {
    $new_password_hash = substr($mb['mb_lost_certify'], 33);
    sql_query(" update {$g5['member_table']} set mb_password = '$new_password_hash' where mb_no = '$mb_no' ");

    run_event('password_lost_certify_after', $mb, $mb_nonce);

    alert('비밀번호가 변경됐습니다.\\n\\n회원아이디와 변경된 비밀번호로 로그인 하시기 바랍니다.', G5_BBS_URL.'/login.php');
}
else {
    die("Error");
}