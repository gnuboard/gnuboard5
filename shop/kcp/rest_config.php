<?php
if (!defined('_GNUBOARD_')) exit;

// 테스트/운영 인증서는 de_card_test와 일치하는 가맹점 발급 파일을 설치한다.
$g_conf_site_cd = kcp_rest_site_code();
if (!preg_match('/\A[A-Z0-9]{5}\z/D', $g_conf_site_cd)) alert('KCP REST SITE CODE 설정을 확인해 주십시오.');
$default['de_kcp_mid'] = $g_conf_site_cd;
$g_conf_site_key = '';
$g_conf_home_dir = G5_SHOP_PATH.'/kcp';
$g_conf_key_dir = $g_conf_log_dir = '';
$g_conf_gw_url = $default['de_card_test'] ? 'testpaygw.kcp.co.kr' : 'paygw.kcp.co.kr';
$g_conf_gw_port = '8090';
$g_conf_log_level = '0';
$g_conf_site_name = $default['de_admin_company_name'];
$module_type = '01';
$g_conf_js_url = $default['de_card_test'] ? 'https://testpay.kcp.co.kr/plugin/payplus_web.jsp' : 'https://pay.kcp.co.kr/plugin/payplus_web.jsp';
