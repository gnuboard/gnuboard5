<?php
include_once('./_common.php');

$t = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';

if (! $t) {
    die('');
}

$cron_token = subscription_cron_token();

if ($t !== $cron_token) {
    die('토큰값이 올바르지 않습니다.');
}

$is_db_success = true;

if ($is_db_success) {
    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET su_cron_updatetime = '" . G5_TIME_YMDHIS . "' ";
    sql_query($sql);
}

// CRON 야간 시간대 제외이면 
if (get_subs_option('cron_night_block')) {
    $now = G5_SERVER_TIME;

    // 오늘 날짜 기준으로 기준 시간 설정
    $today = date('Y-m-d');
    $startBlock = strtotime($today . ' 23:00'); // 오늘 밤 11시
    $endBlock = strtotime($today . ' 09:00');   // 오늘 오전 9시

    // 만약 지금이 00:00~09:00이라면, $endBlock을 다음날로 이동
    if ($now < $endBlock) {
        $startBlock = strtotime('yesterday 21:00');
        $endBlock = strtotime($today . ' 09:00');
    }

    // 크론 작업 실행 여부 판단
    if ($now >= $startBlock && $now < $endBlock) {
        die('크론 작업 시간 아님 - 실행 안함');
    }
}

// 락 파일 경로
$lock_file = G5_DATA_PATH . '/cache/subscription_cron.lock';
$lock_timeout = 300; // 락 타임아웃 (5분, 초 단위)

// 락 파일 체크 및 생성
if (file_exists($lock_file)) {
    // 락 파일이 존재하면 생성 시간 확인
    $lock_time = filemtime($lock_file);
    if ((time() - $lock_time) < $lock_timeout) {
        // 락 파일이 타임아웃 시간 내에 존재하면 종료
        die('CRON 작업이 이미 실행 중입니다.');
    } else {
        // 락 파일이 오래된 경우 삭제
        @unlink($lock_file);
    }
}

// 락 파일 생성
file_put_contents($lock_file, time());

subscription_setting_holidays();

// 현재 설정중인 PG만 결제하려면
$sql = "SELECT * 
        FROM {$g5['g5_subscription_order_table']} 
        WHERE od_enable_status = '1' 
        AND next_billing_date <= '" . G5_TIME_YMDHIS . "' 
        AND od_pg = '" . get_subs_option('su_pg_service') . "' 
        LIMIT 200";
        
$results = sql_query($sql);
$result_row = sql_result_array($results);

