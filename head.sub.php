<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$begin_time = get_microtime();

if (!isset($g4['title'])) {
    $g4['title'] = $config['cf_title'];
    $g4_head_title = $g4['title'];
}
else {
    $g4_head_title = $g4['title']; // 상태바에 표시될 제목
    $g4_head_title .= " : ".$config['cf_title'];
}

// 현재 접속자
// 게시판 제목에 ' 포함되면 오류 발생
$lo_location = addslashes($g4['title']);
if (!$lo_location)
    $lo_location = $_SERVER['REQUEST_URI'];
$lo_url = $_SERVER['REQUEST_URI'];
if (strstr($lo_url, '/'.G4_ADMIN_DIR.'/') || $is_admin == 'super') $lo_url = '';

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
<? if (G4_IS_MOBILE) {?><meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width"><? } ?>
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge" /> -->
<title><?=$g4_head_title?></title>
<? if (defined('G4_IS_ADMIN')) { ?>
<link rel="stylesheet" href="<?=G4_CSS_URL?>/admin.css?=<?=date("md")?>">
<? } else { ?>
<link rel="stylesheet" href="<?=G4_CSS_URL?>/<?=(G4_IS_MOBILE?'mobile':'default')?>.css?=<?=date("md")?>">
<?}?>
<? // 스킨의 style sheet 불러옴
if(isset($board_skin_path))
    echo get_skin_stylesheet($board_skin_path);
if(isset($member_skin_path))
    echo get_skin_stylesheet($member_skin_path);
if(isset($new_skin_path))
    echo get_skin_stylesheet($new_skin_path);
if(isset($search_skin_path))
    echo get_skin_stylesheet($search_skin_path);
if(isset($connect_skin_path))
    echo get_skin_stylesheet($connect_skin_path);
if(isset($poll_skin_path))
    echo get_skin_stylesheet($poll_skin_path);
?>
<!--[if lte IE 8]>
<script src="<?=G4_JS_URL?>/html5.js"></script>
<![endif]-->
<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g4_url       = "<?=G4_URL?>";
var g4_bbs_url   = "<?=G4_BBS_URL?>";
var g4_img_url   = "<?=G4_IMG_URL?>";
var g4_is_member = "<?=isset($is_member)?$is_member:'';?>";
var g4_is_admin  = "<?=isset($is_admin)?$is_admin:'';?>";
var g4_is_mobile = "<?=G4_IS_MOBILE?>";
var g4_bo_table  = "<?=isset($bo_table)?$bo_table:'';?>";
var g4_sca       = "<?=isset($sca)?$sca:'';?>";
var g4_cookie_domain = "<?=G4_COOKIE_DOMAIN?>";
<? if ($is_admin) { echo 'var g4_admin_url = "'.G4_ADMIN_URL.'";'; }
?>
</script>
<script src="<?=G4_JS_URL?>/jquery-1.8.3.min.js"></script>
<script src="<?=G4_JS_URL?>/common.js"></script>
<script src="<?=G4_JS_URL?>/wrest.js"></script>
<? if(G4_IS_MOBILE) { ?>
<script>
    set_cookie("device_width", screen.width, 6, g4_cookie_domain);
</script>
<? } ?>
<? if (!defined('G4_IS_ADMIN')) { echo $config['cf_add_script']; } ?>
</head>
<body>
<?
if ($is_member) { // 회원이라면 로그인 중이라는 메세지를 출력해준다.
    if ($is_admin == 'super') $sr_admin_msg = "최고관리자 ";
    else if ($is_admin == 'group') $sr_admin_msg = "그룹관리자 ";
    else if ($is_admin == 'board') $sr_admin_msg = "게시판관리자 ";
?>
    <div id="hd_login_msg"><?=$sr_admin_msg?><?=$member['mb_nick']?>님 로그인 중</div>
<? } ?>
