<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

$resultMap = get_session('resultMap');

if( strcmp('0000', $resultMap['resultCode']) == 0 ) {
    //최종결제요청 결과 성공 DB처리
    $tno        = $resultMap['tid'];
    $amount     = $resultMap['TotPrice'];
    $app_time   = $resultMap['applDate'].$resultMap['applTime'];
    $pay_method = $resultMap['payMethod'];
    $pay_type   = $PAY_METHOD[$pay_method];
    $depositor  = $resultMap['VACT_InputName'];
    $commid     = '';
    $mobile_no  = $resultMap['HPP_Num'];
    $app_no     = $resultMap['applNum'];
    $card_name  = $CARD_CODE[$resultMap['CARD_Code']];
    switch($pay_type) {
        case '계좌이체':
            $bank_name = $BANK_CODE[$resultMap['ACCT_BankCode']];
            if ($default['de_escrow_use'] == 1)
                $escw_yn         = 'Y';
            break;
        case '가상계좌':
            $bankname  = $BANK_CODE[$resultMap['VACT_BankCode']];
            $account   = $resultMap['VACT_Num'].' '.$resultMap['VACT_Name'];
            $app_no    = $resultMap['VACT_Num'];
            if ($default['de_escrow_use'] == 1)
                $escw_yn         = 'Y';
            break;
        default:
            break;
    }
} else {
    die($resultMap['resultMsg'].' 코드 : '.$resultMap['resultCode']);
}

?>