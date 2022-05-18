<?php
include_once('./_common.php');

require_once(G5_SHOP_PATH.'/settle_lg.inc.php');

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
$tx = isset($_REQUEST['tx']) ? clean_xss_tags($_REQUEST['tx'], 1, 1) : '';

if($tx == 'personalpay') {
    $od = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">개인결제 내역이 존재하지 않습니다.</p>');

    $od_tno      = $od['pp_tno'];
    $goods_name  = $od['pp_name'].'님 개인결제';
    $settle_case = $od['pp_settle_case'];
    $order_price = $od['pp_receipt_price'];
    $od_casseqno = $od['pp_casseqno'];
} else {
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

    $od_tno      = $od['od_tno'];
    $goods       = get_goods($od['od_id']);
    $goods_name  = $goods['full_name'];
    $settle_case = $od['od_settle_case'];
    $order_price = $od['od_tax_mny'] + $od['od_vat_mny'] + $od['od_free_mny'];
    $od_casseqno = $od['od_casseqno'];
}

switch($settle_case) {
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
        break;
}

$LGD_METHOD                 = 'AUTH';                                   //메소드('AUTH':승인, 'CANCEL' 취소)
$LGD_OID                    = $od_id;                                   //주문번호(상점정의 유니크한 주문번호를 입력하세요)
$LGD_PAYTYPE                = $pay_type;                                //결제수단 코드 (SC0030:계좌이체, SC0040:가상계좌, SC0100:무통장입금 단독)
$LGD_AMOUNT                 = $order_price;                             //금액("," 를 제외한 금액을 입력하세요)
$LGD_PRODUCTINFO            = $goods_name;                              //상품명
$LGD_TID                    = $od_tno;                                  //LG유플러스 거래번호
$LGD_CUSTOM_MERTNAME        = $default['de_admin_company_name'];        //상점명
$LGD_CUSTOM_CEONAME         = $default['de_admin_company_owner'];       //대표자명
$LGD_CUSTOM_BUSINESSNUM     = $default['de_admin_company_saupja_no'];   //사업자등록번호
$LGD_CUSTOM_MERTPHONE       = $default['de_admin_company_tel'];         //상점 전화번호
$LGD_CASHCARDNUM            = $_POST['id_info'];                        //발급번호(주민등록번호,현금영수증카드번호,휴대폰번호 등등)
$LGD_CASHRECEIPTUSE         = $_POST['tr_code'];                        //현금영수증발급용도('1':소득공제, '2':지출증빙)

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

$xpay->Init_TX($LGD_MID);
$xpay->Set("LGD_TXNAME", "CashReceipt");
$xpay->Set("LGD_METHOD", $LGD_METHOD);
$xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

