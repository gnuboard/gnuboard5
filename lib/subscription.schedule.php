<?php
if (!defined('_GNUBOARD_')) exit;

function getWeeklyDeliveryDate($startDate, $weekInterval, $targetDayOfWeek = 0, $is_first=0)
{
    // $startDate: 최초 배송 시작일 (YYYY-MM-DD)
    // $weekInterval: 몇 주 후인지 (0: 이번 주, 1: 다음 주 등)
    // $targetDayOfWeek: 목표 요일 (0: 일요일, 1: 월요일, ..., 6: 토요일)

    // 기준 날짜의 타임스탬프
    $startTimestamp = strtotime($startDate);
    
    // 주에 요일이 조건에 있다면
    if ($targetDayOfWeek) {
        
        $startw = date('w', $startTimestamp);
        $lead_days = (int) get_subs_option('su_auto_payment_lead_days');

        if ($is_first && (($targetDayOfWeek - $startw) > $lead_days)) {
            $weekInterval = 0;
        }
        
        $baseWeekMonday = strtotime("last sunday +1 day", $startTimestamp); // 이번 주 월요일
        $targetWeekStart = strtotime("+{$weekInterval} week", $baseWeekMonday);

        // targetDayOfWeek는 0:일 ~ 6:토, 월요일 = 1 → 기준이 월요일이므로 -1 필요
        $daysToAdd = $targetDayOfWeek - 1;

        $targetTimestamp = strtotime("+{$daysToAdd} days", $targetWeekStart);
        $scheduledDate = date('Y-m-d', $targetTimestamp);
    } else {
        // 기준 날짜 계산 (startDate에서 weekInterval만큼 후)
        $scheduledDate = date('Y-m-d', strtotime("+$weekInterval weeks", $startTimestamp));
    }
    
    // 공휴일 조정
    $adjustedDate = getBusinessDaysBefore($scheduledDate, 0);
    
    return $adjustedDate;
    
}

function getMonthlyDeliveryDate($startDate, $monthInterval, $targetDay, $is_first=0)
{
    $holidays = array();
    
    // $startDate: 최초 배송 시작일 (YYYY-MM-DD)
    // $monthInterval: 몇 달 후인지 (0: 이번 달, 1: 다음 달 등)
    // $targetDay: 목표 날짜 (1~31)

    // 입력 검증: $targetDay는 1~31 사이어야 함
    if ($targetDay < 1 || $targetDay > 31) {
        // throw new Exception("Target day must be between 1 and 31");
        trigger_error("Target day must be between 1 and 31", E_USER_ERROR);
        return false;
    }
    
    $startTimestamp = strtotime($startDate);
    
    $start_day = date('d', $startTimestamp);
    
    $lead_days = (int) get_subs_option('su_auto_payment_lead_days');
    
    if ($is_first && (($targetDay - $start_day) > $lead_days)) {
        $monthInterval = 0;
    }
        
    // 기준 날짜에서 월 간격 적용
    $baseDate = date('Y-m-01', strtotime("+$monthInterval months", $startTimestamp));

    // 목표 날짜 설정 (해당 달의 $targetDay)
    $scheduledDate = $baseDate;
    $month = date('m', strtotime($baseDate));
    $year = date('Y', strtotime($baseDate));

    // 해당 달의 마지막 날 확인
    $lastDayOfMonth = date('t', strtotime($baseDate));
    $adjustedTargetDay = min($targetDay, $lastDayOfMonth); // 31일이 없는 달 조정

    $scheduledDate = sprintf('%d-%02d-%02d', $year, $month, $adjustedTargetDay);

    // getBusinessDaysBefore를 사용해 연휴 전 영업일로 조정
    $adjustedDate = getBusinessDaysBefore($scheduledDate, 0, $holidays);

    $monthStart = date('Y-m-01', strtotime($baseDate));
    if (strtotime($adjustedDate) < strtotime($monthStart)) {
        $timestamp = strtotime(date('Y-m-t', strtotime($baseDate)));
        $maxIterations = 10;
        $iteration = 0;

        while ($iteration++ < $maxIterations) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);

            // PHP 5.2.17 호환성을 위해 array_flip 후 isset 대신 in_array 사용
            $holidaysFlipped = array_flip($holidays);
            $isHoliday = in_array($formattedDate, array_keys($holidaysFlipped));

            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !$isHoliday) {
                break;
            }
            $timestamp = strtotime('-1 day', $timestamp);
        }
        $adjustedDate = date('Y-m-d', $timestamp) . ' ' . SUBSCRIPTION_DEFAULT_TIME_SUFFIX;
    }

    return $adjustedDate;
}

