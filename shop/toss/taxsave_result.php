<?php
include_once('./_common.php');
require_once(G5_SHOP_PATH.'/settle_toss.inc.php');

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
    $od_name     = $od['pp_name'];
    $od_email    = $od['pp_email'];
    $od_tel      = $od['pp_hp'];
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
    $od_name     = $od['od_name'];
    $od_email    = $od['od_email'];
    $od_tel      = $od['od_tel'];
}

switch($settle_case) {
    case '가상계좌':
    case '계좌이체':
    case '무통장':
        // 토스페이먼츠는 결제수단 구분 없이 현금영수증 발급 가능
        break;
    default:
        die('<p id="scash_empty">현금영수증은 무통장, 가상계좌, 계좌이체에 한해 발급요청이 가능합니다.</p>');
        break;
}

// 토스페이먼츠 현금영수증 발급 요청
$orderId = $od_id;
$amount = $order_price;
$type = ($_POST['tr_code'] == '0') ? '소득공제' : '지출증빙';
$customerIdentityNumber = $_POST['id_info'];
$orderName = $od_name;
$customerEmail = $_POST['buyeremail'] ?: $od_email;
$customerMobilePhone = $_POST['buyertel'] ?: $od_tel;

// 토스페이먼츠 현금영수증 발급 API 호출
$toss->setCashReceiptsData([
    'orderId' => $orderId,
    'amount' => $amount,
    'type' => $type,
    'customerIdentityNumber' => $customerIdentityNumber,
    'orderName' => $goods_name,
]);
$toss_result = $toss->issueCashReceipt();

/*
 * 토스페이먼츠 현금영수증 발급 요청 결과처리
 */
if ($toss_result && isset($toss->responseData['receiptKey'])) {
    // 현금영수증 발급 성공
    $data = $toss->responseData;
    $receiptKey = $data['receiptKey']; // 현금영수증 발급 키
    $cash_no = $data['issueNumber']; // 현금영수증 발급 번호
    $approvedAt = $data['requestedAt'];

    $cash = array();
    $cash['receiptKey'] = $receiptKey;
    $cash['approvedAt'] = $approvedAt;
    $cash['orderId'] = $data['orderId'];
    $cash['amount'] = $data['amount'];
    $cash['type'] = $data['type'];
    $cash['receiptUrl'] = $data['receiptUrl'];
    $cash_info = serialize($cash);

    if($tx == 'personalpay') {
        $sql = " update {$g5['g5_shop_personalpay_table']}
                    set pp_cash = '1',
                        pp_cash_no = '$cash_no',
                        pp_cash_info = '$cash_info'
                  where pp_id = '$orderId' ";
    } else {
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_cash = '1',
                        od_cash_no = '$cash_no',
                        od_cash_info = '$cash_info'
                  where od_id = '$orderId' ";
    }

    $result = sql_query($sql, false);

    if(!$result) { // DB 정보갱신 실패시 취소
        $cancel_result = $toss->cancelCashReceipt($receiptKey, 'DB 업데이트 실패로 인한 취소');

        if (!$cancel_result) {
            $msg = '현금영수증 취소 요청처리가 정상적으로 완료되지 않았습니다.\\n'. $toss->responseData['message'];
            alert_close($msg);
        }
    }

} else {
    // API 요청 실패 화면처리
    $msg = '현금영수증 발급 요청처리가 정상적으로 완료되지 않았습니다.\\n'. $toss->responseData['message'];
    alert($msg);
}

$g5['title'] = '';
include_once(G5_PATH.'/head.sub.php');
?>


<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://dashboard.tosspayments.com/receipt/mids/si_<?php echo $config['cf_lg_mid']; ?>/orders/<?php echo $orderId; ?>/cash-receipt?ref=dashboard";
    window.open(showreceiptUrl,"showreceipt","width=380,height=540, scrollbars=no,resizable=no");
}
</script>

<div id="toss_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - 토스페이먼츠</h1>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php if ($toss_result && isset($toss->responseData['receiptKey'])): ?>
        <tr>
            <th scope="row">결과</th>
            <td>발급 완료</td>
        </tr>
        <tr>
            <th scope="row">현금영수증 발급번호</th>
            <td><?php echo $toss->responseData['issueNumber']; ?></td>
        </tr>
        <tr>
            <th scope="row">주문번호</th>
            <td><?php echo $toss->responseData['orderId']; ?></td>
        </tr>
        <tr>
            <th scope="row">발급 유형</th>
            <td><?php echo $toss->responseData['type']; ?></td>
        </tr>
        <tr>
            <th scope="row">금액</th>
            <td><?php echo number_format($toss->responseData['amount']); ?>원</td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo date('Y-m-d H:i:s', strtotime($toss->responseData['requestedAt'])); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 확인</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:showreceipt();">영수증 확인</button>
                <p>영수증 확인은 실 등록의 경우에만 가능합니다.</p>
            </td>
        </tr>
        <?php else: ?>
        <tr>
            <th scope="row">결과</th>
            <td>발급 실패</td>
        </tr>
        <tr>
            <th scope="row">오류 메시지</th>
            <td><?php echo isset($toss_result['error']) ? $toss_result['error'] : '알 수 없는 오류가 발생했습니다.'; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td colspan="2"></td>
        </tr>
        </tbody>
        </table>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');