foreach ($result_row as $od) {
    
    if (empty($od)) {
        continue;
    }
    
    // 장바구니 금액 변동 체크
    $is_od_update = updateSubscriptionItemIfChanged($od);

    if ($is_od_update) {
        // 주문정보가 변경된 경우 다시 값을 가져온다.
        $od = get_subscription_order($od['od_id']);
    }
    
    $row = $od;
    
    $sql = "SELECT count(*) as cnt FROM {$g5['g5_subscription_cart_table']} WHERE od_id = '" . $od['od_id'] . "' AND ct_select = 1 ";
    $select_items = sql_fetch($sql);
    
    $select_item_count = (isset($select_items['cnt']) && $select_items['cnt']) ? (int) $select_items['cnt'] : 0;
    
    // 재고가 없거나, 결제될 금액이 없다면
    if ($od['od_receipt_price'] <= 0 || !$od['od_cart_count'] || !$select_items['cnt']) {
        // 비활성화한다.
        $sql = "UPDATE {$g5['g5_subscription_order_table']} 
                SET od_enable_status = '0' 
                WHERE od_id = '" . $od['od_id'] . "'";
                
        // sql_query($sql);
        
        add_subscription_order_history('결제금액이 0 이므로, 정기구독이 해지 되었습니다.', array(
            'hs_type' => 'subscription_disable_order',
            'od_id' => $od['od_id'],
            'mb_id' => $od['mb_id']
        ));
        
        continue;
    }
    
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    // 결제할 카드 빌링키 정보
    $billingKey = get_card_billkey($od);
    
    // 이용횟수
    $od_number_of_uses = isset($od_subscription_selected_number['use_input']) ? $od_subscription_selected_number['use_input'] : 0;

    // 만약에 해당 주문의 이용횟수가 있어서 이용횟수 기간이 지났다면, 또는 카드 빌링키가 없다면
    if (($od_number_of_uses && $od['od_pays_total'] >= $od_number_of_uses) || ! $billingKey) {

        // 비활성화한다.
        $sql = "UPDATE {$g5['g5_subscription_order_table']} 
                SET od_enable_status = '0' 
                WHERE od_id = '" . $od['od_id'] . "'";
        sql_query($sql);
        
        if (! $billingKey) {
            add_subscription_order_history('결제할 카드정보가 없어서 비활성화 되었습니다.', array(
                'hs_type' => 'subscription_disable_order',
                'od_id' => $od['od_id'],
                'mb_id' => $od['mb_id']
            ));
        } else {
            add_subscription_order_history($od_number_of_uses . ' 회가 지나서 비활성화 되었습니다', array(
                'hs_type' => 'subscription_disable_order',
                'od_id' => $od['od_id'],
                'mb_id' => $od['mb_id']
            ));
        }

        continue;
    }

    $sql = "SELECT COUNT(*) AS total 
            FROM {$g5['g5_subscription_pay_table']} 
            WHERE od_id = '" . $od['od_id'] . "' 
            AND DATE(py_time) = '" . date('Y-m-d', G5_SERVER_TIME) . "'";
            
    $today_exists = sql_fetch($sql);

    if (isset($today_exists['total']) && $today_exists['total']) {
        // 오늘 1회 이상 결제가 되었다면 결제하지 않는다. 1일당 1회만 결제되게 한다.
        continue;
    }

    $pays = subscription_process_payment($od, $od['od_pg']);

    $od_name = $od['od_name'];
    $od_email = $od['od_email'];
    $od_id = $od['od_id'];
    $is_pay_fail = 0;
    $pay_round_no = (int) $od['od_pays_total'] + 1;     // 다음 회차
        
    // 정기결제가 성공이면
    if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {

        $insert_id = subscription_order_pay($od, $pays['response'], $pay_round_no);

        // 성공이면
        if ($insert_id) {
            
            $pay_id = $insert_id;
            $nextBillingDate = calculateNextBillingDate($od);

            $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} 
                    SET next_billing_date = '$nextBillingDate', 
                        next_delivery_date = '". calculateNextDeliveryDate($od) ."',
                        last_billed_date = '" . G5_TIME_YMDHIS . "', 
                        od_pays_total = '$pay_round_no', 
                        od_fail_count = '0' 
                    WHERE od_id = '" . $od['od_id'] . "'";
            $result = sql_query($updateQuery);

            add_subscription_order_history('정기구독 ' . $pay_round_no . '회차 결제에 성공했습니다.', array(
                'hs_type' => 'subscription_order_success',
                'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
                'od_id' => $od_id,
                'mb_id' => $member['mb_id']
            ));
            
            $pay = get_subscription_pay($pay_id);
            
            // 정기구독정보의 배송주기와 이용횟수 등을 가져옴
            $crp = calculateRecurringPaymentDetails($od);
            
            // 배송주기
            $od_deliverys = $crp['deliverys'];
            // 신청한 총 이용횟수 (숫자 + 글자)
            $od_usages = $crp['usages'];
            // 신청한 총 이용횟수 (숫자만)
            $od_usage_count = $crp['usage_count'];
            // 정기구독이 진행중이명 0, 끝났으면 1
            $is_end_subscription = $crp['is_end_subscription'];
            // 현재횟차
            $current_cycle = $crp['current_cycle'];
            // 현재횟차 (숫자 + 글자)
            $current_cycle_str = $crp['current_cycle_str'];
    
            $od_id = $pay['od_id'];
            $py_send_cost = $pay['py_send_cost'];
            $py_send_cost2 = $pay['py_send_cost2'];
            
            include_once(G5_SUBSCRIPTION_PATH . '/subscription_pay_mail1.inc.php');
            include_once(G5_SUBSCRIPTION_PATH . '/subscription_pay_mail2.inc.php');
        } else {
            // DB 실패시 처리

            $failure_reason = '(크론) 결제에 성공했으나, DB 쓰기에 실패했습니다.(' . $pay_round_no . '회차) ';

            add_subscription_order_history($failure_reason, array(
                'hs_type' => 'subscription_pay_db_fail',
                'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
                'od_id' => $od['od_id'],
                'mb_id' => $od['mb_id']
            ));

            include_once(G5_SUBSCRIPTION_PATH . '/ordermail1.inc.php');
            include_once(G5_SUBSCRIPTION_PATH . '/mail/fail_db.mail.php');

            // 연속으로 몇번 이상 실패시 해당 구독을 비활성화 해야 한다.
            $is_pay_fail = 1;
        }
    } else {
        // 결제 실패시 처리

        $failure_reason = '결제가 실패 되었습니다. (' . $pay_round_no . '회차) 코드 : ' . $pays['code'] . ' 이유 : ' . $pays['message'];

        add_subscription_order_history($failure_reason, array(
            'hs_type' => 'subscription_pay_pg_fail',
            'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
            'od_id' => $od['od_id'],
            'mb_id' => $od['mb_id']
        ));
        
        include_once(G5_SUBSCRIPTION_PATH . '/ordermail1.inc.php');
        include_once(G5_SUBSCRIPTION_PATH . '/mail/fail_pay.mail.php');

        // 연속으로 몇번 이상 실패시 해당 구독을 비활성화 해야 한다.
        $is_pay_fail = 1;
    }

    // 정기결제가 실패 되었다면, 3회 이상 실패시 
    if ($is_pay_fail) {

        $add_updates = '';

        // 3회 이상 실패시 해당 구독주문을 비활성화 한다.
        if (($od['od_fail_count'] + 1) >= 3) {
            $add_updates = ', od_enable_status = 0 ';

            $failure_reason = "3회 이상 정기결제가 실패되어서, 정기구독이 비활성화 되었습니다.";

            add_subscription_order_history($failure_reason, array(
                'hs_type' => 'subscription_disable_order',
                'od_id' => $od['od_id'],
                'mb_id' => $od['mb_id']
            ));
        }

        $sql = "UPDATE {$g5['g5_subscription_order_table']} 
                SET od_fail_count = od_fail_count + 1
                " . $add_updates . "
                WHERE od_id = '" . $od['od_id'] . "'";
        sql_query($sql);

    }
}

// 작업 완료 후 락 파일 삭제
@unlink($lock_file);
