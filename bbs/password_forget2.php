<?
die("이 프로그램은 더 이상 사용하지 않습니다. 그누보드 4.32.09 를 참고하세요.");
include_once("./_common.php");

// 토큰 생성
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);
$norobot_key = substr($token, 0, rand(4,6));
set_session("ss_norobot_key", $norobot_key);

if ($_POST[pass_mb_id])
    $sql = " select mb_id, mb_password_q from $g4[member_table] where mb_id = '$_POST[pass_mb_id]' ";
else if ($_POST[mb_name] && $_POST[mb_jumin])
    $sql = " select mb_id, mb_password_q from $g4[member_table] where mb_name = '$_POST[mb_name]' and mb_jumin = '".sql_password($_POST[mb_jumin])."' ";
else if ($_POST[mb_name] && $_POST[mb_email])
    $sql = " select mb_id, mb_password_q from $g4[member_table] where mb_name = '$_POST[mb_name]' and mb_email = '$_POST[mb_email]' ";
else 
    alert("올바른 방법으로 접근하여 주십시오.");

$mb = sql_fetch($sql);
if (!$mb[mb_id]) 
    alert("입력하신 내용으로는 회원정보가 존재하지 않습니다.");
else if (is_admin($mb[mb_id])) 
    alert("관리자 아이디는 접근 불가합니다.");

$g4[title] = "패스워드 찾기 2단계";
include_once("$g4[path]/head.sub.php");

// 081022 : CSRF 보안 결함으로 인한 코드 수정
$mb[mb_password_q] = get_text($mb[mb_password_q]);

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/password_forget2.skin.php");

include_once("$g4[path]/tail.sub.php");
?>