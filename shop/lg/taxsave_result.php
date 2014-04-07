<?php
include_once('./_common.php');

require_once(G5_SHOP_PATH.'/settle_lg.inc.php');

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
if (!$od)
    die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

$goods = get_goods($od['od_id']);
$goods_name = $goods['full_name'];
$order_price = $od['od_receipt_price'] - $od['od_refund_price'];

switch($od['od_settle_case']) {
    case '가상계좌':
        $pay_type = 'SC0040';
        break;
    case '계좌이체':
        $pay_type = 'SC0030';
        break;
    case '무통장':
        $pay_type = 'SC0100';
        break;
    default:
        die('<p id="scash_empty">현금영수증은 무통장, 가상계좌, 계좌이체에 한해 발급요청이 가능합니다.</p>');
        break
}

$LGD_METHOD                 = 'AUTH';                                   //메소드('AUTH':승인, 'CANCEL' 취소)
$LGD_OID                    = $od['od_id'];                             //주문번호(상점정의 유니크한 주문번호를 입력하세요)
$LGD_PAYTYPE                = $pay_type;                                //결제수단 코드 (SC0030:계좌이체, SC0040:가상계좌, SC0100:무통장입금 단독)
$LGD_AMOUNT                 = $order_price;                             //금액("," 를 제외한 금액을 입력하세요)
$LGD_PRODUCTINFO            = $goods_name;                              //상품명
$LGD_TID                    = $od['od_tno'];                            //LG유플러스 거래번호
$LGD_CUSTOM_MERTNAME        = $default['de_admin_company_name'];        //상점명
$LGD_CUSTOM_BUSINESSNUM     = $default['de_admin_company_saupja_no'];   //사업자등록번호
$LGD_CUSTOM_MERTPHONE       = $default['de_admin_company_tel'];         //상점 전화번호
$LGD_CASHCARDNUM            = $_POST['id_info'];                        //발급번호(주민등록번호,현금영수증카드번호,휴대폰번호 등등)
$LGD_CASHRECEIPTUSE         = $_POST['tr_code'];                        //현금영수증발급용도('1':소득공제, '2':지출증빙)

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $default['de_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $default['de_lg_mert_key']);

$xpay->Init_TX($LGD_MID);
$xpay->Set("LGD_TXNAME", "CashReceipt");
$xpay->Set("LGD_METHOD", $LGD_METHOD);
$xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

if ($LGD_METHOD == "AUTH") {                 // 현금영수증 발급 요청
    $xpay->Set("LGD_OID", $LGD_OID);
    $xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
    $xpay->Set("LGD_CASHCARDNUM", $LGD_CASHCARDNUM);
    $xpay->Set("LGD_CUSTOM_MERTNAME", $LGD_CUSTOM_MERTNAME);
    $xpay->Set("LGD_CUSTOM_BUSINESSNUM", $LGD_CUSTOM_BUSINESSNUM);
    $xpay->Set("LGD_CUSTOM_MERTPHONE", $LGD_CUSTOM_MERTPHONE);
    $xpay->Set("LGD_CASHRECEIPTUSE", $LGD_CASHRECEIPTUSE);

    if($od['od_tax_flag'] && $od['free_mny'] > ) {
        $xpay->Set("LGD_TAXFREEAMOUNT", $od['free_mny']); //비과세 금액
    }

    if ($LGD_PAYTYPE == "SC0030"){              //기결제된 계좌이체건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $LGD_TID);
    }
    else if ($LGD_PAYTYPE == "SC0040"){         //기결제된 가상계좌건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $LGD_TID);
        $xpay->Set("LGD_SEQNO", $od['od_casseqno']);
    }
    else {                                      //무통장입금 단독건 발급요청
        $xpay->Set("LGD_PRODUCTINFO", $LGD_PRODUCTINFO);
    }
}

/*
 * 1. 현금영수증 발급/취소 요청 결과처리
 *
 * 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
 */
if ($xpay->TX()) {
    //1)현금영수증 발급/취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
    echo "현금영수증 발급/취소 요청처리가 완료되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
    echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<br>";
    echo "거래번호 : " . $xpay->Response("LGD_TID",0) . "<p>";

    $keys = $xpay->Response_Names();
        foreach($keys as $name) {
            echo $name . " = " . $xpay->Response($name, 0) . "<br>";
        }

}else {
    //2)API 요청 실패 화면처리
    echo "현금영수증 발급/취소 요청처리가 실패되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
}
?>