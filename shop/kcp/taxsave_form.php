<?php
if (!defined('_GNUBOARD_')) exit;
require_once G5_LIB_PATH.'/kcp_cash.lib.php';
$cash_type = $tx === 'personalpay' ? 'personalpay' : 'order';
try {
    // 화면과 처리 경로에서 같은 원장/정책 검증을 사용한다.
    $cash_order = kcp_cash_order($cash_type, (string)$od_id);
    $cash_data = kcp_cash_data($cash_order, $cash_type);
    $cash_token = kcp_cash_token($cash_type, (string)$od_id);
} catch (KcpCashRequestException $e) {
    alert($e->getMessage());
    exit;
} catch (Throwable $e) {
    alert('주문 정보를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
    exit;
}
?>
<div id="scash" class="new_win">
    <h1 id="win_title">현금영수증 발급</h1>
    <section class="new_win_con">
        <h2>주문정보</h2>
        <div class="tbl_head01 tbl_wrap"><table><tbody>
            <tr><th scope="row">주문 번호</th><td><?php echo kcp_cash_html($od_id); ?></td></tr>
            <tr><th scope="row">상품 정보</th><td><?php echo kcp_cash_html($cash_data['good_name']); ?></td></tr>
            <tr><th scope="row">주문자</th><td><?php echo kcp_cash_html($cash_data['buyr_name']); ?></td></tr>
            <tr><th scope="row">발급 금액</th><td><?php echo number_format($cash_data['amt_tot']); ?>원</td></tr>
            <tr><th scope="row">공급가액</th><td><?php echo number_format($cash_data['amt_sup']); ?>원</td></tr>
            <tr><th scope="row">부가가치세</th><td><?php echo number_format($cash_data['amt_tax']); ?>원</td></tr>
        </tbody></table></div>
        <form action="<?php echo kcp_cash_html(G5_SHOP_URL); ?>/kcp/pp_cli_hub.php" method="post" autocomplete="off">
            <input type="hidden" name="req_tx" value="pay">
            <input type="hidden" name="tx" value="<?php echo $cash_type; ?>">
            <input type="hidden" name="ordr_idxx" value="<?php echo kcp_cash_html($od_id); ?>">
            <input type="hidden" name="cash_token" value="<?php echo $cash_token; ?>">
            <h2>발급 정보</h2>
            <p><label><input type="radio" name="tr_code" value="0" checked> 소득공제용</label>
            <label><input type="radio" name="tr_code" value="1"> 지출증빙용</label></p>
            <p><label for="id_info">주민·휴대폰번호 또는 사업자번호</label>
            <input type="text" id="id_info" name="id_info" class="frm_input" inputmode="numeric" pattern="[0-9]{10,13}" maxlength="13" required> 숫자만 입력</p>
            <div id="scash_apply"><button type="submit" class="btn_submit">발급 신청</button></div>
        </form>
    </section>
</div>
