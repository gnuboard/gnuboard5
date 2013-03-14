<?
include_once("./_common.php");

$mb = get_member($_SESSION[ss_mb_reg]);
// 회원정보가 없다면 초기 페이지로 이동
if (!$mb[mb_id]) 
    goto_url($g4[path]);

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";

$g4[title] = "회원가입결과";
include_once("./_head.php");
include_once("$member_skin_path/register_result.skin.php");
include_once("./_tail.php");
?>