function get_subscription_order_status($od)
{
    $status = isset($od['od_enable_status']) ? (int) $od['od_enable_status'] : '';

    if ($status === 1) {
        return '구독중';
    } else {
        return '종료됨';
    }

    return '';
}

function get_card_billkey($od)
{
    global $g5;

    $sql = "SELECT * 
        FROM {$g5['g5_subscription_mb_cardinfo_table']} 
        WHERE ci_id = '" . $od['ci_id'] . "' 
        AND mb_id = '" . $od['mb_id'] . "' 
        AND pg_service = '" . $od['od_pg'] . "'";
    $row = sql_fetch($sql);

    return isset($row['card_billkey']) ? $row['card_billkey'] : '';
}

function get_customer_card_info($od)
{
    global $g5;

    $sql = "SELECT * 
            FROM {$g5['g5_subscription_mb_cardinfo_table']} 
            WHERE mb_id = '" . $od['mb_id'] . "' 
            AND ci_id = '" . $od['ci_id'] . "'";

    return sql_fetch($sql);
}

function subs_date_ymd($str)
{
    return date('Y-m-d', strtotime($str));
}

function is_use_subscription_item($it)
{

    if (defined('G5_USE_SUBSCRIPTION') && isset($it['it_class_num']) && $it['it_class_num']) {
        return 1;
    }

    return 0;
}

