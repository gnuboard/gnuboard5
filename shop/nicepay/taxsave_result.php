<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 방법으로 이용해 주십시오.', G5_SHOP_URL);
}

if (function_exists('check_request_origin')) {
    check_request_origin(G5_SHOP_URL);
}

/*
 *
 * 현금결제(실시간 은행계좌이체, 무통장입금)에 대한 현금결제 영수증 발행 요청한다.
 *
 */

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$tx    = isset($_POST['tx']) ? clean_xss_tags($_POST['tx'], 1, 1) : '';
$reg_num = isset($_POST['id_info']) ? preg_replace('/[^0-9]/', '', $_POST['id_info']) : '';
$useopt = isset($_POST['tr_code']) ? (int)$_POST['tr_code'] : 0;
$buyeremail = isset($_POST['buyeremail']) ? clean_xss_tags($_POST['buyeremail'], 1, 1) : '';
$buyertel = isset($_POST['buyertel']) ? clean_xss_tags($_POST['buyertel'], 1, 1) : '';

if (!$od_id) {
    alert('주문번호가 누락되었습니다.', G5_SHOP_URL);
}

if (!in_array($useopt, array(1, 2))) {
    alert('현금영수증 발행용도가 올바르지 않습니다.');
}

if (($useopt == 1 && !in_array(strlen($reg_num), array(10, 11, 13))) || ($useopt == 2 && strlen($reg_num) != 10)) {
    alert('현금영수증 발급번호를 정확히 입력해 주시기 바랍니다.');
}

if($tx == 'personalpay') {
    $od = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">개인결제 내역이 존재하지 않습니다.</p>');

    // IDOR 방지: 본인 개인결제거나 정당한 세션 uid 보유 시에만 허용
    if (function_exists('is_shop_order_owner') && !is_shop_order_owner($od, 'personalpay')) {
        alert('해당 개인결제 정보에 접근 권한이 없습니다.', G5_SHOP_URL);
    }

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

    // IDOR 방지: 본인 주문이거나 정당한 세션 uid 보유 시에만 허용
    if (function_exists('is_shop_order_owner') && !is_shop_order_owner($od, 'order')) {
        alert('해당 주문 정보에 접근 권한이 없습니다.', G5_SHOP_URL);
    }

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
$moid = ($tx == 'personalpay') ? $od['pp_id'] : $od['od_id'];
$receipt_tax_free_amt = ($tx == 'personalpay') ? 0 : (int)$od['od_free_mny'];

// 04 (현금영수증), 01 (매체구분 일반), 시간정보 (12자리), 랜덤 4자리숫자
$tid = $default['de_nicepay_mid'].'04'.'01'.substr($ediDate, 2).rand(1000, 9999);
$signData = bin2hex(hash('sha256', $default['de_nicepay_mid'].$amt_tot.$ediDate.$moid.$default['de_nicepay_key'], true));

$data = array(
    'MID' => $default['de_nicepay_mid'],
    'TID' => $tid,
    'EdiDate' => $ediDate,
    'Moid' => $moid,
    'SignData' => $signData,
    'GoodsName' => iconv('utf-8', 'euc-kr', $goodName),
    'ReceiptAmt' => $amt_tot,
    'ReceiptType' => ($useopt > 1) ? 2 : 1,
    'ReceiptTypeNo' => $regNum,
    'ReceiptSupplyAmt' => $supPrice,
    'ReceiptVAT' => $tax,
    'ReceiptServiceAmt' => $srcvPrice,
    'ReceiptTaxFreeAmt' => $receipt_tax_free_amt,
    'CharSet' => 'utf-8',
);

$response = nicepay_reqPost($data, "https://pg-api.nicepay.co.kr/webapi/cash_receipt.jsp");

$result = json_decode($response, true);

if (!is_array($result)) {
    alert('현금영수증 발급 요청처리가 정상적으로 완료되지 않았습니다.\\n나이스페이 응답을 확인할 수 없습니다.');
}

$result_code = isset($result['ResultCode']) ? $result['ResultCode'] : '';
$result_msg  = isset($result['ResultMsg']) ? $result['ResultMsg'] : '';
$result_tid  = isset($result['TID']) ? $result['TID'] : '';
$result_auth_code = isset($result['AuthCode']) ? $result['AuthCode'] : '';
$result_auth_date = isset($result['AuthDate']) ? $result['AuthDate'] : '';

// 성공이면
if ($result_code === '7001') {

    if (!$result_tid || !$result_auth_code) {
        alert('현금영수증 발급 응답 정보가 올바르지 않습니다.');
    }

    // DB 반영
    $cash_no = $result_auth_code;       // 현금영수증 승인번호

    $cash = array();
    $cash['TID']       = $result_tid;
    $cash['ApplNum']   = $cash_no;
    $cash['AuthDate']  = $result_auth_date;
    $cash_info = sql_escape_string(serialize($cash));
    $cash_no = sql_escape_string($cash_no);

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

    if (!$sql_result) {
        alert_close('현금영수증은 나이스페이에 발급 요청되었으나 쇼핑몰 DB 반영에 실패했습니다.\\n나이스페이 관리자에서 현금영수증 발급 상태를 확인하고 필요 시 수동 취소해 주세요.\\n현금영수증 거래번호 : '.$result_tid);
    }

} else {
    //2)API 요청 실패 화면처리

    $msg = '현금영수증 발급 요청처리가 정상적으로 완료되지 않았습니다.';
    $msg .= '\\nTX Response_code = '.$result_code;
    $msg .= '\\nTX Response_msg = '.$result_msg;

    alert($msg);
}

$g5['title'] = '현금영수증 발급';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://npg.nicepay.co.kr/issue/IssueLoader.do?type=1&TID=" + <?php echo function_exists('get_js_safe_string') ? get_js_safe_string($result_tid) : '"'.str_replace('"', '\\"', $result_tid).'"'; ?>;
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
            <td><?php echo get_text($result_code); ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo get_text($result_msg); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo get_text($result_tid); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo get_text($result_auth_code); ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo get_text(preg_replace("/([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $result_auth_date)); ?></td>
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
