<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$begin_time = get_microtime();

if (!isset($g4['title']))
    $g4['title'] = $config['cf_title'];

// 현재 접속자
//$lo_location = get_text($g4[title]);
//$lo_location = $g4[title];
// 게시판 제목에 ' 포함되면 오류 발생
$lo_location = addslashes($g4['title']);
if (!$lo_location)
    $lo_location = $_SERVER['REQUEST_URI'];
//$lo_url = $g4['url'] . $_SERVER['REQUEST_URI'];
$lo_url = $_SERVER['REQUEST_URI'];
if (strstr($lo_url, "/$g4[admin]/") || $is_admin == 'super') $lo_url = '';

/*
// 만료된 페이지로 사용하시는 경우
header("Cache-Control: no-cache"); // HTTP/1.1
header("Expires: 0"); // rfc2616 - Section 14.21
header("Pragma: no-cache"); // HTTP/1.0
*/

$g4_css = "";
if (G4_IS_MOBILE) $g4_css = "mobile";
else $g4_css = "default";
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<? if (G4_IS_MOBILE) {?><meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width"><? } ?>
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge" /> -->
<title><?=$g4['title']?></title>
<? if (isset($administrator)) { ?>
<link rel="stylesheet" href="<?=G4_CSS_URL?>/adm.css?=<?=date("md")?>">
<? } else { ?>
<link rel="stylesheet" href="<?=G4_CSS_URL?>/<?=$g4_css?>.css?=<?=date("md")?>">
<?}?>
<!--[if lte IE 8]>
<script src="<?=G4_JS_URL?>/html5.js"></script>
<![endif]-->
<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g4_url       = "<?=G4_URL?>";
var g4_bbs_url   = "<?=G4_BBS_URL?>";
var g4_is_member = "<?=isset($is_member)?$is_member:'';?>";
var g4_is_admin  = "<?=isset($is_admin)?$is_admin:'';?>";
var g4_bo_table  = "<?=isset($bo_table)?$bo_table:'';?>";
var g4_sca       = "<?=isset($sca)?$sca:'';?>";
var g4_charset   = "<?=$g4['charset']?>";
var g4_cookie_domain = "<?=$g4['cookie_domain']?>";
var g4_is_gecko  = navigator.userAgent.toLowerCase().indexOf("gecko") != -1;
var g4_is_ie     = navigator.userAgent.toLowerCase().indexOf("msie") != -1;
<? if ($is_admin) { echo "var g4_admin = '{$g4['admin']}';"; } ?>
</script>
<script src="<?=G4_JS_URL?>/jquery-1.8.3.min.js"></script>
<script src="<?=G4_JS_URL?>/common.js"></script>
<script src="<?=G4_JS_URL?>/wrest.js"></script>
</head>
<body>
<a id="g4_head"></a>