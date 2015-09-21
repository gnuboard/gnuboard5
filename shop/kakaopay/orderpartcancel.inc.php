<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'KAKAOPAY') return;

include_once(G5_SHOP_PATH.'/settle_kakaopay.inc.php');

include_once(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
include_once(G5_SHOP_PATH.'/kakaopay/lgcns_CNSpay.php');

$CancelNo                      = (int)$od['od_casseqno'] + 1;
$vat_mny                       = round((int)$tax_mny / 1.1);

$_REQUEST['TID']               = $od['od_tno'];
$_REQUEST['Amt']               = (int)$tax_mny + (int)$free_mny;
$_REQUEST['CancelMsg']         = $mod_memo;
$_REQUEST['PartialCancelCode'] = 1;
$_REQUEST['CheckRemainAmt']    = (int)$od['od_receipt_price'] - (int)$od['od_refund_price'];
$_REQUEST['CancelNo']          = $CancelNo;
$_REQUEST['SupplyAmt']         = ((int)$tax_mny + (int)$free_mny - $vat_mny);
$_REQUEST['GoodsVat']          = $vat_mny;
$_REQUEST['ServiceAmt']        = 0;


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

if($resultCode == "2001" || $resultCode == "2002") {
    $mod_mny = (int)$tax_mny + (int)$free_mny;

    $sql = " update {$g5['g5_shop_order_table']}
                set od_refund_price = od_refund_price + '$mod_mny',
                    od_shop_memo = concat(od_shop_memo, \"$mod_memo\"),
                    od_casseqno = '$CancelNo'
                where od_id = '{$od['od_id']}'
                  and od_tno = '{$od['od_tno']}' ";
    sql_query($sql);

    // 미수금 등의 정보 업데이트
    $info = get_order_info($od_id);

    $sql = " update {$g5['g5_shop_order_table']}
                set od_misu     = '{$info['od_misu']}',
                    od_tax_mny  = '{$info['od_tax_mny']}',
                    od_vat_mny  = '{$info['od_vat_mny']}',
                    od_free_mny = '{$info['od_free_mny']}'
                where od_id = '$od_id' ";
    sql_query($sql);
} else {
    alert($resultMsg . ' 코드 : ' . $resultCode);
}
?>