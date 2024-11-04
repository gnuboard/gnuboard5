<?php
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    include_once('../cloudflare.check.php');    // cloudflare 의 ip 대역인지 체크
}