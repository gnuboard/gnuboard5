<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

$iv = get_inicis_iniapi_iv();
if (strlen($iv) !== 16){
    alert('쇼핑몰설정 < KG이니시스 INIAPI IV 값을 16자리로 설정 후 이용해 주세요.');
}

require_once(G5_LIB_PATH.'/shop_cash.lib.php');

// KVE-2026-3247: POST·출처·주문에 묶인 일회용 토큰·식별번호 형식·발급 조건($_POST만 사용)을 확인하고,
// PG 호출 전에 주문을 조건부로 선점(정확히 1행)한 뒤 발급한다. 발급되지 않았으면 안내 후 종료한다.
// 이니시스 요청·응답 검증(KVE-2026-3248)은 shop_cash_gateway_inicis()에서 한다.
$cash_result    = shop_cash_handle('inicis', 'shop_cash_gateway_inicis');
$cash_view      = $cash_result['display'];
$result_code    = $cash_view['code'];
$result_msg     = $cash_view['msg'];
$cash_tid       = $cash_view['tid'];
$cash_no        = $cash_view['cash_no'];
$cash_appl_time = $cash_view['appl_time'];

$g5['title'] = '현금영수증 발급';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
function showreceipt() // 현금 영수증 출력
{
    var showreceiptUrl = "https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/Cash_mCmReceipt.jsp?noTid=" + encodeURIComponent(<?php echo get_js_safe_string($cash_tid); ?>) + "&clpaymethod=22";
    window.open(showreceiptUrl,"showreceipt","width=380,height=540, scrollbars=no,resizable=no");
}
</script>

<div id="lg_req_tx" class="new_win">
    <h1 id="win_title">현금영수증 - KG이니시스</h1>

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
            <td><?php echo get_text($cash_tid); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo get_text($cash_no); ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo get_text($cash_appl_time); ?></td>
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