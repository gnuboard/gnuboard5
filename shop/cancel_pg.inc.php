<?php
/**
 * PG 결제 취소 공통 처리
 *
 * 이 파일을 include 하기 전에 $cancel_msg 변수를 설정해야 합니다.
 * 필요 변수: $tno, $od_pg, $pg_price, $amount, $cancel_msg
 */
if (!defined('G5_IS_SHOP')) exit;

switch($od_pg) {
    case 'lg':
        include G5_SHOP_PATH.'/lg/xpay_cancel.php';
        break;
    case 'toss':
        include G5_SHOP_PATH.'/toss/toss_cancel.php';
        break;
    case 'inicis':
        include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
        break;
    case 'nicepay':
        $cancelAmt = (int)$pg_price;
        include G5_SHOP_PATH.'/nicepay/cancel_process.php';
        break;
    case 'KAKAOPAY':
        $_REQUEST['TID']               = $tno;
        $_REQUEST['Amt']               = $amount;
        $_REQUEST['CancelMsg']         = $cancel_msg;
        $_REQUEST['PartialCancelCode'] = 0;
        include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
        break;
    default:
        include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
        break;
}
