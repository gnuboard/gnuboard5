<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
include_once(G5_SHOP_PATH.'/kakaopay/lgcns_CNSpay.php');

// 로그 저장 위치 지정
$connector = new CnsPayWebConnector($LogDir);
$connector->CnsActionUrl($CnsPayDealRequestUrl);
$connector->CnsPayVersion($phpVersion);
$connector->setRequestData($_REQUEST);
$connector->addRequestData("actionType", "CL0");
$connector->addRequestData("CancelPwd", $cancelPwd);
$connector->addRequestData("CancelIP", $_SERVER['REMOTE_ADDR']);

//가맹점키 셋팅 (MID 별로 틀림)
$connector->addRequestData("EncodeKey", $merchantKey);

// 4. CNSPAY Lite 서버 접속하여 처리
$connector->requestAction();

// 5. 결과 처리
$resultCode = $connector->getResultData("ResultCode"); 	// 결과코드 (정상 :2001(취소성공), 2002(취소진행중), 그 외 에러)
$resultMsg = $connector->getResultData("ResultMsg");   	// 결과메시지
$cancelAmt = $connector->getResultData("CancelAmt");   	// 취소금액
$cancelDate = $connector->getResultData("CancelDate"); 	// 취소일
$cancelTime = $connector->getResultData("CancelTime");   	// 취소시간
$payMethod = $connector->getResultData("PayMethod");   // 취소 결제수단
$mid = 	$connector->getResultData("MID");             		// 가맹점 ID
$tid = $connector->getResultData("TID");               		// TID
$errorCD = $connector->getResultData("ErrorCD");        	// 상세 에러코드
$errorMsg = $connector->getResultData("ErrorMsg");      	// 상세 에러메시지
$authDate = $cancelDate . $cancelTime;						// 거래시간
$ccPartCl = $connector->getResultData("CcPartCl");         	// 부분취소 가능여부 (0:부분취소불가, 1:부분취소가능)
$stateCD = $connector->getResultData("StateCD");         	// 거래상태코드 (0: 승인, 1:전취소, 2:후취소)
$authDate = $connector->makeDateString($authDate);
$errorMsg = iconv("euc-kr", "utf-8", $errorMsg);
$resultMsg = iconv("euc-kr", "utf-8", $resultMsg);
?>