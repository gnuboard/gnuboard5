<?php
include_once('./_common.php');

$is_db_success = true;

if ($is_db_success) {
    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET su_cron_updatetime = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);
}

$sql = "select * from `{$g5['g5_subscription_order_table']} where card_billkey != '' and od_enable_status = 1 and next_billing_date <= '".G5_TIME_YMDHIS."' ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++) {
    
    /*
    $userId = $subscription['user_id'];
    $amount = 100; // 결제 금액 (사용자에 따라 다를 수 있음)
    
    $isSuccess = processPayment($userId, $amount);
    
    if ($isSuccess) {
        // 결제 성공 시 next_billing_date 업데이트
        $nextBillingDate = calculateNextBillingDate($subscription['next_billing_date'], $subscription['billing_interval']);
        
        $updateQuery = "UPDATE subscriptions SET next_billing_date = ?, last_billed_date = ? WHERE id = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param('ssi', $nextBillingDate, $today, $subscription['id']);
        $updateStmt->execute();
    } else {
        // 결제 실패 시 로직 추가 (알림 등)
    }
    */
    
    $pays = subscription_process_payment($row);
    
    // 정기결제가 성공이면
    if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {
        
        $insert_id = subscription_order_pay($row, $pays);
        
        // 성공이면
        if ($insert_id) {
            
            $nextBillingDate = calculateNextBillingDate($subscription['next_billing_date'], $subscription['billing_interval']);
            
            $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET next_billing_date = '".$nextBillingDate."', last_billed_date = '".G5_TIME_YMDHIS."' WHERE od_id = '$od_id'";
            
            sql_query($updateQuery);
            
        } else {
            // 실패시 처리
        }
        
    } else {
        // 실패시 처리
    }

}