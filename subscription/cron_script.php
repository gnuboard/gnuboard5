<?php
include_once('./_common.php');

$t = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';

if (! $t) {
    die('abc');
}

$is_db_success = true;

if ($is_db_success) {
    
    sql_bind_update($g5['g5_subscription_config_table'], array('su_cron_updatetime'=>G5_TIME_YMDHIS));
    
    /*
    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET su_cron_updatetime = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);
    
    sql_bind_update(
        $g5['g5_subscription_order_table'],
        array('next_billing_date'=>$nextBillingDate,
        'last_billed_date'=>G5_TIME_YMDHIS,
        'od_pays_total'=>1),
        array('od_id'=>$od_id)
    );
    */
}

if ($_SERVER['REMOTE_ADDR'] !== '59.10.38.2') {
    die('');
}

// $tomorrow = date('Y-m-d', strtotime('+1 day', G5_SERVER_TIME));

/*
$sql = "select * from `{$g5['g5_subscription_order_table']}` where card_billkey != '' and od_enable_status = 1 and next_billing_date <= '".G5_TIME_YMDHIS."' limit 1000";

echo $sql;
exit;
*/

$subscription_wheres = array('card_billkey' => array('!=' => ''), 'od_enable_status' => 1, 'next_billing_date' => array('<=' => G5_TIME_YMDHIS));

// 현재 설정중인 PG만 결제하려면
$subscription_wheres['od_pg'] = get_subs_option('su_pg_service');

$result_row = sql_bind_select_array(
    $g5['g5_subscription_order_table'],
    '*',
    $subscription_wheres,
    array('limit'=> 500)
);

echo count($result_row);
echo "<br>";

