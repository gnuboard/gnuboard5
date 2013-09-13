<?php
// 타임존
$GLOBALS['syndi_time_zone'] = '+09:00';

// 데이타 인코딩
$GLOBALS['syndi_from_encoding'] = 'utf-8';

// 도메인 (http:// 제외, 마지막 / 제외)
$GLOBALS['syndi_tag_domain'] = $_SERVER['HTTP_HOST']; // 도메인을 직접 입력하시는게 좋습니다. (예: 'gnuboard.com')

// 도메인 연결 날짜(년도)
$GLOBALS['syndi_tag_year'] = '2010';

// 홈페이지 제목
$GLOBALS['syndi_homepage_title'] = $config['cf_title'];

// Syndication 출력 url (syndi_echo.php의 웹경로)
$GLOBALS['syndi_echo_url'] = G5_SYNDI_URL.'/syndi_echo.php';
?>
