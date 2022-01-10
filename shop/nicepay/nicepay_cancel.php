<?php
header("Content-Type:text/html; charset=euc-kr;");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
/*******************************************************************
 * DB연동 실패 시 강제취소                                      *
 *                                                                 *
 * 지불 결과를 DB 등에 저장하거나 기타 작업을 수행하다가 실패하는  *
 * 경우, 아래의 코드를 참조하여 이미 지불된 거래를 취소하는 코드를 *
 * 작성합니다.                                                     *
 ***********/

$cancelFlag = "true";

// $cancelFlag를 "true"로 변경하는 condition 판단은 개별적으로
// 수행하여 주십시오.

if($cancelFlag == "true") {

    include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

    $nicepay->m_ActionType      = "CLO";
    $nicepay->m_ssl             = 'true';
    $nicepay->m_CancelAmt       = $amount;
    $nicepay->m_TID             = $tno;
    $nicepay->m_Moid            = $od_id;
    $nicepay->m_CancelMsg       = "DB FAIL";

    if($default['de_nicepay_admin_key']) {
        $nicepay->m_CancelPwd       = $default['de_nicepay_admin_key'];
    }

    // 부분 취소관련 코드 (0:전체취소, 1: 부분 취소)
    $nicepay->m_PartialCancelCode = 0;
    $nicepay->startAction();

    var_dump($nicepay);
    exit;
}
