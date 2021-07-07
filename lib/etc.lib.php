<?php
if (!defined('_GNUBOARD_')) exit;

// 로그를 파일에 쓴다
function write_log($file, $log) {
    $fp = fopen($file, "a+");
    ob_start();
    print_r($log);
    $msg = ob_get_contents();
    ob_end_clean();
    fwrite($fp, $msg);
    fclose($fp);
}