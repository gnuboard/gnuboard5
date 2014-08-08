<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$g_conf_home_dir  = G5_SHOP_PATH.'/kcp';       // BIN 절대경로 입력 (bin전까지)
$g_conf_site_name = $default['de_admin_company_name'];
$g_conf_log_level = '3';           // 변경불가
$g_conf_gw_port   = '8090';        // 포트번호(변경불가)

$g_conf_key_dir   = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $g_conf_log_dir   = G5_SHOP_PATH.'/kcp/log';
    $g_conf_key_dir   = G5_SHOP_PATH.'/kcp/bin/pub.key';
}

if ($default['de_card_test']) {
    // 결제 테스트
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
    $g_wsdl = "KCPPaymentService.wsdl";
    $g_conf_gw_url = "testpaygw.kcp.co.kr";
}
else {
    $default['de_kcp_mid'] = "SR".$default['de_kcp_mid'];
    $g_wsdl = "real_KCPPaymentService.wsdl";
    $g_conf_gw_url = "paygw.kcp.co.kr";
}

$g_conf_site_cd = $default['de_kcp_mid'];
$g_conf_site_key = $default['de_kcp_site_key'];
?>
