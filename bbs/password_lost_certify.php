<?php
include_once('./_common.php');

// 오류시 공히 Error 라고 처리하는 것은 회원정보가 있는지? 비밀번호가 틀린지? 를 알아보려는 해킹에 대비한것

$mb_no = trim($_GET['mb_no']);
$mb_datetime = trim($_GET['mb_datetime']);
$mb_lost_certify = trim($_GET['mb_lost_certify']);

// 회원아이디가 아닌 회원고유번호로 회원정보를 구한다.
$sql = " select mb_id, mb_datetime, mb_lost_certify from {$g5['member_table']} where mb_no = '$mb_no' ";
$mb  = sql_fetch($sql);
if (!trim($mb['mb_lost_certify']))
    die("Error");

// 인증 링크는 한번만 처리가 되게 한다.
sql_query(" update {$g5['member_table']} set mb_lost_certify = '' where mb_no = '$mb_no' ");

// 변경될 비밀번호가 넘어와야하고 저장된 변경비밀번호를 md5 로 변환하여 같으면 정상
if ($mb_lost_certify && $mb_datetime === sql_password($mb['mb_datetime']) && $mb_lost_certify === $mb['mb_lost_certify']) {
    sql_query(" update {$g5['member_table']} set mb_password = '{$mb['mb_lost_certify']}' where mb_no = '$mb_no' ");
    alert('비밀번호가 변경됐습니다.\\n\\n회원아이디와 변경된 비밀번호로 로그인 하시기 바랍니다.', G5_BBS_URL.'/login.php');
}
else {
    die("Error");
}
?>