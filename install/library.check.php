<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function die_utf8($msg)
{
    if(!trim($msg))
        return;

    die('<meta charset="utf-8"><p>'.$msg.'</p>');
}

if(!extension_loaded('gd') || !function_exists('gd_info'))
    die_utf8('GD 라이브러리를 설치하신 후 '.G5_VERSION.' 설치를 진행해 주십시오.');

if(!extension_loaded('openssl'))
    die_utf8('openssl 모듈을 설치하신 후 '.G5_VERSION.' 설치를 진행해 주십시오.');

// SOAP 모듈체크
if(!extension_loaded('soap') || !class_exists('SOAPClient'))
    die_utf8('SOAP 확장모듈을 설치하신 후 '.G5_VERSION.' 설치를 진행해 주십시오.');
?>