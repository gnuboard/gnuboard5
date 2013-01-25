<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (!defined("_ORDERSMS_")) exit;

$receive_number = preg_replace("/[^0-9]/", "", $od_hp);	// 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호

if ($od_sms_ipgum_check)
{
	if ($od_bank_account && $od_receipt_bank && $od_deposit_name)
	{
		$sms_contents = $default[de_sms_cont3];
		$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
		$sms_contents = preg_replace("/{입금액}/", number_format($od_receipt_bank), $sms_contents);
		$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
		$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

		if ($default[de_sms_use] == "icode")
		{
			$SMS = new SMS;
			$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
			$SMS->Send();
		}
	}
}

if ($od_sms_baesong_check)
{
	if ($dl_id && $od_invoice)
	{
		$sms_contents = $default[de_sms_cont4];
		$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
		$sql = " select dl_company from $g4[yc4_delivery_table] where dl_id = '$dl_id' ";
		$row = sql_fetch($sql);
		$sms_contents = preg_replace("/{택배회사}/", $row[dl_company], $sms_contents);
		$sms_contents = preg_replace("/{운송장번호}/", $od_invoice, $sms_contents);
		$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
		$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

		if ($default[de_sms_use] == "icode")
		{
			$SMS = new SMS;
			$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
			$SMS->Send();
		}
	}
}
?>
