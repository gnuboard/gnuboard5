<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

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
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge" /> -->
<?
if (G4_IS_MOBILE) {
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    echo "<link rel=\"stylesheet\" href=\"{$g4['url']}/css/jquery.mobile-1.3.0-beta.1.min.css\">\n";
} else {
    if (isset($administrator)) {
        echo "<link rel=\"stylesheet\" href=\"{$g4['url']}/css/adm.css\">\n";
    } else {
        echo "<link rel=\"stylesheet\" href=\"{$g4['url']}/css/default.css\">\n";
    }
}
?>
<title><?=$g4['title']?></title>
<!-- <meta http-equiv='X-UA-Compatible' content='IE=Edge'> -->
<? if (isset($administrator)) { ?>
<link rel="stylesheet" href="<?=$g4['url']?>/css/adm.css?=<?=date("md")?>">
<? } else { ?>
<link rel="stylesheet" href="<?=$g4['url']?>/css/default.css?=<?=date("md")?>">
<?}?>
<!--[if lte IE 8]>
<script src="<?=$g4['url']?>/js/html5.js"></script>
<![endif]-->
<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g4_path      = "<?=$g4['path']?>";
var g4_bbs       = "<?=$g4['bbs']?>";
var g4_bbs_img   = "<?=$g4['bbs_img']?>";
var g4_url       = "<?=$g4['url']?>";
var g4_path      = "<?=$g4['path']?>";
var g4_bbs_url   = "<?=$g4['bbs_url']?>";
var g4_bbs_path  = "<?=$g4['bbs_path']?>";
var g4_is_member = "<?=isset($is_member)?$is_member:'';?>";
var g4_is_admin  = "<?=isset($is_admin)?$is_admin:'';?>";
var g4_bo_table  = "<?=isset($bo_table)?$bo_table:'';?>";
var g4_sca       = "<?=isset($sca)?$sca:'';?>";
var g4_charset   = "<?=$g4['charset']?>";
var g4_cookie_domain = "<?=$g4['cookie_domain']?>";
var g4_is_gecko  = navigator.userAgent.toLowerCase().indexOf("gecko") != -1;
var g4_is_ie     = navigator.userAgent.toLowerCase().indexOf("msie") != -1;
<? if ($is_admin) { echo "var g4_admin = '{$g4['admin']}';"; } ?>
<?
if (!empty($g4['js_code'])) {
    foreach ($g4['js_code'] as $key=>$value) {
        echo $value."\n";
    }
}
?>
</script>
<script src="<?=$g4['url']?>/js/jquery-1.8.3.min.js"></script>
<? if (G4_IS_MOBILE) echo "<script src=\"{$g4['url']}/js/jquery.mobile-1.3.0-beta.1.min.js\"></script>\n"; ?>
<script src="<?=$g4['url']?>/js/common.js"></script>
<? if (!G4_IS_MOBILE) echo "<script src=\"{$g4['url']}/js/wrest.js\"></script>\n"; ?>
<?
if (!empty($g4['js_file'])) {
    foreach ($g4['js_file'] as $key=>$value) {
        echo "<script src=\"$value\"></script>\n";
    }
}
?>
</head>
<body>
<a id="g4_head"></a>

<?
// 쪽지를 받았나?
if (isset($member['mb_memo_call']) && $member['mb_memo_call']) {
    $mb = get_member($member['mb_memo_call'], "mb_nick");
    sql_query(" update {$g4['member_table']} set mb_memo_call = '' where mb_id = '{$member['mb_id']}' ");

    //alert($mb['mb_nick'].'님으로부터 쪽지가 전달되었습니다.', $_SERVER['REQUEST_URI'], false);
    $memo_msg = $mb['mb_nick'].'님으로부터 쪽지가 전달되었습니다.\\n\\n바로 확인하시겠습니까?';
    include_once($g4['bbs_path'].'/memocall.php');
}
?>

<?
if (G4_IS_MOBILE) {
    include_once($g4['path'].'/mobile.head.php');
}
?>
