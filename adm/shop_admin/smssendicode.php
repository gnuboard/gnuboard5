<?
$sub_menu = "500200";
include_once("./_common.php");
include_once("$g4[path]/lib/icode.sms.lib.php");

auth_check($auth[$sub_menu], "w");

//print_r2($_POST);

// SMS 연결
$SMS	= new SMS;
$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);

$recv = explode(",", $receive_number);

$tran_callback = preg_replace("/[^0-9]/", "", $send_number);
$sms_id = $default[de_icode_id];
$tran_msg = $sms_contents;
$tran_date = "";
if ($reserved_flag) // 예약전송
{
	$tran_date = $reserved_year . 
		substr("0".$reserved_month, -2) . 
		substr("0".$reserved_day, -2).
		substr("0".$reserved_hour, -2).
		substr("0".$reserved_minute, -2);
}
for($i=0; $i<count($recv); $i++)
{
	$tran_phone = trim(preg_replace("/[^0-9]/", "", $recv[$i]));
	if (!$tran_phone) continue;

	$result = $SMS->Add($tran_phone, $tran_callback, $sms_id, stripslashes($tran_msg), $tran_date);
}
$result = $SMS->Send();
if ($result) 
{
	//echo "SMS 서버에 접속했습니다.<br>";
	$success = $fail = 0;
	foreach($SMS->Result as $result) 
	{
		list($phone,$code)=explode(":",$result);
		if ($code=="Error") 
		{
			//echo $phone.'로 발송하는데 에러가 발생했습니다.<br>';
			$msg .= $phone."로 발송하는데 에러가 발생했습니다.\\n";
			$fail++;
		} 
		else 
		{
			//echo $phone."로 전송했습니다. (메시지번호:".$code.")<br>";
			$success++;
		}
	}
	//echo $success."건을 전송했으며 ".$fail."건을 보내지 못했습니다.\\n";
	$SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
}
else
{
	//echo "에러: SMS 서버와 통신이 불안정합니다.<br>";
	$msg .= "에러: SMS 서버와 통신이 불안정합니다.\\n";
}

if (!$msg)
	$msg = "정상적으로 전송하였습니다.";

alert($msg, "./smssend.php");
?>
