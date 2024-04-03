<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 나이스페이 공통 설정
require_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

if (function_exists('add_log')) add_log($_POST);

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
$moid = isset($_POST['Moid']) ? clean_xss_tags($_POST['Moid']) : '';							// order number
$amt = isset($_POST['Amt']) ? (int) preg_replace('/[^0-9]/', '', $_POST['Amt']) : 0;							// Amount of payment
$reqReserved = isset($_POST['ReqReserved']) ? clean_xss_tags($_POST['ReqReserved']) : '';			// mall custom field 
$netCancelURL = isset($_POST['NetCancelURL']) ? clean_xss_tags($_POST['NetCancelURL']) : '';			// netCancelURL
$Signature = isset($_POST['Signature']) ? clean_xss_tags($_POST['Signature']) : '';			// netCancelURL

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

// hex(sha256(AuthToken+MID+Amt+MerchantKey)), 위변조 검증 데이터
$signData = bin2hex(hash('sha256', $authToken. $default['de_nicepay_mid'] . $order_price . $default['de_nicepay_key'], true));

if ($Signature != $signData) {
    alert("유효성 검증이 틀려서 결제를 진행할수 없습니다.");
}

// API CALL foreach example
function jsonRespDump($resp){
	$respArr = json_decode($resp);
	foreach ( $respArr as $key => $value ){
		echo "$key=". $value."<br />";
	}
}

if (! function_exists('nicepay_res')) {
    function nicepay_res($key, $data, $default_val='') {
        $response_val = isset($data[$key]) ? $data[$key] : $default_val;

        return ($response_val ? $response_val : $default_val);
    }
}

/*
****************************************************************************************
* <authorization parameters init>
****************************************************************************************
*/
$response = "";

if($authResultCode === "0000"){
	/*
	****************************************************************************************
	* <Hash encryption> (do not modify)
	****************************************************************************************
	*/
	$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
	$merchantKey = $default['de_nicepay_key']; // 상점키
	$signData = bin2hex(hash('sha256', $authToken . $mid . $amt . $ediDate . $merchantKey, true));

	try {
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
        
        if (function_exists('add_log')) add_log($respArr);

        $ResultCode = nicepay_res('ResultCode', $respArr);
        $ResultMsg = nicepay_res('ResultMsg', $respArr);
        $tno             = nicepay_res('TID', $respArr);
        $amount          = (int) nicepay_res('Amt', $respArr, 0);
        $app_time        = nicepay_res('AuthDate', $respArr);
        $pay_method = nicepay_res('PayMethod', $respArr);
        $od_app_no = $app_no    = nicepay_res('AuthCode', $respArr); // 승인 번호  (신용카드, 계좌이체, 휴대폰)
        $pay_type   = $NICEPAY_METHOD[$pay_method];
        
        // 승인된 코드가 아니면 결제가 되지 않게 합니다.
        if (! in_array($ResultCode, array('3001', '4000', '4100', 'A000', '7001'))) {
            alert($ResultMsg.' 코드 : '.$ResultCode, G5_SHOP_URL);
            die();
        }

        if ($ResultCode == '3001') {    // 신용카드

            $card_cd   = nicepay_res('CardCode', $respArr); // 카드사 코드
            $card_name = nicepay_res('CardName', $respArr); // 카드 종류

        } else if ($ResultCode == '4100') {    // 가상계좌

            $bank_name = $bankname = nicepay_res('VbankBankName', $respArr);
            $account = nicepay_res('VbankNum', $respArr);
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
