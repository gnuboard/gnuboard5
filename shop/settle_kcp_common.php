<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/kcp_notification.lib.php');

$data = kcp_noti_request($_POST, $_SERVER, $default);
$result = $data !== false && kcp_noti_process($data) ? '0000' : '9999';
?>
<html><body><form><input type="hidden" name="result" value="<?php echo $result; ?>"></form></body></html>