if ($LGD_METHOD == "AUTH") {                 // 현금영수증 발급 요청
    $xpay->Set("LGD_OID", $LGD_OID);
    $xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
    $xpay->Set("LGD_CASHCARDNUM", $LGD_CASHCARDNUM);
    $xpay->Set("LGD_CUSTOM_MERTNAME", $LGD_CUSTOM_MERTNAME);
    $xpay->Set("LGD_CUSTOM_CEONAME", $LGD_CUSTOM_CEONAME);
    $xpay->Set("LGD_CUSTOM_BUSINESSNUM", $LGD_CUSTOM_BUSINESSNUM);
    $xpay->Set("LGD_CUSTOM_MERTPHONE", $LGD_CUSTOM_MERTPHONE);
    $xpay->Set("LGD_CASHRECEIPTUSE", $LGD_CASHRECEIPTUSE);
    $xpay->Set("LGD_ENCODING",    "UTF-8");

    if(isset($od['od_tax_flag']) && $od['od_tax_flag'] && $od['od_free_mny'] > 0) {
        $xpay->Set("LGD_TAXFREEAMOUNT", $od['od_free_mny']); //비과세 금액
    }

    if ($LGD_PAYTYPE == "SC0030"){              //기결제된 계좌이체건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $LGD_TID);
    }
    else if ($LGD_PAYTYPE == "SC0040"){         //기결제된 가상계좌건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $LGD_TID);
        $xpay->Set("LGD_SEQNO", $od_casseqno);
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
    /*
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
    */

    if($xpay->Response_Code() == '0000') {
        $LGD_OID = $xpay->Response("LGD_OID",0);
        $cash_no = $xpay->Response("LGD_CASHRECEIPTNUM",0);

        $cash = array();
        $cash['LGD_TID']        = $xpay->Response("LGD_TID",0);
        $cash['LGD_TIMESTAMP']  = $xpay->Response("LGD_TIMESTAMP",0);
        $cash['LGD_RESPDATE']   = $xpay->Response("LGD_RESPDATE",0);
        $cash_info = serialize($cash);

        if($tx == 'personalpay') {
            $sql = " update {$g5['g5_shop_personalpay_table']}
                        set pp_cash = '1',
                            pp_cash_no = '$cash_no',
                            pp_cash_info = '$cash_info'
                      where pp_id = '$LGD_OID' ";
        } else {
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_cash = '1',
                            od_cash_no = '$cash_no',
                            od_cash_info = '$cash_info'
                      where od_id = '$LGD_OID' ";
        }

        $result = sql_query($sql, false);

        if(!$result) { // DB 정보갱신 실패시 취소
            $xpay->Set("LGD_TXNAME", "CashReceipt");
            $xpay->Set("LGD_METHOD", "CANCEL");
            $xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);
            $xpay->Set("LGD_TID", $LGD_TID);

            if ($LGD_PAYTYPE == "SC0040"){				//가상계좌건 현금영수증 발급취소시 필수
                $xpay->Set("LGD_SEQNO", $od_casseqno);
            }

            if ($xpay->TX()) {
                /*
                echo "현금영수증 취소 요청처리가 완료되었습니다.  <br>";
                echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
                echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

                echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
                echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<br>";
                echo "거래번호 : " . $xpay->Response("LGD_TID",0) . "<p>";
                */
            } else {
                $msg = '현금영수증 취소 요청처리가 정상적으로 완료되지 않았습니다.';
                if(!$is_admin)
                    $msg .= '쇼핑몰 관리자에게 문의해 주십시오.';

                alert_close($msg);
            }
        }
    }

} else {
    //2)API 요청 실패 화면처리
    /*
    echo "현금영수증 발급/취소 요청처리가 실패되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
    */

    $msg = '현금영수증 발급 요청처리가 정상적으로 완료되지 않았습니다.';
    $msg .= '\\nTX Response_code = '.$xpay->Response_Code();
    $msg .= '\\nTX Response_msg = '.$xpay->Response_Msg();

    alert($msg);
}

$g5['title'] = '';
include_once(G5_PATH.'/head.sub.php');

if($default['de_card_test']) {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS.'"></script>'.PHP_EOL;
} else {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS.'"></script>'.PHP_EOL;
}

switch($LGD_PAYTYPE) {
    case 'SC0030':
        $trade_type = 'BANK';
        break;
    case 'SC0040':
        $trade_type = 'CAS';
        break;
    default:
        $trade_type = 'CR';
        break;
}
?>

<div id="lg_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - 토스페이먼츠 eCredit</h1>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $xpay->Response_Code(); ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $xpay->Response_Msg(); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo $xpay->Response("LGD_TID",0); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo $xpay->Response("LGD_CASHRECEIPTNUM",0); ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6",$xpay->Response("LGD_RESPDATE",0)); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showCashReceipts('<?php echo $LGD_MID; ?>','<?php echo $LGD_OID; ?>','<?php echo $od_casseqno; ?>','<?php echo $trade_type; ?>','<?php echo $CST_PLATFORM; ?>');">영수증 확인</button>
                <p>영수증 확인은 실 등록의 경우에만 가능합니다.</p>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        </tbody>
        </table>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');