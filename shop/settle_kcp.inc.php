<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$test = "";

if ($default['de_card_test']) {
    if ($default['de_escrow_use'] == 1) {
        // 에스크로결제 테스트
        $default['de_kcp_mid'] = "T0007";
        $default['de_kcp_site_key'] = '4Ho4YsuOZlLXUZUdOxM1Q7X__';
    }
    else {
        // 일반결제 테스트
        $default['de_kcp_mid'] = "T0000";
        $default['de_kcp_site_key'] = '3grptw1.zW0GSo4PQdaGvsF__';
    }

    $test = "_test";
}
else {
    $default['de_kcp_mid'] = "SR".$default['de_kcp_mid'];
}

$g_conf_site_key = $default['de_kcp_site_key'];

$g_conf_js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}_un.js";

$g_conf_log_level = "3";           // 변경불가
$g_conf_gw_port   = "8090";        // 포트번호(변경불가)
?>
