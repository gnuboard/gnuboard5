<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/lg_notification.lib.php');

$post = $_POST;
$post['LGD_TID'] = filter_input(INPUT_POST, 'LGD_TID');
$data = lg_noti_request($post, $_SERVER, $config);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo $data !== false && lg_noti_process($data) ? 'OK' : 'FAIL';
