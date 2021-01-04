<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'lg') return;

include_once(G5_SHOP_PATH.'/settle_lg.inc.php');

/*
 * [결제 부분취소 요청 페이지]
 *
 * LG유플러스으로 부터 내려받은 거래번호(LGD_TID)를 가지고 취소 요청을 합니다.(파라미터 전달시 POST를 사용하세요)
 * (승인시 LG유플러스으로 부터 내려받은 PAYKEY와 혼동하지 마세요.)
 */

$LGD_TID              		= $od['od_tno'];			  		                            //LG유플러스으로 부터 내려받은 거래번호(LGD_TID)
$LGD_CANCELAMOUNT     		= (int)$tax_mny;                                                //부분취소 금액
$LGD_REMAINAMOUNT     		= (int)$od['od_receipt_price'] - (int)$od['od_refund_price'];   //취소전 남은금액

$LGD_CANCELTAXFREEAMOUNT    = (int)$free_mny;                                               //면세대상 부분취소 금액 (과세/면세 혼용상점만 적용)
$LGD_CANCELREASON     		= $mod_memo;                                                    //취소사유
$LGD_RFACCOUNTNUM           = $_POST['LGD_RFACCOUNTNUM'];	 		                        //환불계좌 번호(가상계좌 환불인경우만 필수)
$LGD_RFBANKCODE             = $_POST['LGD_RFBANKCODE'];	 		                            //환불계좌 은행코드(가상계좌 환불인경우만 필수)
$LGD_RFCUSTOMERNAME         = $_POST['LGD_RFCUSTOMERNAME']; 		                        //환불계좌 예금주(가상계좌 환불인경우만 필수)
$LGD_RFPHONE                = $_POST['LGD_RFPHONE'];		 		                        //요청자 연락처(가상계좌 환불인경우만 필수)

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

$xpay->Init_TX($LGD_MID);

$xpay->Set("LGD_TXNAME",                "PartialCancel");
$xpay->Set("LGD_TID",                   $LGD_TID);
$xpay->Set("LGD_CANCELAMOUNT",          $LGD_CANCELAMOUNT);
$xpay->Set("LGD_REMAINAMOUNT",          $LGD_REMAINAMOUNT);
$xpay->Set("LGD_CANCELTAXFREEAMOUNT",   $LGD_CANCELTAXFREEAMOUNT);
$xpay->Set("LGD_CANCELREASON",          $LGD_CANCELREASON);
$xpay->Set("LGD_RFACCOUNTNUM",          $LGD_RFACCOUNTNUM);
$xpay->Set("LGD_RFBANKCODE",            $LGD_RFBANKCODE);
$xpay->Set("LGD_RFCUSTOMERNAME",        $LGD_RFCUSTOMERNAME);
$xpay->Set("LGD_RFPHONE",               $LGD_RFPHONE);
$xpay->Set("LGD_REQREMAIN",             "0");
$xpay->Set("LGD_ENCODING",              "UTF-8");

/*
 * 1. 결제 부분취소 요청 결과처리
 *
 */
if ($xpay->TX()) {
    //1)결제 부분취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
    /*
    echo "결제 부분취소 요청이 완료되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    $keys = $xpay->Response_Names();
        foreach($keys as $name) {
            echo $name . " = " . $xpay->Response($name, 0) . "<br>";
        }
    echo "<p>";
    */

    if( '0000' == $xpay->Response_Code() ) {
        // 환불금액기록
        $tno = $xpay->Response("LGD_TID", 0);
        $mod_mny = (int)$tax_mny + (int)$free_mny;

        $sql = " update {$g5['g5_shop_order_table']}
                    set od_refund_price = od_refund_price + '$mod_mny',
                        od_shop_memo = concat(od_shop_memo, \"$mod_memo\")
                    where od_id = '{$od['od_id']}'
                      and od_tno = '$tno' ";
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
        alert($xpay->Response_Msg().' 코드 : '.$xpay->Response_Code());
    }
} else {
    //2)API 요청 실패 화면처리
    /*
    echo "결제 부분취소 요청이 실패하였습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
    */

    alert('결제 부분취소 요청이 실패하였습니다.\\n\\n'.$xpay->Response_Code().' : '.$xpay->Response_Msg());
}