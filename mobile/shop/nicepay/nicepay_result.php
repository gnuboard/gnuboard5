<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 나이스페이 공통 설정
require_once(G5_MSHOP_PATH.'/settle_nicepay.inc.php');

/*
****************************************************************************************
* <Authentication Result Parameter>
****************************************************************************************
*/
$authResultCode = isset($_POST['AuthResultCode']) ? clean_xss_tags($_POST['AuthResultCode']) : '';		// authentication result code 0000:success
$authResultMsg = isset($_POST['AuthResultMsg']) ? clean_xss_tags($_POST['AuthResultMsg']) : '';		// authentication result message
$nextAppURL = isset($_POST['NextAppURL']) ? clean_xss_tags($_POST['NextAppURL']) : '';				// authorization request URL
$txTid = isset($_POST['TxTid']) ? clean_xss_tags($_POST['TxTid']) : '';						// transaction ID
$authToken = isset($_POST['AuthToken']) ? clean_xss_tags($_POST['AuthToken']) : '';				// authentication TOKEN
$payMethod = isset($_POST['PayMethod']) ? clean_xss_tags($_POST['PayMethod']) : '';				// payment method
$mid = isset($_POST['MID']) ? clean_xss_tags($_POST['MID']) : '';							// merchant id
$moid = isset($_POST['Moid']) ? addslashes(clean_xss_tags(stripslashes($_POST['Moid']))) : '';							// order number
$amt = isset($_POST['Amt']) ? (int) preg_replace('/[^0-9]/', '', $_POST['Amt']) : 0;							// Amount of payment
$reqReserved = isset($_POST['ReqReserved']) ? clean_xss_tags($_POST['ReqReserved']) : '';			// mall custom field
$netCancelURL = isset($_POST['NetCancelURL']) ? clean_xss_tags($_POST['NetCancelURL']) : '';			// netCancelURL
$Signature = isset($_POST['Signature']) ? clean_xss_tags($_POST['Signature']) : '';			// authentication signature

if($authResultCode !== "0000"){
    //When authentication fail
    $ResultCode = $authResultCode;
    $ResultMsg = $authResultMsg;

    alert($ResultMsg.' 실패 코드 : '.$ResultCode);
}

if (isset($pp['pp_id']) && $pp['pp_id']) {   //개인결제
    $session_order_id = get_session('ss_personalpay_id');
    $order_price = (int) $pp['pp_price'];
} else {
    $session_order_id = get_session('ss_order_id');     // 쇼핑몰 일반결제
}

if ($session_order_id != $moid){
    alert("요청한 주문번호가 틀려서 결제를 진행할수 없습니다.\\n다시 장바구니에서 시도해 주세요.", G5_SHOP_URL);
}

if ($default['de_nicepay_mid'] != $mid) {
    alert("요청한 상점 mid와 설정된 mid가 틀리므로 결제를 진행할수 없습니다.", G5_SHOP_URL);
}

if ($order_price != $amt) {
    alert("요청한 결제금액이 틀리므로 결제를 진행할수 없습니다.", G5_SHOP_URL);
}

// API CALL foreach example
if (! function_exists('jsonRespDump')) {
    function jsonRespDump($resp){
        $respArr = json_decode($resp);
        foreach ( $respArr as $key => $value ){
            echo "$key=". $value."<br />";
        }
    }
}

if (! function_exists('nicepay_res')) {
    function nicepay_res($key, $data, $default_val='') {
        $response_val = isset($data[$key]) ? $data[$key] : $default_val;

        return ($response_val ? $response_val : $default_val);
    }
}

if (! function_exists('nicepay_expected_pay_result')) {
    function nicepay_expected_pay_result($settle_case) {
        $pay_results = array(
            '신용카드' => array('PayMethod'=>'CARD', 'ResultCode'=>'3001'),
            '간편결제' => array('PayMethod'=>'CARD', 'ResultCode'=>'3001'),
            '계좌이체' => array('PayMethod'=>'BANK', 'ResultCode'=>'4000'),
            '가상계좌' => array('PayMethod'=>'VBANK', 'ResultCode'=>'4100'),
            '휴대폰' => array('PayMethod'=>'CELLPHONE', 'ResultCode'=>'A000')
        );

        return isset($pay_results[$settle_case]) ? $pay_results[$settle_case] : false;
    }
}

/*
****************************************************************************************
* <authorization parameters init>
****************************************************************************************
*/
$response = "";

