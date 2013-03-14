<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = $default[de_sms_cont1];
$sms_contents = preg_replace("/{이름}/", $mb_name, $sms_contents);
$sms_contents = preg_replace("/{회원아이디}/", $mb_id, $sms_contents);
$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

// 핸드폰번호에서 숫자만 취한다
$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호

if ($w == "" && $default[de_sms_use1] && $receive_number) 
{ 
	if ($default[de_sms_use] == "xonda") 
	{
		$usrdata1 = "회원가입";

		define("_SMS_", TRUE);
		include "$g4[shop_path]/sms.inc.php";
	}
	else if ($default[de_sms_use] == "icode") 
	{
		include_once("$g4[path]/lib/icode.sms.lib.php");
		$SMS = new SMS;	// SMS 연결
		$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
		$SMS->Send();
	}
}
//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------
?>