function get_subscription_total_amount($od)
{
    global $g5;

    $order = $od;

    if (!$order) {
        return false; // 주문이 존재하지 않으면 false 반환
    }

    // 장바구니 내 모든 상품 총액 계산
    $cart_price_result = sql_fetch("SELECT SUM(ct_price * ct_qty) AS cart_price 
                                    FROM {$g5['g5_subscription_cart_table']} 
                                    WHERE od_id = '{$od['od_id']}'");

    $cart_price = (int)$cart_price_result['cart_price'];

    // 각 항목 합산
    $total_amount = 0;
    $total_amount += (int)$order['od_send_cost'];     // 기본 배송비
    $total_amount += (int)$order['od_send_cost2'];    // 추가 배송비
    if (isset($order['od_temp_point']) && $order['od_temp_point']) {
        $total_amount -= (int)$order['od_temp_point'];    // 포인트 결제
    }
    if (isset($order['od_coupon']) && $order['od_coupon']) {
        $total_amount -= (int)$order['od_coupon'];        // 쿠폰 할인
    }
    $total_amount += $cart_price;                     // 상품 가격 합산

    // 여기에 추가 비용 항목이 있다면 더해줄 수 있음
    // 예: $total_amount += (int)$order['od_other_fee'];

    return array('cart_price' => $cart_price, 'total_amount' => $total_amount);
}

// 연도 유효성 검사 함수
function isValidYear($value)
{
    // 문자열이 4자리 숫자인지 확인
    if (!preg_match('/^\d{4}$/', $value)) {
        return false;
    }

    // 숫자로 변환 후 범위 확인 (예: 1900~9999)
    $year = (int)$value;
    return $year >= 2025 && $year <= 9999;
}

function get_subscription_holidays($get_short = 0)
{

    $saved_holidays = get_subs_option('api_holiday_settings');

    $saved_holidays_array = array();

    if ($saved_holidays) {
        $tmps = array();

        $saved_holidays_array = subscription_serial_decode($saved_holidays);
    }

    if ($get_short) {
        foreach ($saved_holidays_array as $k => $v) {

            if (empty($v)) {
                continue;
            }

            foreach ($v as $k2 => $v2) {

                if (empty($v2)) {
                    continue;
                }

                $saved_holidays_array[$k][$k2]['short'] = utf8_strcut($v2['name'], 10, '..');
            }
        }
    }

    return $saved_holidays_array;
}

function get_subscription_business_days()
{

    $holidays = get_subscription_holidays();
    
    $su_holiday_settings = get_subs_option('su_holiday_settings');
    
    $result = $holidays;

    if ($su_holiday_settings) {
        
        $new_entries = unserialize(base64_decode($su_holiday_settings));
        
        foreach ($new_entries as $entry) {
            if ($entry['type'] === 'h') {
                $year = substr($entry['date'], 0, 4);
                $result[$year][] = [
                    'dateymd' => $entry['date'],
                    'name' => $entry['title']
                ];
            }
        }

        foreach ($new_entries as $entry) {
            if ($entry['type'] === 'w') {
                $year = substr($entry['date'], 0, 4);
                
                if (!isset($result[$year]) || !is_array($result[$year])) {
                    $result[$year] = array();
                }
                
                $result[$year] = array_filter($result[$year], function ($holiday) use ($entry) {
                    return $holiday['dateymd'] !== $entry['date'];
                });
                // Reindex the array to maintain consecutive keys
                $result[$year] = array_values($result[$year]);
            }
        }
    }

    $dates = array();
    foreach ($result as $year => $holiday_list) {
        foreach ($holiday_list as $holiday) {
            $dates[] = $holiday['dateymd'];
        }
    }

    return $dates;
}

function get_subscription_exception_dates()
{

    $dates = array();

    $new_entries = unserialize(base64_decode(get_subs_option('su_holiday_settings')));

    $dates = array();
    foreach ((array) $new_entries as $item) {
        if (isset($item['type']) && $item['type'] === 'w') {
            $dates[] = $item['date'];
        }
    }

    return $dates;
}

// 기존 배송 날짜
function get_subscription_delivery_date($od, $date_format = '')
{

    $delivery_date = (isset($od['next_delivery_date']) && $od['next_delivery_date']) ? $od['next_delivery_date'] : $od['od_hope_date'];

    if ($date_format) {
        return date($date_format, strtotime($delivery_date));
    }

    return $delivery_date;
}

// 공휴일 설정 함수
function subscription_setting_holidays($datetime = '')
{
    global $g5;
    
    $serviceKey = get_subs_option('api_holiday_data_go_key');
    
    if (!$serviceKey) {
        return;
    }

    $storedDate = get_subs_option('api_holiday_last');
    $saved_holidays = get_subs_option('api_holiday_settings');
    $saved_holidays_array = $saved_holidays ? subscription_serial_decode($saved_holidays) : array();
    
    $solYear = $datetime ? date('Y', $datetime) : date('Y', G5_SERVER_TIME);

    // 해당년과 다음년도 조회
    $solYears = array($solYear);

    if ((int)$solYear + 1 > date('Y', G5_SERVER_TIME)) {
        $solYears[] = (int)$solYear + 1;
    }
    
    $year_keys = $solYears ? array_values($solYears) : array();
    
    $check_key_exists = false;
    
    if ($year_keys) {
        foreach($year_keys as $year_key) {
            if (isset($saved_holidays_array[$year_key]) && $saved_holidays_array[$year_key]) {
                $check_key_exists = true;
            }
        }
    }
    
    if ($check_key_exists && $storedDate && (G5_SERVER_TIME - strtotime($storedDate) < 86400)) {
        $check_key_exists = false;
    }

    if (!$saved_holidays_array) {
        $check_key_exists = true;
    }
    
    if (!$check_key_exists) {
        return;
    }
    
    // 공휴일을 받아올수 있는 공공데이터포털 > 한국천문연구원_특일 정보 API
    $url = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';

    $holys = array();

    foreach ($solYears as $solYear) {

        if (!isValidYear($solYear)) {
            continue;
        }

        $queryParams = '?' . urlencode('serviceKey') . $serviceKey;
        $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('100');
        $queryParams .= '&' . urlencode('solYear') . '=' . urlencode($solYear);
        $queryParams .= '&' . urlencode('_type') . '=' . urlencode('json');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);    // 최대 실행 시간
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        $sols = array();

        if (isset($data['response']['body']['items']['item'])) {
            $holidays = $data['response']['body']['items']['item'];

            foreach ($holidays as $holiday) {

                $sols[] = array(
                    'dateymd' => date('Y-m-d', strtotime($holiday['locdate'])),
                    'name' => $holiday['dateName']
                );
            }
        }

        $holys[$solYear] = $sols;
    }

    if ($saved_holidays && $saved_holidays_array) {
        $tmps = array();
        
        foreach ($saved_holidays_array as $k => $v) {
            if (!isValidYear($k)) {
                continue;
            }

            $tmps[$k] = $v;
        }

        $result = array();
        foreach (array($holys, $tmps) as $array) {
            foreach ($array as $year => $holidays) {
                if (!isset($result[$year])) {
                    $result[$year] = array();
                }

                if (isset($holys[$year])) {
                    $result[$year] = $holys[$year];
                } else {
                    $result[$year] = $tmps[$year];
                }
            }
        }
        $holys = $result;
    }

    if ($holys) {

        $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET api_holiday_settings = '" . subscription_serial_encode($holys) . "', api_holiday_last = '" . G5_TIME_YMDHIS . "'";
        sql_query($sql, false);
    }
}

