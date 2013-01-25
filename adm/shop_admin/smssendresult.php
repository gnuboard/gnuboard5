<?
$sub_menu = "500200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

// 로그를 남김
$fp = fopen("$g4[path]/data/log/sms.log", "a+");
$msg  = "$now|$_SERVER[REMOTE_ADDR]|return_value=$return_value|success_value=$success_value|fail_value=$fail_value|";
$msg .= "error_code=$error_code|error_msg=$error_msg|unique_num=$unique_num|";
$msg .= "process_type=$process_type|usrdata1=$usrdata1|usrdata2=$usrdata2|usrdata3=$usrdata3\n";
fwrite($fp, $msg);
fclose($fp);

echo "<script language='JavaScript'>";
if ($return_value == 1) {
    echo "alert('정상적으로 전송하였습니다.');";
} else {
    echo "alert('오류발생 : $error_msg ($error_code)');";
}
echo "</script>";

goto_url("./smssend.php");
?>
