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
$sql = "SELECT * FROM {$g5['g5_subscription_order_table']} 
        WHERE next_billing_date BETWEEN '" . sql_real_escape_string($start) . " 00:00:00' 
        AND '" . sql_real_escape_string($end) . " 23:59:59' 
        AND od_enable_status = '1'";
$results = sql_query($sql);

$selects = sql_result_array($results);

$data = array();

foreach ($selects as $row) {
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

    $title = $row['od_name'] . ' (' . number_format(subscription_order_pay_price($row['od_id'])) . ')';

    $data[] = array(
        'oid' => get_text($row['od_id']),
        "title" => get_text($title),
        "start" => get_text(str_replace(" ", "T", $row['next_billing_date'])),
        "end" => get_text(str_replace(" ", "T", $row['next_billing_date'])),
        "color" => "#3788d8"
    );
}

$sql = "SELECT * FROM {$g5['g5_subscription_pay_table']} 
        WHERE py_time BETWEEN '" . sql_real_escape_string($start) . " 00:00:00' 
        AND '" . sql_real_escape_string($end) . " 23:59:59' ";

$results = sql_query($sql);

$selects2 = sql_result_array($results);
if ($results) {
    while ($row = sql_fetch_array($results)) {
        $selects2[] = $row;
    }
}

foreach ($selects2 as $row) {
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

    $title = $row['py_name'] . ' (' . number_format($row['py_receipt_price']) . ')';

    $data[] = array(
        'pid' => get_text($row['pay_id']),
        "title" => get_text($title),
        "start" => get_text(str_replace(" ", "T", $row['py_time'])),
        "end" => get_text(str_replace(" ", "T", $row['py_time'])),
        "color" => "#ed0707"
    );
}

$sql = "SELECT * FROM {$g5['g5_subscription_order_history_table']} 
        WHERE hs_time BETWEEN '" . sql_real_escape_string($start) . " 00:00:00' 
        AND '" . sql_real_escape_string($end) . " 23:59:59' 
        AND hs_type IN ('subscription_pay_db_fail', 'subscription_pay_pg_fail')";
$results = sql_query($sql);

$selects3 = sql_result_array($results);

foreach ($selects3 as $row) {
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

    $od = get_subscription_order($row['od_id']);

    $title = $od['od_name'] . ' (실패 : ' . get_text($row['hs_content']) . ')';

    $data[] = array(
        'oid' => get_text($row['od_id']),
        "title" => get_text($title),
        "start" => get_text(str_replace(" ", "T", $row['hs_time'])),
        "end" => get_text(str_replace(" ", "T", $row['hs_time'])),
        "color" => "#c3d4d4"
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
