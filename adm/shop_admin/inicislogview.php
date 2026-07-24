<?php
$sub_menu = '400420';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once('./inicislog.lib.php');

$oid = isset($_GET['oid']) && !is_array($_GET['oid']) ? inicis_pro_clean_oid($_GET['oid']) : '';
if ($oid === '' || !isset($_GET['oid']) || $oid !== $_GET['oid'])
    alert('결제 주문번호가 올바르지 않습니다.', './inicisloglist.php');

$tables = inicis_pro_audit_tables();
if (!inicis_pro_audit_schema_ready())
    alert('결제 현황 테이블이 설치되지 않았습니다. DB 업그레이드를 실행해 주십시오.', G5_ADMIN_URL.'/dbupgrade.php');

// 목록으로 돌아갈 때 넘어온 검색 조건과 페이지를 그대로 유지한다.
$list_query = array();
foreach (array('status', 'risk', 'sfl', 'stx', 'fr_date', 'to_date', 'page') as $list_key) {
    if (isset($_GET[$list_key]) && !is_array($_GET[$list_key]) && $_GET[$list_key] !== '')
        $list_query[$list_key] = (string) $_GET[$list_key];
}
$list_url = './inicisloglist.php'.(count($list_query) ? '?'.http_build_query($list_query, '', '&amp;') : '');

$oid_sql = sql_escape_string($oid);
$order_amount_sql = "(o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_coupon - o.od_coupon - o.od_send_coupon - o.od_receipt_point)";
$sql = " select p.*,
                o.od_id as order_oid, o.od_tno as order_tid, o.od_status as order_status, o.od_time as order_time,
                o.od_receipt_price as order_receipt_price, o.od_misu as order_misu, o.od_cancel_price as order_cancel_price, o.od_refund_price as order_refund_price,
                o.od_settle_case as order_settle_case, $order_amount_sql as order_amount,
                pp.pp_id as personal_oid, pp.pp_tno as personal_tid, pp.pp_price as personal_amount, pp.pp_receipt_price as personal_receipt_price, pp.pp_time as personal_time
           from `{$tables['summary']}` p
           left join {$g5['g5_shop_order_table']} o on p.ip_order_type <> 'personal' and o.od_id = p.ip_oid
           left join {$g5['g5_shop_personalpay_table']} pp on p.ip_order_type = 'personal' and pp.pp_id = p.ip_oid
          where p.ip_oid = '$oid_sql' ";
$row = sql_fetch($sql);
if (empty($row['ip_id']))
    alert('결제 처리 이력을 찾을 수 없습니다.', './inicisloglist.php');

$events = sql_query(" select * from `{$tables['event']}` where ip_oid = '$oid_sql' order by pe_id asc ");
$has_order = inicis_admin_has_order($row);
$is_anomaly = inicis_admin_is_anomaly($row);
$saved_tid = inicis_admin_order_tid($row);
$saved_amount = inicis_admin_order_amount($row);
$legacy_pro_log = empty($row['ip_tid']) ? inicis_pro_get_log($oid) : array();
$legacy_pro_data = isset($legacy_pro_log['pro_data']) && is_array($legacy_pro_log['pro_data']) ? $legacy_pro_log['pro_data'] : array();
// 승인 TID가 있거나 승인 로그에서 복구할 수 있을 때만 조회한다.
// TID를 참조할 수 없는 미완료(인증 전) 거래는 KG에 조회할 대상이 없다.
$can_inquiry = !empty($row['ip_tid'])
    || (!empty($legacy_pro_data['__pro']) && $legacy_pro_data['__pro'] === '1'
        && isset($legacy_pro_log['P_STATUS']) && $legacy_pro_log['P_STATUS'] === '00' && !empty($legacy_pro_data['P_APPL_TID']));
$g5['title'] = 'KG이니시스 INIpay PRO 결제 처리 상세';
include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_desc01 local_desc">
    <p>이 기록은 영카트 서버가 처리한 단계의 감사 이력입니다. 최종 결제·취소 여부는 KG이니시스 상점관리자의 TID 거래내역과 대조하여 확정하십시오.</p>
    <p>설정된 보존기간이 지난 단계별 상세 이력은 순차 삭제될 수 있으며, 아래 누적 이력 수에는 삭제 전 기록도 포함됩니다.</p>
    <?php if (!empty($row['ip_audit_error'])) { ?><p><strong style="color:#d00">이 거래에서 감사 상세 이력 기록 오류가 감지됐습니다. 서버 오류 로그와 KG이니시스 거래내역을 함께 확인하십시오.</strong></p><?php } ?>
    <?php if (!empty($row['ip_refund_required'])) { ?><p><strong style="color:#d00">취소 주문에 입금이 확인되어 환불이 필요합니다. 상품을 발송하지 말고 KG이니시스 상점관리자에서 입금 및 환불 상태를 확인하십시오.</strong></p><?php } ?>