function calculateRecurringPaymentDetails($od)
{

    if (empty($od['od_subscription_selected_data']) || empty($od['od_subscription_selected_number'])) {
        return array(
            'deliverys' => '',
            'usage_count' => 0,
            'usages' => '',
            'is_end_subscription' => 1,
            'current_cycle' => '',
            'current_cycle_str' => ''
        ); // 필요한 데이터가 없으면 빈값으로 리턴
    }

    $opt = subscription_serial_decode($od['od_subscription_selected_data']);
    $use = subscription_serial_decode($od['od_subscription_selected_number']);

    $opt_print = (isset($opt['opt_print']) && $opt['opt_print']) ? $opt['opt_print'] : $opt['opt_input'] . ' 일마다';

    if (!$opt['opt_print']) {

        if (!$opt['opt_input']) $opt['opt_input'] = 1;

        if ($opt['opt_date_format'] === 'week') {

            $opt_print = (int) $opt['opt_input'] . '주에 ';

            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                $opt_print .= get_subscriptionDayOfWeek($opt['opt_etc']);
            } else {
                $opt_print .= '한 번';
            }
        } else if ($opt['opt_date_format'] === 'month') {

            $opt_print = (int) $opt['opt_input'] . '달에 ';

            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                $opt_print .= (int) $opt['opt_etc'] . '일';
            } else {
                $opt_print .= '한 번';
            }
        } else if ($opt['opt_date_format'] === 'year') {
            $opt_print = '1년에 한 번';
        }
    }

    if ($opt['opt_input'] || $opt['opt_date_format']) {
        $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
    }

    // 이용횟수 
    $usage_count = isset($use['use_input']) ? (int) $use['use_input'] : 0;

    $use_print = (isset($use['use_print']) && $use['use_print']) ? $use['use_print'] : $use['use_input'] . '회';
    $use_print = str_replace("{입력}", $usage_count, $use_print);

    $is_end_subscription = ((int) $od['od_pays_total'] >= $usage_count) ? 1 : 0;

    // 구독이 종료되었다면
    if (!$od['od_enable_status']) {
        $is_end_subscription = 1;
    }

    $current_cycle = $is_end_subscription ? (int) $od['od_pays_total'] : (int) $od['od_pays_total'] + 1;

    return array(
        'deliverys' => $opt_print,          // 배송주기
        'usage_count' => $usage_count,      // 이용횟수 (숫자만)
        'usages' => $use_print,             // 이용횟수 (숫자 + 글자)
        'is_end_subscription' => $is_end_subscription,    // 정기구독이 진행중이면 0, 끝났으면 1
        'current_cycle' => $current_cycle,  // 현재횟차
        'current_cycle_str' => $current_cycle . '회' . ($is_end_subscription ? ' (종료됨)' : ' (진행중)')  // 현재횟차 (숫자 + 글자)
    );
}

