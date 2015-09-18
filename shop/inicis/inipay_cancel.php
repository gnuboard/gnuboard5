<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*******************************************************************
 * 7. DB연동 실패 시 강제취소                                      *
 *                                                                 *
 * 지불 결과를 DB 등에 저장하거나 기타 작업을 수행하다가 실패하는  *
 * 경우, 아래의 코드를 참조하여 이미 지불된 거래를 취소하는 코드를 *
 * 작성합니다.                                                     *
 *******************************************************************/

$cancelFlag = "true";

// $cancelFlag를 "ture"로 변경하는 condition 판단은 개별적으로
// 수행하여 주십시오.

if($cancelFlag == "true")
{
    include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

    $TID = $tno;
    $inipay->SetField("type", "cancel"); // 고정
    $inipay->SetField("tid", $TID); // 고정
    $inipay->SetField("cancelmsg", "DB FAIL"); // 취소사유
    $inipay->startAction();
    if($inipay->GetResult('ResultCode') == "00")
    {
        $inipay->MakeTXErrMsg(MERCHANT_DB_ERR,"Merchant DB FAIL");
    }
}
?>