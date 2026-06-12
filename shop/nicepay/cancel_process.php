<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
****************************************************************************************
* <Cancel Request Parameter>
* The sample page only shows basic (required) parameters.
****************************************************************************************
*/
$merchantKey = $default['de_nicepay_key'];
$mid = $default['de_nicepay_mid'];
$moid = isset($od_id) ? $od_id : get_session('ss_order_id');		
$cancelMsg = $cancel_msg;
$tid = $tno;
$partialCancelCode = isset($partialCancelCode) ? (int) $partialCancelCode : 0;
$refundAcctNo = isset($nicepay_refund_acct_no) ? preg_replace('/[^0-9]/', '', $nicepay_refund_acct_no) : '';
$refundBankCd = isset($nicepay_refund_bank_cd) ? preg_replace('/[^0-9]/', '', $nicepay_refund_bank_cd) : '';
$refundAcctNm = isset($nicepay_refund_acct_nm) ? trim($nicepay_refund_acct_nm) : '';

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
		'CharSet' => 'utf-8',
		'EdiType' => 'JSON'
	);

	if ($refundAcctNo !== '' || $refundBankCd !== '' || $refundAcctNm !== '') {
		$data['RefundAcctNo'] = $refundAcctNo;
		$data['RefundBankCd'] = $refundBankCd;
		$data['RefundAcctNm'] = function_exists('iconv') ? iconv("UTF-8", "EUC-KR//IGNORE", $refundAcctNm) : $refundAcctNm;
	}

	/*
	****************************************************************************************
	* <Cancel Request>
	****************************************************************************************
	*/
	$isNicepayVbankCancel = false;
	if (isset($od['od_settle_case']) && $od['od_settle_case'] === '가상계좌') {
		$isNicepayVbankCancel = true;
	} else if (isset($od_settle_case) && $od_settle_case === '가상계좌') {
		$isNicepayVbankCancel = true;
	} else if (isset($pp_settle_case) && $pp_settle_case === '가상계좌') {
		$isNicepayVbankCancel = true;
	} else if (isset($nicepay_settle_case) && $nicepay_settle_case === '가상계좌') {
		$isNicepayVbankCancel = true;
	} else if (isset($pay_method) && $pay_method === 'VBANK') {
		$isNicepayVbankCancel = true;
	}

	$cancelUrl = $isNicepayVbankCancel
		? "https://webapi.nicepay.co.kr/webapi/cancel_process.jsp"
		: "https://pg-api.nicepay.co.kr/webapi/cancel_process.jsp";
	$response = nicepay_reqPost($data, $cancelUrl); //Cancel API call

	$result = json_decode($response, true);

	if (isset($result['ResultCode'])) {
		$responseTid = isset($result['TID']) ? $result['TID'] : $tid;
		$responseCancelAmt = isset($result['CancelAmt']) ? $result['CancelAmt'] : $cancelAmt;
		$responseSignature = isset($result['Signature']) ? $result['Signature'] : '';
		$responseSignData = bin2hex(hash('sha256', $responseTid . $mid . $responseCancelAmt . $merchantKey, true));

		if ($responseSignature !== '' && $responseSignature != $responseSignData) {
			$result['ResultCode'] = '9998';
			$result['ResultMsg'] = '취소 응답 유효성 검증 실패';
		}
	}

}catch(Exception $e){
	$e->getMessage();
	$result = array(
		'ResultCode' => '9999',
		'ResultMsg'  => '통신실패'
	);
}
