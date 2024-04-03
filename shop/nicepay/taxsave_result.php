<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

/*
 *
 * 현금결제(실시간 은행계좌이체, 무통장입금)에 대한 현금결제 영수증 발행 요청한다.
 *
 */

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
    $amt_tax   = (int)$od['od_vat_mny'];
    $amt_svc   = 0;
    $amt_sup = (int)$od['od_tax_mny'];
    $amt_free = (int)$od['od_free_mny'];
}


$reg_num  = $id_info;
$useopt   = $tr_code;
$currency = 'WON';

//step1. 요청을 위한 파라미터 설정
$goodName      = $goodname;                     // 상품명
$crPrice       = $amt_tot;// 총 현금결제 금액
$supPrice      = $amt_sup;// 공급가액
$tax           = $amt_tax;// 부가세
$srcvPrice     = $amt_svc;// 봉사료
$buyerName     = $buyername;// 구매자 성명
$buyerEmail    = $buyeremail;// 구매자 이메일 주소
$buyerTel      = $buyertel;// 구매자 전화번호
$useOpt        = $useopt;// 현금영수증 발행용도 ("1" - 소비자 소득공제용, "2" - 사업자 지출증빙용)
$regNum        = $reg_num;// 현금결제자 주민등록번호

$ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);

// 04 (현금영수증), 01 (매체구분 일반), 시간정보 (12자리), 랜덤 4자리숫자
$tid = $default['de_nicepay_mid'].'04'.'01'.substr($ediDate, 2).rand(1000, 9999);
$signData = bin2hex(hash('sha256', $default['de_nicepay_mid'].$amt_tot.$ediDate.$od['od_id'].$default['de_nicepay_key'], true));

$data = array(
    'MID' => $default['de_nicepay_mid'],
    'TID' => $tid,
    'EdiDate' => $ediDate,
    'Moid' => $od['od_id'],
    'SignData' => $signData,
    'GoodsName' => iconv('utf-8', 'euc-kr', $goodName),
    'ReceiptAmt' => $amt_tot,
    'ReceiptType' => ($useopt > 1) ? 2 : 1,
    'ReceiptTypeNo' => $regNum,
    'ReceiptSupplyAmt' => $supPrice,
    'ReceiptVAT' => $tax,
    'ReceiptServiceAmt' => $srcvPrice,
    'ReceiptTaxFreeAmt' => (int)$od['od_free_mny'],
    'CharSet' => 'utf-8',
);

$response = nicepay_reqPost($data, "https://pg-api.nicepay.co.kr/webapi/cash_receipt.jsp");

$result = json_decode($response, true);

if (function_exists('add_log')) add_log($result, true, 'rr');

// 성공이면
if (isset($result['ResultCode']) && $result['ResultCode'] === '7001') {

    // DB 반영
    $cash_no = $result['AuthCode'];       // 현금영수증 승인번호

    $cash = array();
    $cash['TID']       = $result['TID'];
    $cash['ApplNum']   = $cash_no;
    $cash['AuthDate']  = $result['AuthDate'];
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

    $sql_result = sql_query($sql, false);

} else {
    //2)API 요청 실패 화면처리

    $msg = '현금영수증 발급 요청처리가 정상적으로 완료되지 않았습니다.';
    $msg .= '\\nTX Response_code = '.$result['ResultCode'];
    $msg .= '\\nTX Response_msg = '.$result['ResultMsg'];

    alert($msg);
}

$g5['title'] = '현금영수증 발급';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://npg.nicepay.co.kr/issue/IssueLoader.do?type=1&TID=<?php echo($result['TID']); ?>";
    window.open(showreceiptUrl,"showreceipt","width=430,height=700, scrollbars=no,resizable=no");
}
</script>

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
            <td><?php echo $result['ResultCode']; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $result['ResultMsg']; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo $result['TID']; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo $result['AuthCode']; ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $result['AuthDate']); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showreceipt();">영수증 확인</button>
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