<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!extension_loaded('gd') || !function_exists('gd_info')) {
    echo '<script>'.PHP_EOL;
    echo 'alert("'.G5_VERSION.'의 정상적인 사용을 위해서는 GD 라이브러리가 필요합니다.\nGD 라이브러리가 없을 경우 자동등록방지 문자와 썸네일 기능이 작동하지 않습니다.");'.PHP_EOL;
    echo '</script>'.PHP_EOL;
}

if(!extension_loaded('openssl')) {
    echo '<script>'.PHP_EOL;
    echo 'alert("PHP openssl 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 openssl 확장 모듈을 설치하여 주십시오.");'.PHP_EOL;
    echo '</script>'.PHP_EOL;
}

if(!extension_loaded('soap') || !class_exists('SOAPClient')) {
    echo '<script>'.PHP_EOL;
    echo 'alert("PHP SOAP 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 SOAP 확장 모듈을 설치하여 주십시오.");'.PHP_EOL;
    echo '</script>'.PHP_EOL;
}
?>