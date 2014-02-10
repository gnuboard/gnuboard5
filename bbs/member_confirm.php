<?php
include_once('./_common.php');

if ($is_guest)
    alert('로그인 한 회원만 접근하실 수 있습니다.', G5_BBS_URL.'/login.php');

/*
if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER[REQUEST_URI]);
*/

$g5['title'] = '회원 비밀번호 확인';
include_once('./_head.sub.php');

$url = $_GET['url'];

$p = parse_url($url);
if ((isset($p['scheme']) && $p['scheme']) || (isset($p['host']) && $p['host'])) {
    //print_r2($p);
    if ($p['host'].(isset($p['port']) ? ':'.$p['port'] : '') != $_SERVER['HTTP_HOST'])
        alert('url에 타 도메인을 지정할 수 없습니다.');
}

include_once($member_skin_path.'/member_confirm.skin.php');

include_once('./_tail.sub.php');
?>
