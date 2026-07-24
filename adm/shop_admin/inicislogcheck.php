<?php
$sub_menu = '400420';
include_once('./_common.php');

check_demo();
auth_check_menu($auth, $sub_menu, 'w');
check_admin_token();

include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once('./inicislog.lib.php');

if (!inicis_pro_audit_schema_ready())
    alert('결제 현황 DB 업그레이드를 먼저 실행해 주십시오.', G5_ADMIN_URL.'/dbupgrade.php');
if (inicis_pro_get_iniapi_key() === '')
    alert('KG이니시스 INIAPI KEY를 먼저 설정해 주십시오.', './configform.php');

$tables = inicis_pro_audit_tables();
$mode = isset($_POST['mode']) && !is_array($_POST['mode']) ? $_POST['mode'] : '';
$targets = array();

if ($mode === 'batch') {
    $order_amount_sql = "(o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_coupon - o.od_coupon - o.od_send_coupon - o.od_receipt_point)";
    $anomaly_sql = inicis_admin_anomaly_sql();
    $result = sql_query(" select p.*,
                                 o.od_id as order_oid, o.od_tno as order_tid, o.od_status as order_status,
                                 o.od_receipt_price as order_receipt_price, o.od_misu as order_misu, o.od_cancel_price as order_cancel_price,
                                 o.od_refund_price as order_refund_price, o.od_settle_case as order_settle_case, $order_amount_sql as order_amount,
                                 pp.pp_id as personal_oid, pp.pp_tno as personal_tid, pp.pp_price as personal_amount, pp.pp_receipt_price as personal_receipt_price
                            from `{$tables['summary']}` p
                            left join {$g5['g5_shop_order_table']} o on p.ip_order_type <> 'personal' and o.od_id = p.ip_oid
                            left join {$g5['g5_shop_personalpay_table']} pp on p.ip_order_type = 'personal' and pp.pp_id = p.ip_oid
                            left join {$g5['g5_shop_inicis_log_table']} il on il.oid = p.ip_oid
                           where (p.ip_tid <> '' or (il.P_TID <> '' and il.P_STATUS = '00'))
                             and $anomaly_sql
                           order by p.ip_updated_at asc
                           limit 5 ");
    while ($row = sql_fetch_array($result))
        $targets[] = $row;
} else {
    $oid_raw = isset($_POST['oid']) && !is_array($_POST['oid']) ? trim($_POST['oid']) : '';
    $oid = inicis_pro_clean_oid($oid_raw);
    if ($oid === '' || $oid !== $oid_raw)
        alert('결제 주문번호가 올바르지 않습니다.', './inicisloglist.php');

    $row = sql_fetch(" select * from `{$tables['summary']}` where ip_oid = '".sql_escape_string($oid)."' ");
    if (empty($row['ip_id']))
        alert('결제 처리 이력을 찾을 수 없습니다.', './inicisloglist.php');
    $targets[] = $row;
}

if (!count($targets))
    alert('KG이니시스에서 조회할 확인 필요 거래가 없습니다.', './inicisloglist.php');

$success_count = 0;
$failed_count = 0;
$last_oid = '';
foreach ($targets as $target) {
    $last_oid = $target['ip_oid'];
    $target_tid = !empty($target['ip_tid']) ? $target['ip_tid'] : inicis_pro_recover_approved_tid($target['ip_oid'], 'admin');
    if ($target_tid === '') {
        if ($mode !== 'batch')
            alert('승인 TID가 없어 KG이니시스 거래조회를 실행할 수 없습니다. 인증 전 미완료 결제이거나 승인 TID가 복구되지 않은 거래입니다.', './inicislogview.php?oid='.urlencode($target['ip_oid']));
        $failed_count++;
        continue;
    }
    $inquiry = inicis_pro_inquiry($target_tid, $target['ip_oid'], $target);
    if (inicis_pro_save_inquiry($target['ip_oid'], $inquiry, 'admin') && !empty($inquiry['success']))
        $success_count++;
    else
        $failed_count++;
}

$message = 'KG이니시스 거래조회가 완료되었습니다. 성공 '.$success_count.'건, 실패 '.$failed_count.'건';
if ($mode !== 'batch' && $last_oid !== '')
    alert($message, './inicislogview.php?oid='.urlencode($last_oid));

alert($message, './inicisloglist.php?risk=anomaly');
