<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// https://developers.nicepay.co.kr/manual-card-billing.php#parameter-cancel-request-v1

/*
****************************************************************************************
* <Cancel Request Parameter>
* The sample page only shows basic (required) parameters.
****************************************************************************************
*/
$merchantKey = get_subs_option('su_nicepay_key');
$mid = get_subs_option('su_nicepay_mid');
$moid = isset($od_id) ? $od_id : get_session('subs_order_id');		
$cancelMsg = $cancel_msg;
$tid = $tno;
$partialCancelCode = isset($partialCancelCode) ? (int) $partialCancelCode : 0;

/*
****************************************************************************************
* <Hash encryption> (do not modify)
* SHA-256 hash encryption is a way to prevent forgery.
****************************************************************************************
*/
$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$signData = bin2hex(hash('sha256', $mid . $cancelAmt . $ediDate . $merchantKey, true));

try{
	$data = Array(
		'TID' => $tid,
		'MID' => $mid,
		'Moid' => $moid,
		'CancelAmt' => $cancelAmt,
		'CancelMsg' => iconv("UTF-8", "EUC-KR", $cancelMsg),
		'PartialCancelCode' => $partialCancelCode,
		'EdiDate' => $ediDate,
		'SignData' => $signData,
		'CharSet' => 'utf-8'
	);

	/*
	****************************************************************************************
	* <Cancel Request>	
	****************************************************************************************
	*/	
	$response = nicepay_reqPost($data, "https://pg-api.nicepay.co.kr/webapi/cancel_process.jsp"); //Cancel API call

    $result = json_decode($response, true);
	
}catch(Exception $e){
	$e->getMessage();
	$ResultCode = "9999";
	$ResultMsg = "통신실패";
}