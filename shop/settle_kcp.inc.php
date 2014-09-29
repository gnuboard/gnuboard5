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

$g_conf_home_dir  = G5_SHOP_PATH.'/kcp';
$g_conf_key_dir   = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $g_conf_log_dir   = G5_SHOP_PATH.'/kcp/log';
    $g_conf_key_dir   = G5_SHOP_PATH.'/kcp/bin/pub.key';
}

$g_conf_site_cd  = $default['de_kcp_mid'];
$g_conf_site_key = $default['de_kcp_site_key'];

if (preg_match("/^T000/", $g_conf_site_cd) || $default['de_card_test']) {
    $g_conf_gw_url  = "testpaygw.kcp.co.kr";                    // real url : paygw.kcp.co.kr , test url : testpaygw.kcp.co.kr
}
else {
    $g_conf_gw_url  = "paygw.kcp.co.kr";
    if (!preg_match("/^SR/", $g_conf_site_cd)) {
        alert("SR 로 시작하지 않는 KCP SITE CODE 는 지원하지 않습니다.");
    }
}

// KCP SITE KEY 입력 체크
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
    if(trim($default['de_kcp_site_key']) == '')
        alert('KCP SITE KEY를 입력해 주십시오.');
}

$g_conf_js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}_un.js";

$g_conf_log_level = "3";           // 변경불가
$g_conf_gw_port   = "8090";        // 포트번호(변경불가)
?>
