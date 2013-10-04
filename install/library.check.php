<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function die_utf8($msg)
{
    if(!trim($msg))
        return;

    die('<meta charset="utf-8"><p>'.$msg.'</p>');
}

if(!extension_loaded('gd') || !function_exists('gd_info'))
    die('GD 라이브러리를 설치하신 후 '.G5_VERSION.' 설치를 진행해 주십시오.');
?>