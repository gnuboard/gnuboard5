<?php
// 웹 요청으로는 실행하지 않고 서버 작업 스케줄러의 PHP CLI에서만 실행한다.
if (PHP_SAPI !== 'cli') {
    if (!headers_sent())
        header('HTTP/1.1 404 Not Found');
    exit;
}

if (!defined('G5_SET_TIME_LIMIT'))
    define('G5_SET_TIME_LIMIT', 120);

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
$monitor_host = 'localhost';
$monitor_https = false;
$monitor_client_ip = '';
$monitor_no_external = false;
if (isset($argv) && is_array($argv)) {
    foreach ($argv as $argument) {
        if (preg_match('/^--host=([A-Za-z0-9.-]+)(:[0-9]+)?$/', $argument, $matches))
            $monitor_host = $matches[1].(isset($matches[2]) ? $matches[2] : '');
        elseif ($argument === '--https')
            $monitor_https = true;
        elseif (preg_match('/^--client-ip=([0-9.]+)$/', $argument, $matches) && ip2long($matches[1]) !== false)
            $monitor_client_ip = $matches[1];
        elseif ($argument === '--no-external')
            $monitor_no_external = true;
    }
}
$_SERVER['HTTP_HOST'] = $monitor_host;
$_SERVER['SERVER_NAME'] = $monitor_host;
$_SERVER['SERVER_PORT'] = $monitor_https ? '443' : '80';
if ($monitor_https)
    $_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$_SERVER['SCRIPT_NAME'] = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/shop/inicis/pro/monitor.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_ADDR'] = $monitor_client_ip !== '' ? $monitor_client_ip : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1');

chdir($root_path);
include_once($root_path.'/common.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

if ($monitor_no_external) {
    $default['de_inicis_pro_reconcile_use'] = 0;
    $default['de_inicis_pro_alert_use'] = 0;
}

if (!inicis_pro_is_enabled()) {
    echo "INIpay PRO is disabled.\n";
    exit;
}

$monitor_ran = check_order_inicis_pro_payments();
if (!$monitor_ran) {
    echo "INIpay PRO monitor is already running.\n";
    exit;
}
$monitor = sql_fetch(" select de_inicis_pro_monitor_at, de_inicis_pro_monitor_message from {$g5['g5_shop_default_table']} limit 1 ", false);
echo (!empty($monitor['de_inicis_pro_monitor_at']) ? $monitor['de_inicis_pro_monitor_at'].' ' : '').(!empty($monitor['de_inicis_pro_monitor_message']) ? $monitor['de_inicis_pro_monitor_message'] : 'monitor completed')."\n";
