<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 나이스페이 공통 설정
require_once G5_SUBSCRIPTION_PATH.'/settle_nicepay.inc.php';

// 빌키 발급(비인증) API 요청 URL
$postURL = "https://webapi.nicepay.co.kr/webapi/billing/billing_regist.jsp";

/*
****************************************************************************************
* <요청 값 정보>
* 아래 파라미터에 요청할 값을 알맞게 입력합니다. 
****************************************************************************************
*/
$mid 		= get_subs_option('su_nicepay_mid');		// 가맹점 아이디 
$moid 		= isset($_REQUEST['oid']) ? $_REQUEST['oid'] : '';				// 가맹점 주문번호
$buyerName 	= isset($_REQUEST['od_name']) ? clean_xss_tags($_REQUEST['od_name']) : '';				// 구매자명
$buyerTel 	= isset($_REQUEST['od_hp']) ? clean_xss_tags($_REQUEST['od_hp']) : '';				// 구매자 전화번호
$buyerEmail = isset($_REQUEST['od_email']) ? clean_xss_tags($_REQUEST['od_email']) : '';				// 구매자 이메일
$cardNo 	= isset($_REQUEST['cardNo']) ? clean_xss_tags($_REQUEST['cardNo']) : '';				// 카드번호
$expYear 	= isset($_REQUEST['expYear']) ? clean_xss_tags($_REQUEST['expYear']) : '';				// 유효기간(년) 
$expMonth 	= isset($_REQUEST['expMonth']) ? clean_xss_tags($_REQUEST['expMonth']) : '';				// 유효기간(월) 
$idNo		= isset($_REQUEST['idNo']) ? clean_xss_tags($_REQUEST['idNo']) : '';				// 주민번호 또는 사업자번호
$cardPw 	= isset($_REQUEST['cardPw']) ? clean_xss_tags($_REQUEST['cardPw']) : '';				// 카드 비밀번호 앞 2자리

// Key=Value 형태의 Plain-Text로 카드정보를 나열합니다.
// IDNo와 CardPw는 MID에 설정된 인증방식에 따라 필수 여부가 결정됩니다. 
$plainText = "CardNo=".$cardNo."&ExpYear=".$expYear."&ExpMonth=".$expMonth."&IDNo=".$idNo."&CardPw=".$cardPw;	// 카드정보 구성 (key=value&key=value&...)
		
// 결과 데이터를 저장할 변수를 미리 선언합니다. 
$response = "";	

/*
****************************************************************************************
* <위변조 검증값 및 카드 정보 암호화> (수정하지 마세요)
* SHA-256 해쉬 암호화는 거래 위변조를 막기위한 방법입니다. 
****************************************************************************************
*/	
$ediDate = date("YmdHis", G5_SERVER_TIME);																						// API 요청 전문 생성일시
$merchantKey = get_subs_option('su_nicepay_key');		// 가맹점 키
$encData = bin2hex(aesEncryptSSL($plainText, substr($merchantKey, 0, 16)));										// 카드정보 암호화
$signData = bin2hex(hash('sha256', $mid . $ediDate . $moid . $merchantKey, true));								// 위변조 데이터 검증 값 암호화

/*
****************************************************************************************
* <API 요청부>
* 명세서를 참고하여 필요에 따라 파라미터와 값을 'key'=>'value' 형태로 추가해주세요
****************************************************************************************
*/	

$data = Array(
	'MID' => $mid,
	'Moid' => $moid,
	'EdiDate' => $ediDate,
	'EncData' => $encData,
	'SignData' => $signData
);		

$response = reqPost($data, $postURL); 				//API 호출, 결과 데이터가 $response 변수에 저장됩니다.
//jsonRespDump($response); 							//결과 데이터를 브라우저에 노출합니다.

$resp_utf = iconv("EUC-KR", "UTF-8", $response);

$respArr = json_decode($resp_utf, true);

// https://developers.nicepay.co.kr/manual-card-billing.php
// 0000이 아니면 실패
if ($respArr['ResultCode'] !== 'F100') {
    alert($respArr['ResultMsg']);
}

$od_tno = $respArr['TID'];

$card_mask_number = mask_card_number($cardNo);
$card_billkey = $respArr['BID'];
$tno = $respArr['TID'];
$amount = isset($_POST['good_mny']) ? (int) $_POST['good_mny'] : 0;

if (!$amount && isset($_POST['od_price'])) {
    $amount = (int) $_POST['od_price'];
}

// 카드 코드
$card_code = $respArr['CardCode'];
// 카드이름
$card_name = preg_replace('/\[(.*?)\]/', '$1', $respArr['CardName']);

$app_no = '';
$app_time = '';
/*
(
    [ResultCode] => F100
    [ResultMsg] => 빌키가 정상적으로 생성되었습니다.
    [BID] => BIKYnictest*4m2501021117517***
    [AuthDate] => 20250102
    [CardCode] => 08
    [CardName] => [롯데]
    [TID] => nictest04m0*******021117517554
    [CardCl] => 0
    [AcquCardCode] => 08
    [AcquCardName] => [롯데]
)
*/

// 카드 정보를 암호화할 때 사용하는 AES 암호화 (opnessl) 함수입니다. 
function aesEncryptSSL($data, $key){
	$iv = openssl_random_pseudo_bytes(16);
	$encdata = @openssl_encrypt($data, "AES-128-ECB", $key, true, $iv);
	return $encdata;
}


// json으로 응답된 결과 데이터를 배열 형태로 변환하여 출력하는 함수입니다. 
// 응답 데이터 출력을 위한 예시로 테스트 이후 가맹점 상황에 맞게 변경합니다. 
function jsonRespDump($resp){
	$resp_utf = iconv("EUC-KR", "UTF-8", $resp); 
	$respArr = json_decode($resp_utf);
	foreach ( $respArr as $key => $value ){
		echo "$key=". iconv("UTF-8", "EUC-KR", $value)."<br />";
	}
}

// API를 POST 형태로 호출하는 함수입니다. 
function reqPost(Array $data, $url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);					//connection timeout 15 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));	//POST data
	curl_setopt($ch, CURLOPT_POST, true);
	$response = curl_exec($ch);
	curl_close($ch);	 
	return $response;
}

return;