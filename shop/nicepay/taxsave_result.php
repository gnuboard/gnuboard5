<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

require_once(G5_LIB_PATH.'/shop_cash.lib.php');

// KVE-2026-3247: POST·출처·주문에 묶인 일회용 토큰·식별번호 형식·발급 조건($_POST만 사용)을 확인하고,
// PG 호출 전에 주문을 조건부로 선점(정확히 1행)한 뒤 발급한다. 발급되지 않았으면 안내 후 종료한다.
$cash_result      = shop_cash_handle('nicepay', 'shop_cash_gateway_nicepay');
$cash_view        = $cash_result['display'];
$result_code      = $cash_view['code'];
$result_msg       = $cash_view['msg'];
$result_tid       = $cash_view['tid'];
$result_auth_code = $cash_view['cash_no'];
$result_auth_date = $cash_view['auth_date'];

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
