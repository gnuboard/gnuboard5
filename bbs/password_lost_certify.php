<?
include_once("./_common.php");

// 오류시 공히 Error 라고 처리하는 것은 회원정보가 있는지? 패스워드가 틀린지? 를 알아보려는 해킹에 대비한것

$mb_no           = trim($_GET[mb_no]);
$mb_datetime     = trim($_GET[mb_datetime]);
$mb_lost_certify = trim($_GET[mb_lost_certify]);

// 회원아이디가 아닌 회원고유번호로 회원정보를 구한다.
$sql = " select mb_id, mb_datetime, mb_lost_certify from $g4[member_table] where mb_no = '$mb_no' ";
$mb  = sql_fetch($sql);
if (!trim($mb[mb_lost_certify]))
    die("Error");

// 인증 링크는 한번만 처리가 되게 한다.
sql_query(" update $g4[member_table] set mb_lost_certify = '' where mb_no = '$mb_no' ");

// 변경될 패스워드가 넘어와야하고 저장된 변경패스워드를 md5 로 변환하여 같으면 정상
if ($mb_lost_certify && $mb_datetime === sql_password($mb[mb_datetime]) && $mb_lost_certify === $mb[mb_lost_certify]) {
    sql_query(" update $g4[member_table] set mb_password = '$mb[mb_lost_certify]' where mb_no = '$mb_no' ");
    alert("이메일로 보내드린 패스워드로 변경 하였습니다.\\n\\n회원아이디와 변경된 패스워드로 로그인 하시기 바랍니다.", "$g4[url]/$g4[bbs]/login.php");
}
else {
    die("Error");
}
?>