</div>

<div class="local_desc02 local_desc">
    <p><strong><?php echo $is_anomaly ? '권장 조치' : '확인 결과'; ?>:</strong> <?php echo get_text(inicis_admin_recommendation($row)); ?></p>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>결제 처리 요약</caption>
    <tbody>
    <tr><th scope="row">주문번호 OID</th><td><?php echo get_text($row['ip_oid']); ?></td><th scope="row">처리 판정</th><td><?php echo $is_anomaly ? '<strong style="color:#d00">확인 필요</strong>' : ($has_order ? '<span style="color:#168b3f">주문 정상 연결</span>' : get_text(inicis_admin_status_label(inicis_admin_primary_status($row)))); ?></td></tr>
    <tr><th scope="row">인증 TID</th><td><?php echo $row['ip_auth_tid'] !== '' ? get_text($row['ip_auth_tid']) : '-'; ?></td><th scope="row">승인 TID</th><td><?php echo $row['ip_tid'] !== '' ? get_text($row['ip_tid']) : '-'; ?></td></tr>
    <tr><th scope="row">회원 ID</th><td><?php echo $row['mb_id'] !== '' ? get_text($row['mb_id']) : '비회원'; ?></td><th scope="row">MID</th><td><?php echo get_text($row['ip_mid']); ?></td></tr>
    <tr><th scope="row">결제금액</th><td><?php echo number_format((int) $row['ip_amount']); ?>원</td><th scope="row">결제수단</th><td><?php echo get_text(inicis_admin_pay_type_label($row['ip_pay_type'], $row['ip_easy_pay'])); ?></td></tr>
    <tr><th scope="row">결제환경</th><td><?php echo $row['ip_environment'] === 'test' ? '<strong style="color:#d00">테스트</strong>' : ($row['ip_environment'] === 'live' ? '실결제' : '<strong style="color:#d00">확인 필요</strong>'); ?></td><th scope="row">환불 확인 필요</th><td><?php echo !empty($row['ip_refund_required']) ? '<strong style="color:#d00">예</strong>' : '아니오'; ?></td></tr>
    <tr><th scope="row">접속 구분</th><td><?php echo get_text($row['ip_device']); ?></td><th scope="row">결제 구분</th><td><?php echo $row['ip_order_type'] === 'personal' ? '개인결제' : '일반주문'; ?></td></tr>
    <tr><th scope="row">현재 상태</th><td><?php echo get_text(inicis_admin_status_label(inicis_admin_primary_status($row))); ?></td><th scope="row">결과 코드</th><td><?php echo $row['ip_result_code'] !== '' ? get_text($row['ip_result_code']) : '-'; ?></td></tr>
    <tr><th scope="row">최근 서버 통보</th><td><?php echo $row['ip_noti_status'] !== '' ? get_text(inicis_admin_status_label($row['ip_noti_status'])).($row['ip_noti_code'] !== '' ? ' ('.get_text($row['ip_noti_code']).')' : '') : '-'; ?></td><th scope="row">통보 실패 누계</th><td><?php echo number_format((int) $row['ip_noti_failed_count']); ?>회</td></tr>
    <tr><th scope="row">최근 취소 처리</th><td><?php echo $row['ip_cancel_status'] !== '' ? get_text(inicis_admin_status_label($row['ip_cancel_status'])).($row['ip_cancel_code'] !== '' ? ' ('.get_text($row['ip_cancel_code']).')' : '') : '-'; ?></td><th scope="row">가상계좌 입금기한</th><td><?php echo !empty($row['ip_vbank_due_at']) ? get_text($row['ip_vbank_due_at']) : '-'; ?></td></tr>
    <tr><th scope="row">통보 메시지</th><td><?php echo $row['ip_noti_message'] !== '' ? get_text($row['ip_noti_message']) : '-'; ?></td><th scope="row">취소 메시지</th><td><?php echo $row['ip_cancel_message'] !== '' ? get_text($row['ip_cancel_message']) : '-'; ?></td></tr>
    <tr><th scope="row">감사 이력 상태</th><td><?php echo !empty($row['ip_audit_error']) ? '<strong style="color:#d00">상세 기록 오류</strong>' : '정상'; ?></td><th scope="row">최근 이상 알림</th><td><?php echo !empty($row['ip_alerted_at']) ? get_text($row['ip_alerted_at']) : '-'; ?></td></tr>
    <tr><th scope="row">마지막 메시지</th><td colspan="3"><?php echo $row['ip_result_message'] !== '' ? get_text($row['ip_result_message']) : '-'; ?></td></tr>
    <tr><th scope="row">승인 일시</th><td><?php echo !empty($row['ip_approved_at']) ? get_text($row['ip_approved_at']) : '-'; ?></td><th scope="row">주문 저장 일시</th><td><?php echo !empty($row['ip_ordered_at']) ? get_text($row['ip_ordered_at']) : '-'; ?></td></tr>
    <tr><th scope="row">통보 일시</th><td><?php echo !empty($row['ip_notified_at']) ? get_text($row['ip_notified_at']) : '-'; ?></td><th scope="row">취소 일시</th><td><?php echo !empty($row['ip_canceled_at']) ? get_text($row['ip_canceled_at']) : '-'; ?></td></tr>
    <tr><th scope="row">주문 DB의 TID</th><td><?php echo $has_order ? get_text($saved_tid) : '-'; ?></td><th scope="row">주문 DB의 금액</th><td><?php echo $has_order ? number_format($saved_amount).'원' : '-'; ?></td></tr>
    <?php if ($row['ip_order_type'] !== 'personal') { ?><tr><th scope="row">주문 상태/입금액</th><td><?php echo $has_order ? get_text($row['order_status']).' / '.number_format((int) $row['order_receipt_price']).'원' : '-'; ?></td><th scope="row">주문 미수금/취소액</th><td><?php echo $has_order ? number_format((int) $row['order_misu']).'원 / '.number_format((int) $row['order_cancel_price']).'원' : '-'; ?></td></tr><?php } ?>
    <tr><th scope="row">KG 거래조회 상태</th><td><?php echo !empty($row['ip_pg_checked_at']) ? ($row['ip_pg_result_code'] === '00' ? get_text(inicis_admin_pg_status_label($row['ip_pg_status'])) : '조회 실패 ('.get_text($row['ip_pg_result_code']).')') : '미조회'; ?></td><th scope="row">KG 거래조회 일시</th><td><?php echo !empty($row['ip_pg_checked_at']) ? get_text($row['ip_pg_checked_at']) : '-'; ?></td></tr>
    <tr><th scope="row">KG 조회 TID</th><td><?php echo !empty($row['ip_pg_tid']) ? get_text($row['ip_pg_tid']) : '-'; ?></td><th scope="row">KG 조회 금액</th><td><?php echo (int) $row['ip_pg_amount'] > 0 ? number_format((int) $row['ip_pg_amount']).'원' : '-'; ?></td></tr>
    <tr><th scope="row">KG 조회 메시지</th><td colspan="3"><?php echo !empty($row['ip_pg_message']) ? get_text($row['ip_pg_message']) : '-'; ?></td></tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="<?php echo $list_url; ?>" class="btn btn_02">목록</a>
    <?php if ($can_inquiry) { ?>
    <form method="post" action="./inicislogcheck.php" style="display:inline">
        <input type="hidden" name="token" value="">
        <input type="hidden" name="mode" value="single">
        <input type="hidden" name="oid" value="<?php echo get_text($row['ip_oid']); ?>">
        <button type="submit" class="btn btn_02" onclick="return confirm('이 거래를 KG이니시스에서 조회하시겠습니까? 주문 생성이나 결제 취소는 실행하지 않습니다.');">KG 거래조회</button>
    </form>
    <?php } ?>
    <?php if ($has_order && $row['ip_order_type'] === 'personal') { ?>
    <a href="./personalpayform.php?w=u&amp;pp_id=<?php echo urlencode($row['ip_oid']); ?>" class="btn btn_03">개인결제 보기</a>
    <?php } elseif ($has_order) { ?>
    <a href="./orderform.php?od_id=<?php echo urlencode($row['ip_oid']); ?>" class="btn btn_03">주문 보기</a>
    <?php } ?>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>결제 단계별 처리 이력</caption>
    <thead>
    <tr>
        <th scope="col">순서</th>
        <th scope="col">처리일시</th>
        <th scope="col">경로</th>
        <th scope="col">단계</th>
        <th scope="col">결과</th>
        <th scope="col">코드</th>
        <th scope="col">메시지</th>
        <th scope="col">IP</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 1; $event = sql_fetch_array($events); $i++) { ?>
    <tr class="bg<?php echo ($i - 1) % 2; ?>">
        <td class="td_num"><?php echo $i; ?></td>
        <td class="td_time"><?php echo get_text($event['pe_created_at']); ?></td>
        <td class="td_center"><?php echo get_text(inicis_admin_source_label($event['pe_source'])); ?></td>
        <td class="td_center"><?php echo get_text(inicis_admin_stage_label($event['pe_stage'])); ?></td>
        <td class="td_center"><?php echo get_text(inicis_admin_status_label($event['pe_status'])); ?></td>
        <td class="td_center"><?php echo $event['pe_code'] !== '' ? get_text($event['pe_code']) : '-'; ?></td>
        <td class="td_left"><?php echo $event['pe_message'] !== '' ? get_text($event['pe_message']) : '-'; ?></td>
        <td class="td_center"><?php echo get_text($event['pe_ip']); ?></td>
    </tr>
    <?php }
    if ($i === 1)
        echo '<tr><td colspan="8" class="empty_table">단계별 이력이 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
