<?php
include_once('../common.php');

if (isset($_REQUEST['sort']) && !preg_match("/(--|#|\/\*|\*\/)/", $_REQUEST['sort']))  {
    $sort = trim($_REQUEST['sort']);
    $sort = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $sort);
} else {
    $sort = '';
}

if (isset($_REQUEST['sortodr']))  {
    $sortodr = preg_match("/^(asc|desc)$/i", $sortodr) ? $sortodr : '';
} else {
    $sortodr = '';
}

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

define('_SHOP_', true);
define('_SHOP_COMMON_', true); // 모바일 페이지의 직접 접근을 막는 경우에 사용
?>