if($authResultCode === "0000"){
    // hex(sha256(AuthToken+MID+Amt+MerchantKey)), 위변조 검증 데이터
    $authSignData = bin2hex(hash('sha256', $authToken. $default['de_nicepay_mid'] . $order_price . $default['de_nicepay_key'], true));

    if ($Signature != $authSignData) {
        alert("유효성 검증이 틀려서 결제를 진행할수 없습니다.");
    }

    if (! nicepay_is_allowed_api_url($nextAppURL, array('/webapi/pay_process.jsp'))) {
        alert("승인 요청 url이 올바르지 않습니다.", G5_SHOP_URL);
    }

    if (! nicepay_is_allowed_api_url($netCancelURL, array('/webapi/cancel_process.jsp'))) {
        alert("망취소 요청 url이 올바르지 않습니다.", G5_SHOP_URL);
    }

	/*
	****************************************************************************************
	* <Hash encryption> (do not modify)
	****************************************************************************************
	*/
	$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
	$merchantKey = $default['de_nicepay_key']; // 상점키
	$signData = bin2hex(hash('sha256', $authToken . $mid . $amt . $ediDate . $merchantKey, true));

	try{
		$data = Array(
			'TID' => $txTid,
			'AuthToken' => $authToken,
			'MID' => $mid,
			'Amt' => $amt,
			'EdiDate' => $ediDate,
			'SignData' => $signData,
			'CharSet' => 'utf-8'
		);		
		/*
		****************************************************************************************
		* <authorization request>
		* authorization through server to server communication.
		****************************************************************************************

        // 3001 : 신용카드 성공코드
        // 4000 : 계좌이체 성공코드
        // 4100 : 가상계좌 발급 성공코드
        // A000 : 휴대폰 소액결제 성공코드
        // 7001 : 현금영수증
        // https://developers.nicepay.co.kr/manual-auth.php
			*/
			$response = nicepay_reqPost($data, $nextAppURL);

	        if (! $response) {
	            alert('응답이 없거나 잘못된 url 입니다.', G5_SHOP_URL);
	        }

	        $respArr = json_decode($response, true);

	        $ResultCode = nicepay_res('ResultCode', $respArr);
	        $ResultMsg = nicepay_res('ResultMsg', $respArr);
	        $tno             = nicepay_res('TID', $respArr);
	        $response_amt    = nicepay_res('Amt', $respArr, 0);
	        $response_mid    = nicepay_res('MID', $respArr);
	        $response_moid   = nicepay_res('Moid', $respArr);
	        $amount          = (int) $response_amt;
	        $app_time        = nicepay_res('AuthDate', $respArr);
	        $pay_method = nicepay_res('PayMethod', $respArr);
	        $od_app_no = $app_no    = nicepay_res('AuthCode', $respArr); // 승인 번호  (신용카드, 계좌이체, 휴대폰)
	        $pay_type   = isset($NICEPAY_METHOD[$pay_method]) ? $NICEPAY_METHOD[$pay_method] : '';

	        // 승인된 코드가 아니면 결제가 되지 않게 합니다.
	        if (! in_array($ResultCode, array('3001', '4000', '4100', 'A000', '7001'))) {
	            alert($ResultMsg.' 코드 : '.$ResultCode, G5_SHOP_URL);
	            die();
	        }

	        $responseSignature = nicepay_res('Signature', $respArr);
	        $responseSignData = bin2hex(hash('sha256', $tno . $mid . $response_amt . $merchantKey, true));

	        if ($responseSignature != $responseSignData) {
	            if($amount > 0 && $tno) {
	                $od_id = $moid;
	                $cancel_msg = '승인 응답 유효성 검증 실패';
	                $cancelAmt = $amount;
	                $partialCancelCode = 0;
	                include G5_SHOP_PATH.'/nicepay/cancel_process.php';
	            }

	            alert("승인 응답 유효성 검증이 틀려서 결제를 진행할수 없습니다.", G5_SHOP_URL);
	        }

	        if ($response_mid != $mid || $response_moid != $moid) {
	            if($amount > 0) {
	                $od_id = $moid;
	                $cancel_msg = '승인 응답 주문정보 불일치';
	                $cancelAmt = $amount;
	                $partialCancelCode = 0;
	                include G5_SHOP_PATH.'/nicepay/cancel_process.php';
	            }

	            alert("승인 응답 주문정보가 틀려서 결제를 진행할수 없습니다.", G5_SHOP_URL);
	        }

	        if ($amount != $order_price) {
	            if($amount > 0) {
	                $od_id = $moid;
	                $cancel_msg = '결제금액 불일치';
	                $cancelAmt = $amount;
	                $partialCancelCode = 0;
	                include G5_SHOP_PATH.'/nicepay/cancel_process.php';
	            }

	            alert("승인된 결제금액이 틀리므로 결제를 진행할수 없습니다.", G5_SHOP_URL);
	        }

	        $nicepay_settle_case = '';
	        if (isset($pp['pp_id']) && $pp['pp_id'] && isset($pp_settle_case)) {
	            $nicepay_settle_case = $pp_settle_case;
	        } else if (isset($od_settle_case)) {
	            $nicepay_settle_case = $od_settle_case;
	        }

	        $expected_pay_result = nicepay_expected_pay_result($nicepay_settle_case);
	        if ($expected_pay_result && ($pay_method !== $expected_pay_result['PayMethod'] || $ResultCode !== $expected_pay_result['ResultCode'])) {
	            if($amount > 0 && $tno) {
	                $od_id = $moid;
	                $cancel_msg = '결제수단 불일치';
	                $cancelAmt = $amount;
	                $partialCancelCode = 0;
	                include G5_SHOP_PATH.'/nicepay/cancel_process.php';
	            }

	            alert("선택한 결제수단과 승인된 결제수단이 일치하지 않아 결제를 진행할수 없습니다.", G5_SHOP_URL);
	        }

	        if ($ResultCode == '3001') {    // 신용카드

	            $card_cd   = nicepay_res('CardCode', $respArr); // 카드사 코드
            $card_name = nicepay_res('CardName', $respArr); // 카드 종류

        } else if ($ResultCode == '4100') {    // 가상계좌

            $bank_name = $bankname = nicepay_res('VbankBankName', $respArr);
            $account   = nicepay_res('VbankNum', $respArr);
            $va_date   = nicepay_res('VbankExpDate', $respArr).' '.nicepay_res('VbankExpTime', $respArr); // 가상계좌 입금마감시간
            $app_no    = nicepay_res('VbankNum', $respArr);

            if ($default['de_escrow_use'] == 1)
                $escw_yn         = 'Y';

        } else if ($ResultCode == '4000') {     // 계좌이체
            $bank_name = $bankname = nicepay_res('BankName', $respArr);
            $bank_code = nicepay_res('BankCode', $respArr);

            $RcptType = nicepay_res('RcptType', $respArr); // 현금영수증타입 (0:발행안함,1:소득공제,2:지출증빙)
            $RcptTID = nicepay_res('RcptTID', $respArr); // 현금영수증 TID, 현금영수증 거래인 경우 리턴
            $RcptAuthCode = nicepay_res('RcptAuthCode', $respArr); // 현금영수증 승인번호, 현금영수증 거래인 경우 리턴
            $AuthDate = nicepay_res('AuthDate', $respArr); // 현금영수증 승인번호, 현금영수증 거래인 경우 리턴

            // 현금영수증 발급시 1 또는 2 이면
            if ($RcptType) {
                $pg_receipt_infos['od_cash'] = 1;   // 현금영수증 발급인것으로 처리
                $pg_receipt_infos['od_cash_no'] = $RcptAuthCode;    // 현금영수증 승인번호
                $pg_receipt_infos['od_cash_info'] = serialize(array('TID'=>$RcptTID, 'ApplNum'=>$RcptAuthCode, 'AuthDate'=>$AuthDate));
            }

            if ($default['de_escrow_use'] == 1)
                $escw_yn         = 'Y';

        }
        $depositor       = '';  // 입금할 계좌 예금주 (나이스페이 경우 가상계좌의 예금주명을 리턴받지 못합니다. )
        $account         = nicepay_res('VbankNum', $respArr);
        $commid          = '';    // 통신사 코드
        $mobile_no       = '';    // 휴대폰결제시 휴대폰번호 (나이스페이 경우 결제한 휴대폰번호를 리턴받지 못합니다.)
        $card_name       = nicepay_res('CardName', $respArr);

	} catch(Exception $e) {
		$e->getMessage();
		$data = Array(
			'TID' => $txTid,
			'AuthToken' => $authToken,
			'MID' => $mid,
			'Amt' => $amt,
			'EdiDate' => $ediDate,
			'SignData' => $signData,
			'NetCancel' => '1',
			'CharSet' => 'utf-8'
		);
		/*
		*************************************************************************************
		* <NET CANCEL>
		* If an exception occurs during communication, cancelation is recommended
		*************************************************************************************
		*/			
		$response = nicepay_reqPost($data, $netCancelURL);
		// jsonRespDump($response);

        alert("결제 오류로 더 이상 진행할수 없습니다.");
	}	
	
} else {
	//When authentication fail
	$ResultCode = $authResultCode; 	
	$ResultMsg = $authResultMsg;

    alert($ResultMsg.' 실패 코드 : '.$ResultCode);
}
