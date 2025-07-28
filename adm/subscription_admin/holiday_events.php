<?php
$sub_menu = '600510';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'w');

header('Content-Type: application/json; charset=utf-8');

// g5_config 테이블에서 holiday 데이터 가져오기
$data = unserialize(base64_decode(get_subs_option('su_holiday_settings')));
$data = is_array($data) ? $data : array();

$events = array();

foreach ($data as $row) {
    
    if (empty($row)) {
        continue;
    }
    
    $events[] = array(
        'id' => $row['id'],
        'title' => ($row['type'] === 'w' ? '[영업일] ' : '[휴무일]') . $row['title'],
        'start' => $row['date'],
        'allDay' => true,
        'classNames' => array($row['type']) // fullcalendar에서 스타일 구분 가능
    );
}

if ($events) {
    // 날짜 기준으로 정렬
    usort($events, function($a, $b) {
        return strcmp($a['start'], $b['start']);
    });
}

echo json_encode($events);