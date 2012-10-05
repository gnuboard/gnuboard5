<?
include_once("./_common.php");

$g4[title] = "로그인";
include_once("./_head.php");

$p = parse_url($url);
if ($p['scheme'] || $p['host']) {
    alert("url에 도메인을 지정할 수 없습니다.");
}

// 이미 로그인 중이라면
if ($member[mb_id])
{
    if ($url)
        goto_url($url);
    else
        goto_url($g4[path]);
}

if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER[REQUEST_URI]);

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";

include_once("$member_skin_path/login.skin.php");

include_once("./_tail.php");
?>