// 결제일을 구하는 함수
function getNextPaymentDate($baseDate, $daysToSubtract = 0, $od=array()) {
    
    $calculatedDate = strtotime("$baseDate -$daysToSubtract days");
    
    // 영업일을 적용하려면 hook 을 통해서
    return run_replace('getNextPaymentDate', date('Y-m-d', $calculatedDate).' '.SUBSCRIPTION_DEFAULT_TIME_SUFFIX, $calculatedDate, $baseDate, $daysToSubtract, $od);
}

function get_weekday($dateString)
{

    $timestamp = strtotime($dateString);
    $dayOfWeek = date('w', $timestamp);

    $days = array('일', '월', '화', '수', '목', '금', '토');

    return $days[$dayOfWeek];
}

function updateSubscriptionItemIfChanged($od)
{
    // 1. 현재 상품 정보와 기존 주문의 상품정보 비교
    // 2. 가격/옵션이 달라졌으면 주문 데이터 업데이트

    global $g5;

    $is_same_cart = before_check_subscription_cart_price($od['od_id'], false, true, true, true);

    // 장바구니 금액이 변동되었거나, 재고가 없다면
    if (!$is_same_cart) {

        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
                      COUNT(distinct it_id) as cart_count
                    from {$g5['g5_subscription_cart_table']} where od_id = '{$od['od_id']}' and ct_select = '1' ";

        $row = sql_fetch($sql);

        $tot_ct_price = (int) $row['od_price'];
        $cart_count = (int) $row['cart_count'];
        $tot_od_price = $tot_ct_price;

        $send_cost = (int) $od['od_send_cost'];
        $send_cost2 = (int) $od['od_send_cost2'];
        $tot_sc_cp_price = (int) $od['od_send_coupon'];

        // 장바구니 금액이 변동되어 결제할 가격을 변경합니다.
        $od_receipt_price = $tot_od_price + $send_cost + $send_cost2 - $tot_sc_cp_price;

        $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} SET od_cart_price = '$tot_ct_price', od_cart_count = '$cart_count', od_receipt_price = '$od_receipt_price' WHERE od_id = '{$od['od_id']}'";

        sql_query($updateQuery);

        // 변경된 내역이 있으면 1 리턴
        return 1;
    }

    // 변경된 내역이 없으면 0 리턴
    return 0;
}

