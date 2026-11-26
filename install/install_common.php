<?php
// 설치 화면은 common.php를 거치지 않으므로 같은 최소 버전 검사를 여기서 한다.
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die(sprintf('PHP 7.0 or higher required. Your PHP version is %s', PHP_VERSION));
}
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    include_once('../cloudflare.check.php');    // cloudflare 의 ip 대역인지 체크
}