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
        $pg_res_cd = 'SIRK_RETIRED';
        $pg_res_msg = 'SIRK 전용 카카오페이는 이니시스 상점관리자에서 취소해 주십시오.';
        break;
    default:
        include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
        break;
}
