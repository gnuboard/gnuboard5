<?php
include_once('./_common.php');

// 로그를 남김
$fp = fopen(G5_DATA_PATH.'/log/sms.log', "a+");
$msg  = G5_TIME_YMDHIS."|{$_SERVER['REMOTE_ADDR']}|return_value=$return_value|success_value=$success_value|fail_value=$fail_value|";
$msg .= "error_code=$error_code|error_msg=$error_msg|unique_num=$unique_num|";
$msg .= "process_type=$process_type|usrdata1=$usrdata1|usrdata2=$usrdata2|usrdata3=$usrdata3\n";
fwrite($fp, $msg);
fclose($fp);
?>
<script language="JavaScript">window.close();</script>