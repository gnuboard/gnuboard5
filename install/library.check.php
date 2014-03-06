<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!extension_loaded('gd') || !function_exists('gd_info')) {
    echo '<script>'.PHP_EOL;
    echo 'alert("'.G5_VERSION.'의 정상적인 사용을 위해서는 GD 라이브러리가 필요합니다.\nGD 라이브러리가 없을 경우 자동등록방지 문자와 썸네일 기능이 작동하지 않습니다.");'.PHP_EOL;
    echo '</script>'.PHP_EOL;
}
?>