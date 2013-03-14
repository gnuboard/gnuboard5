<?
include_once("./_common.php");

if (!$member[mb_id]) 
    alert("로그인 한 회원만 접근하실 수 있습니다.");

/*
if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER[REQUEST_URI]);
*/

$g4[title] = "회원 패스워드 확인";
include_once("./_head.php");

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/member_confirm.skin.php");

include_once("./_tail.php");
?>
