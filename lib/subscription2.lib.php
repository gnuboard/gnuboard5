<?php
if (!defined('_GNUBOARD_')) exit;

function getWeeklyDeliveryDate($startDate, $weekInterval, $targetDayOfWeek = 0, $holidays = array()) {
    // $startDate: 최초 배송 시작일 (YYYY-MM-DD)
    // $weekInterval: 몇 주 후인지 (0: 이번 주, 1: 다음 주 등)
    // $targetDayOfWeek: 목표 요일 (0: 일요일, 1: 월요일, ..., 6: 토요일)
    // $holidays: 공휴일 배열
    
    // 기준 날짜의 타임스탬프
    $startTimestamp = strtotime($startDate);
    
    if ($targetDayOfWeek) {
        // 시작 날짜의 요일
        $startDayOfWeek = date('w', $startTimestamp);
        
        // 목표 요일까지의 날짜 차이 계산
        $daysToAdd = ($targetDayOfWeek - $startDayOfWeek + 7) % 7;
        if ($weekInterval > 0) {
            $daysToAdd += $weekInterval * 7; // 주 단위로 추가
        }
        
        // 목표 날짜 계산
        $scheduledDate = date('Y-m-d', strtotime("+$daysToAdd days", $startTimestamp));
    } else {
        // 기준 날짜 계산 (startDate에서 weekInterval만큼 후)
        $scheduledDate = date('Y-m-d', strtotime("+$weekInterval weeks", $startTimestamp));
    }
    
    // getBusinessDaysBefore를 사용해 연휴 전 영업일로 조정
    $adjustedDate = getBusinessDaysBefore($scheduledDate, 0, $holidays);
    
    return $adjustedDate;
}

function getMonthlyDeliveryDate($startDate, $monthInterval, $targetDay, $holidays = array()) {
    // $startDate: 최초 배송 시작일 (YYYY-MM-DD)
    // $monthInterval: 몇 달 후인지 (0: 이번 달, 1: 다음 달 등)
    // $targetDay: 목표 날짜 (1~31)
    // $holidays: 공휴일 배열
    
    // 입력 검증: $targetDay는 1~31 사이어야 함
    if ($targetDay < 1 || $targetDay > 31) {
        // throw new Exception("Target day must be between 1 and 31");
        trigger_error("Target day must be between 1 and 31", E_USER_ERROR);
        return false;
    }
    
    // 기준 날짜에서 월 간격 적용
    $baseDate = date('Y-m-01', strtotime("+$monthInterval months", strtotime($startDate)));
    
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
    
    // 조정된 날짜가 해당 달을 벗어나면 해당 달의 마지막 영업일로 고정
    /*
    $monthStart = date('Y-m-01', strtotime($baseDate));
    if (strtotime($adjustedDate) < strtotime($monthStart)) {
        $timestamp = strtotime(date('Y-m-t', strtotime($baseDate)));
        $maxIterations = 10;
        $iteration = 0;
        while ($iteration++ < $maxIterations) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !isset(array_flip($holidays)[$formattedDate])) {
                break;
            }
            $timestamp = strtotime('-1 day', $timestamp);
        }
        $adjustedDate = date('Y-m-d', $timestamp) . ' 09:00:01';
    }
    */
    
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
        $adjustedDate = date('Y-m-d', $timestamp) . ' 09:00:01';
    }

    return $adjustedDate;
}

function get_card_billkey($od) {
    global $g5;
    
    $result = sql_bind_select_fetch($g5['g5_subscription_mb_cardinfo_table'], '*', array('ci_id'=>$od['ci_id'], 'mb_id'=>$od['mb_id'], 'pg_service' => $od['od_pg']));
    
    return isset($result['card_billkey']) ? $result['card_billkey'] : '';
}

function is_use_subscription_item($it) {
    
    // defined('G5_USE_SUBSCRIPTION') && function_exists('get_subs_option') && get_subs_option('su_hope_date_use')
    // if (defined('G5_USE_SUBSCRIPTION') && function_exists('get_subs_option') && get_subs_option('su_hope_date_use') && isset($it['it_class_num']) && $it['it_class_num']) {
    // 정기결제를 사용하는 상품이면
    if (defined('G5_USE_SUBSCRIPTION') && get_subs_option('su_hope_date_use') && isset($it['it_class_num']) && $it['it_class_num']) {
        return true;
    }
    
    return 0;
}