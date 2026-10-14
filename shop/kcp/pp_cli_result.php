<?php
include_once './_common.php';
require_once G5_LIB_PATH.'/kcp_cash.lib.php';
$stored = get_session('ss_kcp_cash_result');
if (!is_array($stored) || empty($stored['result']) || $stored['expires'] < time()) {
    alert('확인할 현금영수증 처리 결과가 없습니다.');
    exit;
}
$result = $stored['result'];
$g5['title'] = '현금영수증 처리 결과';
include_once G5_PATH.'/head.sub.php';
?>
<div class="new_win" id="kcp_req_rx">
    <h1 id="win_title">현금영수증 처리 결과</h1>
    <div class="new_win_con">
        <p><?php echo kcp_cash_html($result['message']); ?></p>
        <?php if ($result['state'] === 'issued') { ?>
        <p>발급 금액: <?php echo number_format($result['amount']); ?>원</p>
        <?php } ?>
        <p>처리 확인번호: <?php echo kcp_cash_html($result['attempt']); ?></p>
        <p>발급 내역은 주문 상세 화면에서 확인할 수 있습니다.</p>
    </div>
    <div class="win_btn"><button type="button" class="btn_close" onclick="window.close()">닫기</button></div>
</div>
<?php include_once G5_PATH.'/tail.sub.php'; ?>
