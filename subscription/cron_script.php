<?php
include_once('./_common.php');

$t = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';

if (! $t) {
    // die('abc');
}

$is_db_success = true;

if ($is_db_success) {
    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET su_cron_updatetime = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);
}

$tomorrow = date('Y-m-d', strtotime('+1 day', G5_SERVER_TIME));

$sql = "select * from `{$g5['g5_subscription_order_table']}` where card_billkey != '' and od_enable_status = 1 and next_billing_date <= '".G5_TIME_YMDHIS."' limit 1000";

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
    
    /*
            $nextBillingDate = calculateNextBillingDate($row);
            
            $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET next_billing_date = '".$nextBillingDate."', last_billed_date = '".G5_TIME_YMDHIS."' WHERE od_id = '".$row['od_id']."'";
            
            echo $updateQuery;
            
            sql_query($updateQuery);
    */
    
    $pays = subscription_process_payment($row, $row['od_pg']);
    
    print_r($pays);
    
    // 정기결제가 성공이면
    if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {
        
        $pay_round_no = (int) $row['od_pays_total'] + 1;
        $insert_id = subscription_order_pay($row, $pays, $pay_round_no);
        
        // 성공이면
        if ($insert_id) {
            
            $nextBillingDate = calculateNextBillingDate($row);
            
            $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET next_billing_date = '".$nextBillingDate."', last_billed_date = '".G5_TIME_YMDHIS."', od_pays_total = '".$pay_round_no."' WHERE od_id = '".$row['od_id']."'";
            
            sql_query($updateQuery);
            
            $od_name = $row['od_name'];
            $od_email = $row['od_email'];
            
            include_once(G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php');
            include_once(G5_SUBSCRIPTION_PATH.'/cron_ordermail2.inc.php');
            
        } else {
            // 실패시 처리
            
            if (function_exists('add_log')) {
                add_log(array('error'=>'fail1'), false, '_subscription_fail_');
            }
        }
        
    } else {
        // 실패시 처리
        
        if (function_exists('add_log')) {
            add_log(array('error'=>'fail2'), false, '_subscription_fail_');
        }
    }
}