// 정기결제 재고 감소 및 복원 이중 처리 방지
function subscription_pay_process_stock($pay_id, $mode = 'decrease')
{
    global $g5;

    $pay_id = trim($pay_id);
    $mode = strtolower($mode);

    if (!in_array($mode, array('decrease', 'increase'))) {
        return false;
    }

    // 상태 전이 정의
    if ($mode === 'decrease') {
        // none → used | restored → reused
        $where = "pb_stock_status IN ('none', 'restored')";
    } else {
        // used → restored | reused → restored
        $where = "pb_stock_status IN ('used', 'reused')";
    }

    $sql = "SELECT * FROM {$g5['g5_subscription_pay_basket_table']}
            WHERE pay_id = '{$pay_id}' AND {$where}";

    $result = sql_query($sql);

    while ($row = sql_fetch_array($result)) {
        $it_id  = $row['it_id'];
        $pb_qty = (int)$row['pb_qty'];
        $io_id  = trim($row['io_id']);
        $pb_id  = $row['pb_id'];
        $io_type = $row['io_type'];
        $prev_status = $row['pb_stock_status'];

        $operator = ($mode === 'decrease') ? '-' : '+';

        if ($io_id) {
            // 옵션 재고 처리
            $sql2 = "UPDATE {$g5['g5_shop_item_option_table']}
                        SET io_stock_qty = io_stock_qty {$operator} {$pb_qty}
                     WHERE it_id = '{$it_id}' AND io_id = '{$io_id}' AND io_type = '{$io_type}' ";
        } else {
            // 일반 재고 처리
            $sql2 = "UPDATE {$g5['g5_shop_item_table']}
                        SET it_stock_qty = it_stock_qty {$operator} {$pb_qty}
                     WHERE it_id = '{$it_id}'";
        }
        
        sql_query($sql2);

        // 상태 전이
        /* php 버전이 낮으면 match문 지원 안함
        $new_status = match (true) {
            $mode === 'decrease' && $prev_status === 'none'     => 'used',
            $mode === 'decrease' && $prev_status === 'restored' => 'reused',
            $mode === 'increase'                                 => 'restored',
            default                                              => $prev_status,
        };
        */
        
        // 상태 전이
        if ($mode === 'decrease' && $prev_status === 'none') {
            $new_status = 'used';
        } elseif ($mode === 'decrease' && $prev_status === 'restored') {
            $new_status = 'reused';
        } elseif ($mode === 'increase') {
            $new_status = 'restored';
        } else {
            $new_status = $prev_status;
        }

        sql_query("UPDATE {$g5['g5_subscription_pay_basket_table']}
                      SET pb_stock_status = '{$new_status}'
                   WHERE pb_id = '{$pb_id}'");

        // (선택) 로그 기록 (로그는 다음에)
        /*
        sql_query("INSERT INTO g5_shop_stock_log
                    SET od_id = '{$pay_id}',
                        is_subscription = 1,
                        pb_id = '{$pb_id}',
                        it_id = '{$it_id}',
                        io_id = " . ($io_id ? "'{$io_id}'" : "NULL") . ",
                        qty_change = " . ($mode === 'decrease' ? -$pb_qty : $pb_qty) . ",
                        action_type = '{$mode}',
                        comment = '상태: {$prev_status} → {$new_status}',
                        created_at = NOW()");
        */
    }

    return true;
}

function get_subscription_schedule($od, $delivery_count)
{
    global $g5;

    $od_id = $od['od_id'];
    $lead_days = (int) get_subs_option('su_auto_payment_lead_days');
    $current_usage = (int) $od['od_pays_total'];
    $n_timestamp = !is_null_date($od['next_billing_date']) ? strtotime($od['next_billing_date']) : strtotime($od['od_time']);
    // $deliveryDate = '';
    $delivery_date = '';
    $schedule = array();

    for ($i = 1; $i <= $delivery_count; $i++) {
        $entry = array(
            'round' => $i,
            'is_current' => 0,
            'payment_status' => '',
            'payment_date' => '',
            'delivery_title' => '',
            'delivery_date' => ''
        );

        if ($i === ($current_usage + 1)) {
            $entry['is_current'] = 1;
        }

        // 결제 완료 회차
        if ($i <= $current_usage) {
            $pay = sql_fetch("SELECT * FROM {$g5['g5_subscription_pay_table']} WHERE od_id = '$od_id' AND py_round_no = '$i'");

            if (!empty($pay['pay_id'])) {
                $entry['payment_status'] = 'paid';
                $entry['payment_date'] = subs_date_ymd($pay['py_receipt_time']);

                if (!is_null_date($pay['py_invoice_time'])) {
                    $entry['delivery_title'] = '배송일';
                    $entry['delivery_date'] = subs_date_ymd($pay['py_invoice_time']);
                } else {
                    $receipt_ts = strtotime($pay['py_receipt_time']);
                    $delivery_ts = $lead_days ? strtotime("+$lead_days days", $receipt_ts) : $receipt_ts;
                    $delivery_date = (isset($pay['next_delivery_date']) && !is_null_date($pay['next_delivery_date'])) ? $pay['next_delivery_date'] : getBusinessDaysnext(date('Y-m-d H:i:s', $delivery_ts));

                    $entry['delivery_title'] = '배송 예정일';
                    $entry['delivery_date'] = subs_date_ymd($delivery_date);
                }
            }
        }

        // 예정 회차
        else {
            $entry['payment_status'] = 'scheduled';

            if ($i === ($current_usage + 1) && !is_null_date($od['next_billing_date'])) {
                $payment_date = $od['next_billing_date'];
                $payment_ts = strtotime($payment_date);
                $delivery_ts = $lead_days ? strtotime("+$lead_days days", $payment_ts) : $payment_ts;

                /*
                if (isset($od['next_delivery_date']) && !is_null_date($od['next_delivery_date'])) {
                    $delivery_date = $od['next_delivery_date'];
                } else {
                    $delivery_date = getBusinessDaysnext(date('Y-m-d H:i:s', $delivery_ts));
                }
                */

                $delivery_date = getBusinessDaysnext(date('Y-m-d H:i:s', $delivery_ts));

                if (isset($od['next_delivery_date']) && !is_null_date($od['next_delivery_date'])) {
                    $delivery_date = $od['next_delivery_date'];
                }
            } else {
                // $delivery_ts = $deliveryDate ? strtotime($deliveryDate) : $n_timestamp;
                $delivery_ts = $delivery_date ? strtotime($delivery_date) : $n_timestamp;
                // $delivery_ts = $lead_days ? strtotime("+$lead_days days", $delivery_ts) : $delivery_ts;

                $od['next_delivery_date'] = date('Y-m-d', $delivery_ts);

                $delivery_date = calculateNextDeliveryDate($od);
                $delivery_ts = strtotime($delivery_date);

                $payment_ts = $lead_days ? strtotime("-$lead_days days", $delivery_ts) : $delivery_ts;
                $payment_date = date('Y-m-d', $payment_ts);
            }

            $entry['payment_date'] = subs_date_ymd($payment_date);
            $entry['delivery_title'] = '배송 예정일';
            $entry['delivery_date'] = subs_date_ymd($delivery_date);
        }

        $schedule[] = $entry;
    }

    return $schedule;
}

function getIntervalBasedNextDate($timestamp, $od_subscription_selected_data, $od_subscription_number, $is_first=0) {
    
    $od_subscription_date_format = isset($od['od_subscription_date_format']) ? $od['od_subscription_date_format'] : null;
    $od_subscription_number = isset($od['od_subscription_number']) ? $od['od_subscription_number'] : null;

    if (isset($od_subscription_selected_data['opt_date_format']) && $od_subscription_selected_data['opt_date_format']) {
        $od_subscription_date_format = $od_subscription_selected_data['opt_date_format'];
    }

    if (isset($od_subscription_selected_data['opt_input']) && $od_subscription_selected_data['opt_input']) {
        $od_subscription_number = (int) $od_subscription_selected_data['opt_input'];
    }
    
    $interval = $od_subscription_date_format ? $od_subscription_date_format : 'day';
    $plus = abs($od_subscription_number);
    
    $startDate = date('Y-m-d', $timestamp); // 기준 날짜
    
    switch ($interval) {
        case 'day':
            $timestamp = strtotime('+' . $plus . ' day', $timestamp);
            // 일의 경우 무조건 영업일 다음날로 지정
            $nextdate = getBusinessDaysnext(date('Y-m-d H:i:s', $timestamp));
            break;
        case 'week':
            // $timestamp = strtotime('+'.$plus.' week', $timestamp);
            // 특정 요일 설정 (0: 일요일, 1: 월요일, ..., 6: 토요일)

            $otp_etc = isset($od_subscription_selected_data['opt_etc']) ? $od_subscription_selected_data['opt_etc'] : '';

            // 요일 매핑
            $dayMap = array(
                'sun' => 0,
                'mon' => 1,
                'tue' => 2,
                'wed' => 3,
                'thu' => 4,
                'fri' => 5,
                'sat' => 6
            );

            // etc_data에서 목표 요일 가져오기 (없으면 기본값으로 월요일(1) 사용)
            $targetDayOfWeek = ($otp_etc && isset($dayMap[$otp_etc])) ? $dayMap[$otp_etc] : 0;

            $nextdate = getWeeklyDeliveryDate($startDate, $plus, $targetDayOfWeek, $is_first);
            break;
        case 'month':
            // 특정 날짜 설정 (1~31)
            $targetDay = isset($od_subscription_selected_data['opt_etc'])
                ? (int) $od_subscription_selected_data['opt_etc']
                : 0;
            $nextdate = getMonthlyDeliveryDate($startDate, $plus, $targetDay, $is_first);
            $is_check_before = true;
            break;
        case 'year':
            $timestamp = strtotime("+$plus year", $timestamp);
            $nextdate = getBusinessDaysBefore(date('Y-m-d H:i:s', $timestamp), 0);
            break;
        default:
            throw new Exception("Unknown billing interval: $interval");
    }
    
    return $nextdate;
}
