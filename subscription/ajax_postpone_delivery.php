<?php
include_once './_common.php';

// print_r2($_POST); exit;

// 배송 미루기 기능을 합니다.

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$reason = isset($_POST['reason']) ? clean_xss_tags($_POST['reason']) : '';
$new_delivery_date = isset($_POST['new_delivery_date']) ? clean_xss_tags($_POST['new_delivery_date']) : '';

if (!$od_id) {
    exit;
}

// 날짜 형식 검증
/*
$new_date = DateTime::createFromFormat('Y-m-d', $new_delivery_date);
$today = new DateTime();
if (!$new_date || $new_date <= $today) {
    http_response_code(400);
    echo json_encode(['error' => '유효한 미래 날짜를 입력하세요.']);
    exit;
}
*/

$od = get_subscription_order($od_id);

if (!(isset($od['od_id']) && $od['od_id'])) {
    die('no-order');
}

if ($is_admin !== 'super' && $member['mb_id'] !== $od['mb_id']) {
    die('권한이 없습니다.');
}

// 기존 배송 날짜
$original_delivery_date = get_subscription_delivery_date($od);

// 다음 배송일 구하는 함수
$new_delivery_date = calculateNextDeliveryDate($od);

// 다음 결제일 구하는 함수
$nextBillingDate = calculateNextBillingDate($od, $new_delivery_date);

try {
    // 배송 날짜 업데이트
    $updateQuery = "UPDATE `{$g5['g5_subscription_order_table']}` SET next_billing_date = '$nextBillingDate', 
    new_delivery_date = '$new_delivery_date' WHERE od_id = '{$od['od_id']}'";

    sql_query($updateQuery);

    // 변경 내역 저장

    $Insertsql = "INSERT INTO `{$g5['g5_subscription_delay_schedules_table']}` (mb_id, od_id, od_cycle, reason, created_at, original_delivery_date, new_delivery_date) VALUES (
    '" . $od['od_id'] . "',
    '" . $member['mb_id'] . "',
    '" . $od['od_pays_total'] . "',
    '" . $reason . "',
    '" . G5_TIME_YMDHIS . "',
    '" . $original_delivery_date . "',
    '" . $nextBillingDate . "'
    )
    ";

    sql_query($Insertsql);

    add_subscription_order_history(($od['od_pays_total'] + 1). ' 회차 배송 미루기를 했습니다.', array(
        'hs_type' => 'subscription_order_delay',
        'od_id' => $od_id,
        'mb_id' => $member['mb_id']
    ));

    echo json_encode(array('success' => '다음 배송으로 미루기가 적용되었습니다.', 'new_delivery_date' => $new_delivery_date));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => '서버 오류: ' . $e->getMessage()));
}