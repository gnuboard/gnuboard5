<?php
include_once('./_common.php');
require_once(G5_SHOP_PATH.'/settle_toss.inc.php');

require_once(G5_LIB_PATH.'/shop_cash.lib.php');

// KVE-2026-3247: POST·출처·주문에 묶인 일회용 토큰·식별번호 형식·발급 조건($_POST만 사용)을 확인하고,
// PG 호출 전에 주문을 조건부로 선점(정확히 1행)한 뒤 발급한다. 발급되지 않았으면 안내 후 종료한다.
$cash_result = shop_cash_handle('toss', 'shop_cash_gateway_toss');
$cash_view   = $cash_result['display'];
$orderId     = $cash_view['oid'];

$g5['title'] = '';
include_once(G5_PATH.'/head.sub.php');
?>


<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://dashboard.tosspayments.com/receipt/mids/si_" + encodeURIComponent(<?php echo get_js_safe_string($config['cf_lg_mid']); ?>) + "/orders/" + encodeURIComponent(<?php echo get_js_safe_string($orderId); ?>) + "/cash-receipt?ref=dashboard";
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
        <?php if ($cash_result['state'] === 'issued'): ?>
        <tr>
            <th scope="row">결과</th>
            <td>발급 완료</td>
        </tr>
        <tr>
            <th scope="row">현금영수증 발급번호</th>
            <td><?php echo get_text($cash_view['cash_no']); ?></td>
        </tr>
        <tr>
            <th scope="row">주문번호</th>
            <td><?php echo get_text($cash_view['oid']); ?></td>
        </tr>
        <tr>
            <th scope="row">발급 유형</th>
            <td><?php echo get_text($cash_view['type']); ?></td>
        </tr>
        <tr>
            <th scope="row">금액</th>
            <td><?php echo number_format((int)$cash_view['amount']); ?>원</td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo $cash_view['approved'] !== '' && strtotime($cash_view['approved']) ? date('Y-m-d H:i:s', strtotime($cash_view['approved'])) : ''; ?></td>
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
            <td>알 수 없는 오류가 발생했습니다.</td>
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
