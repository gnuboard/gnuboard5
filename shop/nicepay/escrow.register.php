<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'nicepay') return;

include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$mid = $default['de_nicepay_mid'];
$tid = $escrow_tno;                             // 거래 번호
$reqType = '03';					                // 요청타입 (배송등록 03)
$deliveryCoNm = $escrow_corp;			        // 배송업체명
$buyerAddr = $od['od_b_addr1'].($od['od_b_addr2'] ? ' ' : '').$od['od_b_addr2'];				// 배송지 주소
$invoiceNum = $escrow_numb;				        // 송장번호
$registerName = $default['de_admin_company_name'];			    // 등록자이름 (영카트 회사명으로 지정)
$confirmMail = 1;			                    // 구매결정 메일발송 여부 (1은 발송, 2는 미발송)
$charSet = 'utf-8';					            // 응답파라미터 인코딩 방식
$escrowRequestURL = "https://webapi.nicepay.co.kr/webapi/escrow_process.jsp"; 	//에스크로 요청 URL

/*
*******************************************************
* <해쉬암호화> (수정하지 마세요)
* SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
*******************************************************
*/ 
$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
$signData = bin2hex(hash('sha256', $tid.$mid.$reqType.$ediDate.$default['de_nicepay_key'], true));

$response = "";

$data = array(
    'MID' => $mid,
    'TID' => $tid,
    'EdiDate' => $ediDate,
    'SignData' => $signData,
    'ReqType' => $reqType,
    'DeliveryCoNm' => $deliveryCoNm,
    'BuyerAddr' => $buyerAddr,
    'InvoiceNum' => $invoiceNum,
    'RegisterName' => $registerName,
    'ConfirmMail' => $confirmMail,
    'CharSet' => $charSet
);

$response = nicepay_reqPost($data, $escrowRequestURL);

$nice_result = json_decode($response, true);

if (function_exists('add_log')) add_log($nice_result, true, 'es');

// 성공이면
if (isset($nice_result['ResultCode']) && $nice_result['ResultCode'] === 'C000') {

} else {
    // C000 이 아니면 다 실패

    /*
    C002    에스크로 가맹점 아님
    C003    에스크로 거래만 배송등록 가능
    C004    에스크로결제 신청내역 미존재
    C005    에스크로배송등록 불가상태
    C006    거래내역이 존재하지 않음.
    C007    취소된 거래는 배송등록 불가
    */

}

/**********************
 * 4. 배송 등록  결과 *
 **********************/

$resultCode = $nice_result['ResultCode'];        // 결과코드 ("00"이면 지불 성공)
$resultMsg  = $nice_result['ResultMsg'];          // 결과내용 (지불결과에 대한 설명)
$dlv_date   = $nice_result['ProcessDate'];
$dlv_time   = $nice_result['ProcessTime'];

echo "에스크로배송";
exit;