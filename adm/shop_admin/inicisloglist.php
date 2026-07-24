<?php
$sub_menu = '400420';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once('./inicislog.lib.php');

$g5['title'] = 'KG이니시스 INIpay PRO 결제 처리 현황';
$tables = inicis_pro_audit_tables();
$tables_ready = inicis_pro_audit_tables_ready();
$schema_ready = inicis_pro_audit_schema_ready();

$status = isset($_GET['status']) && !is_array($_GET['status']) ? preg_replace('/[^a-z_]/', '', strtolower($_GET['status'])) : '';
$allowed_status = array('', 'request_ready', 'request_expired', 'authentication_received', 'authentication_failed', 'validation_failed', 'approval_started', 'communication_failed', 'approval_failed', 'approved', 'order_saving', 'order_saved', 'notification_received', 'notification_failed', 'vbank_issued', 'paid', 'paid_after_cancel', 'refund_required', 'refund_completed', 'cancel_requested', 'canceled', 'cancel_failed', 'partial_canceled', 'partial_cancel_failed');
if (!in_array($status, $allowed_status))
    $status = '';

$risk = isset($_GET['risk']) && !is_array($_GET['risk']) ? preg_replace('/[^a-z_]/', '', strtolower($_GET['risk'])) : '';
if (!in_array($risk, array('', 'anomaly', 'failed', 'processing', 'complete', 'canceled', 'abandoned')))
    $risk = '';

$sfl = isset($_GET['sfl']) && !is_array($_GET['sfl']) ? $_GET['sfl'] : 'ip_oid';
if (!in_array($sfl, array('ip_oid', 'ip_tid', 'ip_auth_tid', 'mb_id')))
    $sfl = 'ip_oid';
$stx = isset($_GET['stx']) && !is_array($_GET['stx']) ? get_search_string($_GET['stx']) : '';
$fr_date = isset($_GET['fr_date']) && !is_array($_GET['fr_date']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['fr_date']) ? $_GET['fr_date'] : '';
$to_date = isset($_GET['to_date']) && !is_array($_GET['to_date']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_GET['to_date']) ? $_GET['to_date'] : '';

include_once(G5_ADMIN_PATH.'/admin.head.php');

