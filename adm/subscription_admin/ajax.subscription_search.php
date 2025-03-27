<?php
$sub_menu = '600500';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$start = (isset($_REQUEST['start']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_REQUEST['start'])) ? $_REQUEST['start'] : '';
$end = (isset($_REQUEST['start']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_REQUEST['end'])) ? $_REQUEST['end'] : '';

/*
$year = (isset($_REQUEST['year']) && $_REQUEST['year']) ? (int) $_REQUEST['year'] : 0;
$month = (isset($_REQUEST['month']) && $_REQUEST['month']) ? (int) $_REQUEST['month'] : 0;
*/

if (!($start || $end)) {
    // 검색 못함
}

/*
$wheres = array(
    'next_billing_date' => array(
        'operator' => 'BETWEEN',
        'value' => array("$start 00:00:00", "$end 23:59:59")
    )
);
*/
$wheres = array(
    'next_billing_date' => array('BETWEEN' => array("$start 00:00:00", "$end 23:59:59"))
);
$selects = sql_bind_select_array($g5['g5_subscription_order_table'], '*', $wheres);

print_r2($selects);
exit;

$data = array();

foreach($selects as $row) {
    if (empty($row)) {
        continue;
    }
    
    /*
    $data[] = array(
            "title" => "팀 회의",
            "start" => "2025-03-25T10:00:00",
            "end" => "2025-03-25T11:30:00",
            "color" => "#3788d8"
        );
    */
    
    $data[] = array(
            "title" => "팀 회의",
            "start" => "2025-03-25T10:00:00",
            "end" => "2025-03-25T11:30:00",
            "color" => "#3788d8"
        );
}

/*
$data = [
    [
        "title" => "팀 회의",
        "start" => "2025-03-25T10:00:00",
        "end" => "2025-03-25T11:30:00",
        "color" => "#3788d8"
    ],
    [
        "title" => "점심 약속",
        "start" => "2025-03-26T12:00:00",
        "end" => "2025-03-26T13:00:00",
        "color" => "#28a745"
    ]
];
*/


header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
