<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$companynumber = isset($_REQUEST['companynumber']) ? clean_xss_tags($_REQUEST['companynumber'], 1, 1) : '';

if($tx == 'personalpay') {
    $od = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">개인결제 내역이 존재하지 않습니다.</p>');

    if($od['pp_cash'] == 1)
        alert('이미 등록된 현금영수증 입니다.');

    $buyername = $od['pp_name'];
    $goodname  = $od['pp_name'].'님 개인결제';
    $amt_tot   = (int)$od['pp_receipt_price'];
    $amt_sup   = (int)round(($amt_tot * 10) / 11);
    $amt_svc   = 0;
    $amt_tax   = (int)($amt_tot - $amt_sup);
} else {
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

    if($od['od_cash'] == 1)
        alert('이미 등록된 현금영수증 입니다.');

    $buyername = $od['od_name'];
    $goods     = get_goods($od['od_id']);
    $goodname  = $goods['full_name'];
    $amt_tot   = (int)$od['od_tax_mny'] + (int)$od['od_vat_mny'] + (int)$od['od_free_mny'];
    $amt_sup   = (int)$od['od_tax_mny'] + (int)$od['od_free_mny'];
    $amt_tax   = (int)$od['od_vat_mny'];
    $amt_svc   = 0;
}

$reg_num  = $id_info;
$useopt   = $tr_code;

/****************************************
 현금영수증 발급 관련 parameter
*****************************************
-필수값-
* GoodsName (결제상품명)
* MID (상점아이디)
* Moid (주문번호)
* BuyerName (구매자명)
* ReceiptAmt (현금영수증 요청금액)
* ReceiptSupplyAmt (공급가액)
* ReceiptVAT (부가가치세)
* ReceiptServiceAmt (봉사료)
* ReceiptType (증빙구분)
* ReceiptTypeNo (식별값) 
*****************************************/

$nicepay->m_GoodsName           = $goodname; // 상품명
$nicepay->m_Moid                = $od_id;
$nicepay->m_BuyerName           = $buyername;
$nicepay->m_ReceiptAmt          = $amt_tot;
$nicepay->m_ReceiptSupplyAmt    = $amt_sup;
$nicepay->m_ReceiptVAT          = $amt_tax;
$nicepay->m_ReceiptServiceAmt   = $amt_svc;
$nicepay->m_ReceiptType         = $useopt;
$nicepay->m_ReceiptTypeNo       = $reg_num;


/****************
 * 발급 요청 *
 ****************/
$nicepay->startAction();

/********************************************************************************
 * 발급 결과                                                    
 *
 * 결제 결과 코드   : $nicepay->m_ResultData['ResultCode'] ("7001" 이면 발행 성공)
 * 결제 결과 메시지 : $nicepay->m_ResultData['ResultMsg'] (현금영수증 발행 승인번호)
 * 거래번호         : $nicepay->m_ResultData['TID']
 * 상점ID           : $nicepay->m_ResultData['MID']
 * 승인날짜         : $nicepay->m_ResultData['AuthDate'] (YYYYMMDDHHMMSS)
 * 승인코드         : $nicepay->m_ResultData['AuthCode']
 ********************************************************************************/

 // DB 반영
if($nicepay->m_ResultData['ResultCode'] == '7001') {
    $cash_no = $nicepay->m_ResultData['AuthCode'];

    $cash = array();
    $cash['TID']       = $nicepay->m_ResultData['TID'];
    $cash['AuthCode']   = $nicepay->m_ResultData['AuthCode'];
    $cash['AuthDate']  = $nicepay->m_ResultData['AuthDate'];
    $cash_info = serialize($cash);

    if($tx == 'personalpay') {
        $sql = " update {$g5['g5_shop_personalpay_table']}
                    set pp_cash = '1',
                        pp_cash_no = '$cash_no',
                        pp_cash_info = '$cash_info'
                  where pp_id = '$od_id' ";
    } else {
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_cash = '1',
                        od_cash_no = '$cash_no',
                        od_cash_info = '$cash_info'
                  where od_id = '$od_id' ";
    }

    $result = sql_query($sql, false);

    if(!$result)
        include G5_SHOP_PATH.'/nicepay/nicepay_cancel.php';
}

$g5['title'] = '현금영수증 발급';
include_once(G5_PATH.'/head.sub.php');
?>

<div id="lg_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - 나이스페이</h1>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $nicepay->m_ResultData['ResultCode']; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $nicepay->m_ResultData['ResultMsg']; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo $nicepay->m_ResultData['TID']; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo $nicepay->m_ResultData['ApplNum']; ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6",$nicepay->m_ResultData['AuthDate']); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showreceipt();">영수증 확인</button>
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