if (!$tables_ready) {
?>
<div class="local_desc02 local_desc">
    <p>결제 현황 테이블이 아직 설치되지 않았습니다. 최고관리자로 <a href="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php"><strong>DB 업그레이드</strong></a>를 실행한 후 이용해 주십시오.</p>
</div>
<?php
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}
if (!$schema_ready) {
?>
<div class="local_desc02 local_desc">
    <p>결제 현황 테이블의 운영 감시 필드가 설치되지 않았습니다. 최고관리자로 <a href="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php"><strong>DB 업그레이드</strong></a>를 실행한 후 이용해 주십시오.</p>
</div>
<?php
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$order_amount_sql = "(o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_coupon - o.od_coupon - o.od_send_coupon - o.od_receipt_point)";
$has_order_sql = "((p.ip_order_type = 'personal' and pp.pp_id is not null) or (p.ip_order_type <> 'personal' and o.od_id is not null))";
$anomaly_sql = inicis_admin_anomaly_sql();

$where = array();
if ($status === 'refund_required')
    $where[] = "p.ip_refund_required = '1'";
elseif ($status !== '')
    $where[] = "(p.ip_status = '".sql_escape_string($status)."' or p.ip_noti_status = '".sql_escape_string($status)."' or p.ip_cancel_status = '".sql_escape_string($status)."')";
if ($stx !== '')
    $where[] = "p.$sfl like '%".sql_escape_string($stx)."%'";
if ($fr_date !== '')
    $where[] = "p.ip_created_at >= '".sql_escape_string($fr_date)." 00:00:00'";
if ($to_date !== '')
    $where[] = "p.ip_created_at <= '".sql_escape_string($to_date)." 23:59:59'";

if ($risk === 'anomaly')
    $where[] = $anomaly_sql;
elseif ($risk === 'failed')
    $where[] = "(p.ip_status in ('authentication_failed','validation_failed','communication_failed','approval_failed') or p.ip_noti_status = 'notification_failed' or p.ip_cancel_status in ('cancel_failed','partial_cancel_failed'))";
elseif ($risk === 'processing')
    $where[] = "(p.ip_status in ('request_ready','authentication_received','approval_started','order_saving','cancel_requested') or p.ip_cancel_status = 'cancel_requested')";
elseif ($risk === 'complete')
    $where[] = "$has_order_sql and p.ip_status in ('order_saved','paid','refund_completed') and not ($anomaly_sql)";
elseif ($risk === 'canceled')
    $where[] = "(p.ip_status = 'canceled' or p.ip_cancel_status in ('canceled','partial_canceled'))";
elseif ($risk === 'abandoned')
    $where[] = "p.ip_status = 'request_expired'";

$sql_from = " from `{$tables['summary']}` p
               left join {$g5['g5_shop_order_table']} o on p.ip_order_type <> 'personal' and o.od_id = p.ip_oid
               left join {$g5['g5_shop_personalpay_table']} pp on p.ip_order_type = 'personal' and pp.pp_id = p.ip_oid ";
$sql_where = count($where) ? ' where '.implode(' and ', $where) : '';

$count = sql_fetch(" select count(*) as cnt $sql_from $sql_where ");
$total_count = isset($count['cnt']) ? (int) $count['cnt'] : 0;
$all_count = sql_fetch(" select count(*) as cnt from `{$tables['summary']}` ");
$anomaly_count = sql_fetch(" select count(*) as cnt $sql_from where $anomaly_sql ");
$failed_count = sql_fetch(" select count(*) as cnt from `{$tables['summary']}` where ip_status in ('authentication_failed','validation_failed','communication_failed','approval_failed') or ip_noti_status = 'notification_failed' or ip_cancel_status in ('cancel_failed','partial_cancel_failed') ");
$audit_error_count = sql_fetch(" select count(*) as cnt from `{$tables['summary']}` where ip_audit_error = '1' ");

$rows = (int) $config['cf_page_rows'];
if ($rows < 1)
    $rows = 20;
$total_page = ceil($total_count / $rows);
if ($page < 1)
    $page = 1;
if ($total_page > 0 && $page > $total_page)
    $page = (int) $total_page;
$from_record = ($page - 1) * $rows;

$sql = " select p.*,
                o.od_id as order_oid, o.od_tno as order_tid, o.od_status as order_status, o.od_receipt_price as order_receipt_price,
                o.od_misu as order_misu, o.od_cancel_price as order_cancel_price, o.od_refund_price as order_refund_price, o.od_settle_case as order_settle_case, $order_amount_sql as order_amount,
                pp.pp_id as personal_oid, pp.pp_tno as personal_tid, pp.pp_price as personal_amount, pp.pp_receipt_price as personal_receipt_price
           $sql_from
           $sql_where
          order by p.ip_id desc
          limit $from_record, $rows ";
$result = sql_query($sql);

$query = array(
    'status' => $status,
    'risk' => $risk,
    'sfl' => $sfl,
    'stx' => $stx,
    'fr_date' => $fr_date,
    'to_date' => $to_date
);
$query_string = http_build_query($query, '', '&amp;');
// 상세보기 링크는 목록으로 돌아올 때 현재 페이지까지 유지하도록 page를 포함한다.
$view_query_string = $query_string.'&amp;page='.$page;
?>

<div class="local_ov01 local_ov">
    <a href="./inicisloglist.php" class="ov_listall">전체목록</a>
    <span class="btn_ov01"><span class="ov_txt">전체</span><span class="ov_num"><?php echo number_format((int) $all_count['cnt']); ?>건</span></span>
    <a href="?risk=anomaly" class="btn_ov01"><span class="ov_txt">확인 필요</span><span class="ov_num"><?php echo number_format((int) $anomaly_count['cnt']); ?>건</span></a>
    <a href="?risk=failed" class="btn_ov01"><span class="ov_txt">실패</span><span class="ov_num"><?php echo number_format((int) $failed_count['cnt']); ?>건</span></a>
</div>

<div class="local_desc01 local_desc">
    <p>이 화면은 INIpay PRO의 결제 요청, 인증, 승인, 주문 저장, 서버 통보 및 취소 이력을 표시합니다. KG 거래조회 결과가 있으면 실제 PG 상태와 주문 DB를 함께 비교합니다. 거래조회 실패 또는 조회 전 거래는 KG이니시스 상점관리자에서 TID와 금액을 다시 확인하십시오.</p>
    <p>최근 자동 감시: <?php echo !empty($default['de_inicis_pro_monitor_at']) ? get_text($default['de_inicis_pro_monitor_at']) : '실행 기록 없음'; ?><?php if (!empty($default['de_inicis_pro_monitor_message'])) { ?> / <?php echo get_text($default['de_inicis_pro_monitor_message']); ?><?php } ?></p>
    <p>감사 이력 기록 오류: <strong<?php echo !empty($audit_error_count['cnt']) ? ' style="color:#d00"' : ''; ?>><?php echo number_format((int) $audit_error_count['cnt']); ?>건</strong> / 통보 허용 IP: <?php echo get_text(implode(', ', inicis_pro_noti_allowed_ips())); ?></p>
</div>

<form method="post" action="./inicislogcheck.php" style="margin-bottom:10px">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="mode" value="batch">
    <button type="submit" class="btn btn_02" onclick="return confirm('확인 필요 거래 중 조회 가능한 최근 5건을 KG이니시스에서 조회하시겠습니까? 주문 생성이나 결제 취소는 실행하지 않습니다.');">확인 필요 거래 KG 대사</button>
</form>

<form class="local_sch03 local_sch" method="get">
    <div>
        <strong>처리 구분</strong>
        <select name="risk">
            <option value=""<?php echo get_selected($risk, ''); ?>>전체</option>
            <option value="anomaly"<?php echo get_selected($risk, 'anomaly'); ?>>확인 필요</option>
            <option value="failed"<?php echo get_selected($risk, 'failed'); ?>>실패</option>
            <option value="processing"<?php echo get_selected($risk, 'processing'); ?>>처리 중</option>
            <option value="complete"<?php echo get_selected($risk, 'complete'); ?>>정상 완료</option>
            <option value="canceled"<?php echo get_selected($risk, 'canceled'); ?>>취소 완료</option>
            <option value="abandoned"<?php echo get_selected($risk, 'abandoned'); ?>>결제창 종료·미진행</option>
        </select>
        <strong>현재 상태</strong>
        <select name="status">
            <option value="">전체</option>
            <?php foreach ($allowed_status as $status_value) { if ($status_value === '') continue; ?>
            <option value="<?php echo $status_value; ?>"<?php echo get_selected($status, $status_value); ?>><?php echo get_text(inicis_admin_status_label($status_value)); ?></option>
            <?php } ?>
        </select>
        <strong>기간</strong>
        <input type="text" id="fr_date" name="fr_date" value="<?php echo get_text($fr_date); ?>" class="frm_input" size="10" maxlength="10" placeholder="YYYY-MM-DD">
        ~
        <input type="text" id="to_date" name="to_date" value="<?php echo get_text($to_date); ?>" class="frm_input" size="10" maxlength="10" placeholder="YYYY-MM-DD">
    </div>
    <div>
        <select name="sfl">
            <option value="ip_oid"<?php echo get_selected($sfl, 'ip_oid'); ?>>주문번호 OID</option>
            <option value="ip_tid"<?php echo get_selected($sfl, 'ip_tid'); ?>>승인 TID</option>
            <option value="ip_auth_tid"<?php echo get_selected($sfl, 'ip_auth_tid'); ?>>인증 TID</option>
            <option value="mb_id"<?php echo get_selected($sfl, 'mb_id'); ?>>회원 ID</option>
        </select>
        <input type="text" name="stx" value="<?php echo get_text($stx); ?>" class="frm_input" size="30">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>KG이니시스 INIpay PRO 결제 처리 현황 목록</caption>
    <thead>
    <tr>
        <th scope="col">최근 처리일시</th>
        <th scope="col">주문번호</th>
        <th scope="col">승인 TID</th>
        <th scope="col">회원</th>
        <th scope="col">구분</th>
        <th scope="col">결제수단</th>
        <th scope="col">금액</th>
        <th scope="col">현재 상태</th>
        <th scope="col">KG 거래상태</th>
        <th scope="col">주문 확인</th>
        <th scope="col">이력</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $has_order = inicis_admin_has_order($row);
        $is_anomaly = inicis_admin_is_anomaly($row);
        $order_type_label = $row['ip_order_type'] === 'personal' ? '개인결제' : '주문';
        $device_label = $row['ip_device'] === 'MOBILE' ? '모바일' : ($row['ip_device'] === 'WEB' ? 'PC' : $row['ip_device']);
        $bg = 'bg'.($i % 2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_time"><?php echo get_text($row['ip_updated_at']); ?></td>
        <td class="td_odrnum2"><a href="./inicislogview.php?oid=<?php echo urlencode($row['ip_oid']); ?>&amp;<?php echo $view_query_string; ?>"><?php echo get_text($row['ip_oid']); ?></a></td>
        <td class="td_left"><?php echo $row['ip_tid'] !== '' ? get_text($row['ip_tid']) : '-'; ?></td>
        <td class="td_name"><?php echo $row['mb_id'] !== '' ? get_text($row['mb_id']) : '비회원'; ?></td>
        <td class="td_center"><?php echo get_text($order_type_label.' / '.$device_label); ?><br><?php
            if ($row['ip_environment'] === 'test') echo '<strong style="color:#d00">테스트</strong>';
            elseif ($row['ip_environment'] === 'live') echo '<span style="font-size:11px">실결제</span>';
            else echo '<span style="font-size:11px;color:#d00">확인 필요</span>';
        ?></td>
        <td class="td_center"><?php echo get_text(inicis_admin_pay_type_label($row['ip_pay_type'], $row['ip_easy_pay'])); ?></td>
        <td class="td_price"><?php echo number_format((int) $row['ip_amount']); ?></td>
        <td class="td_center"><?php echo get_text(inicis_admin_status_label(inicis_admin_primary_status($row))); ?></td>
        <td class="td_center">
            <?php if (!empty($row['ip_pg_checked_at'])) { ?>
                <?php echo $row['ip_pg_result_code'] === '00' ? get_text(inicis_admin_pg_status_label($row['ip_pg_status'])) : '<strong style="color:#d00">조회 실패</strong>'; ?><br>
                <span style="font-size:11px"><?php echo get_text($row['ip_pg_checked_at']); ?></span>
            <?php } else { ?>미조회<?php } ?>
        </td>
        <td class="td_center">
            <?php if ($is_anomaly) { ?><strong style="color:#d00">확인 필요</strong>
            <?php } elseif ($has_order) { ?><span style="color:#168b3f">정상 연결</span>
            <?php } elseif ($row['ip_status'] === 'canceled') { ?>취소됨
            <?php } else { ?>미생성<?php } ?>
        </td>
        <td class="td_num"><?php echo number_format((int) $row['ip_event_count']); ?></td>
        <td class="td_mng td_mng_s">
            <a href="./inicislogview.php?oid=<?php echo urlencode($row['ip_oid']); ?>&amp;<?php echo $view_query_string; ?>" class="btn btn_03">상세</a>
            <?php if ($has_order && $row['ip_order_type'] === 'personal') { ?>
            <a href="./personalpayform.php?w=u&amp;pp_id=<?php echo urlencode($row['ip_oid']); ?>" class="btn btn_02">개인</a>
            <?php } elseif ($has_order) { ?>
            <a href="./orderform.php?od_id=<?php echo urlencode($row['ip_oid']); ?>" class="btn btn_02">주문</a>
            <?php } ?>
        </td>
    </tr>
    <?php }
    if ($i === 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php
$paging_url = './inicisloglist.php?'.$query_string.'&amp;page=';
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $paging_url);
?>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
