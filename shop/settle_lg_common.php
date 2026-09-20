<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/lg_notification.lib.php');

$data = lg_noti_request($_POST, $_SERVER, $config);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo $data !== false && lg_noti_process($data) ? 'OK' : 'FAIL';