foreach($result_row as $od) {
    
    $row = $od;
    
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    // 이용횟수
    $od_number_of_uses = isset($od_subscription_selected_number['use_input']) ? $od_subscription_selected_number['use_input'] : 0;
    
    // 만약에 해당 주문의 이용횟수가 있어서 이용횟수 기간이 지났다면
    if ($od_number_of_uses && $od['od_pays_total'] >= $od_number_of_uses) {
        
        // 비활성화한다.
        sql_bind_update($g5['g5_subscription_order_table'], array('od_enable_status'=>0), array('od_id'=>$od['od_id']));
        
        continue;
    }
    
    $today_exists = sql_bind_select_fetch($g5['g5_subscription_pay_table'], 'count(*) as total', array('od_id'=>$od['od_id'], 'DATE(py_time)'=> date('Y-m-d', G5_SERVER_TIME)));
    
    if ($today_exists > 1) {
        // 오늘 2회 이상 결제가 되었는데 어떻게 처리해야 될까?
    }
    
    $pays = subscription_process_payment($od, $od['od_pg']);
    
    print_r2( $pays );
    
    $od_name = $od['od_name'];
    $od_email = $od['od_email'];
    $od_id = $od['od_id'];
    $is_pay_fail = 0;
    
    // 정기결제가 성공이면
    if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {
        
        $pay_round_no = (int) $od['od_pays_total'] + 1;
        $insert_id = subscription_order_pay($od, $pays['response'], $pay_round_no);
        
        // 성공이면
        if ($insert_id) {
            
            $nextBillingDate = calculateNextBillingDate($od);
            
            //$updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET next_billing_date = '".$nextBillingDate."', last_billed_date = '".G5_TIME_YMDHIS."', od_pays_total = '".$pay_round_no."' WHERE od_id = '".$od['od_id']."'";
            
            //sql_query($updateQuery);
            
            $result = sql_bind_update(
                $g5['g5_subscription_order_table'],
                array(
                    'next_billing_date'=>$nextBillingDate,
                    'last_billed_date'=>G5_TIME_YMDHIS,
                    'od_pays_total'=>$pay_round_no,
                    'od_fail_count' => 0
                ), 
                array('od_id'=>$od['od_id'])
            );
            
            add_subscription_order_history('정기구독 '.$pay_round_no.'회차 결제에 성공했습니다.', array(
                'hs_type' => 'subscription_order',
                'od_id' => $od_id,
                'mb_id' => $member['mb_id']
            ));
            
            include_once(G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php');
            include_once(G5_SUBSCRIPTION_PATH.'/cron_ordermail2.inc.php');
            
        } else {
            // 실패시 처리
            
            if (function_exists('add_log')) {
                add_log(array('error'=>'fail1'), false, '_subscription_fail_');
            }
            
            $failure_reason = '(크론) 결제에 성공했으나, DB 쓰기에 실패했습니다.('.$pay_round_no.'회차) 코드 : '.$pays['code'].' 이유 : '.$pays['message'];
            
            add_subscription_order_history($failure_reason, array(
                'hs_type' => 'subscription_pay',
                'od_id' => $od['od_id'],
                'mb_id' => $od['mb_id']
            ));
            
            include_once(G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php');
            include_once(G5_SUBSCRIPTION_PATH.'/mail/fail_db.mail.php');
            
            // 연속으로 몇번 이상 실패시 해당 구독을 비활성화 해야 한다.
            $is_pay_fail = 1;
        }
        
    } else {
        // 실패시 처리
        
        if (function_exists('add_log')) {
            add_log(array('error'=>'fail2'), false, '_subscription_fail_');
        }
        
        $failure_reason = '결제에 성공했으나, DB 쓰기에 실패했습니다.('.$pay_round_no.'회차) 코드 : '.$pays['code'].' 이유 : '.$pays['message'];
        
        add_subscription_order_history($failure_reason, array(
            'hs_type' => 'subscription_pay',
            'od_id' => $od['od_id'],
            'mb_id' => $od['mb_id']
        ));
        
        include_once(G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php');
        include_once(G5_SUBSCRIPTION_PATH.'/mail/fail_pay.mail.php');
            
        // 연속으로 몇번 이상 실패시 해당 구독을 비활성화 해야 한다.
        $is_pay_fail = 1;
    }
    
    // 정기결제가 실패 되었다면, 3회 이상 실패시 
    if ($is_pay_fail && ($od['od_fail_count'] + 1) >= 3) {
        
        // 비활성화한다.
        sql_bind_update(
            $g5['g5_subscription_order_table'],
            array(
                'od_fail_count' => array('expression' => 'od_fail_count + 1'),
                'od_enable_status'=>0
            ),
            array('od_id'=>$od['od_id'])
        );
        
        $failure_reason = "3회 이상 정기결제가 실패되어서, 정기구독이 비활성화 되었습니다.";
        
        add_subscription_order_history($failure_reason, array(
            'hs_type' => 'subscription_pay',
            'od_id' => $od['od_id'],
            'mb_id' => $od['mb_id']
        ));
    }
}

exit;

$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++) {
    
    print_r2($row);

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
    
    // $pays = subscription_process_payment($row, $row['od_pg']);
    
    // print_r($pays);
    
    // // 정기결제가 성공이면
    // if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {
        
    //     $pay_round_no = (int) $row['od_pays_total'] + 1;
    //     $insert_id = subscription_order_pay($row, $pays, $pay_round_no);
        
    //     // 성공이면
    //     if ($insert_id) {
            
    //         $nextBillingDate = calculateNextBillingDate($row);
            
    //         $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET next_billing_date = '".$nextBillingDate."', last_billed_date = '".G5_TIME_YMDHIS."', od_pays_total = '".$pay_round_no."' WHERE od_id = '".$row['od_id']."'";
            
    //         sql_query($updateQuery);
            
    //         $od_name = $row['od_name'];
    //         $od_email = $row['od_email'];
            
    //         include_once(G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php');
    //         include_once(G5_SUBSCRIPTION_PATH.'/cron_ordermail2.inc.php');
            
    //     } else {
    //         // 실패시 처리
            
    //         if (function_exists('add_log')) {
    //             add_log(array('error'=>'fail1'), false, '_subscription_fail_');
    //         }
    //     }
        
    // } else {
    //     // 실패시 처리
        
    //     if (function_exists('add_log')) {
    //         add_log(array('error'=>'fail2'), false, '_subscription_fail_');
    //     }
    // }
}