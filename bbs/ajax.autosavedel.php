<?php
include_once("./_common.php");

if (!$is_member) die("0");

// 삭제는 같은 출처의 AJAX POST로만 처리한다(교차 사이트 GET·양식 요청 차단).
// X-Requested-With는 교차 출처 요청에서 사전 요청(preflight) 없이 붙일 수 없다.
$requested_with = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $requested_with !== 'xmlhttprequest') die("-1");
if (function_exists('check_request_origin')) check_request_origin();

$as_id = isset($_POST['as_id']) ? (int)$_POST['as_id'] : 0;

$sql = " delete from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' and as_id = {$as_id} ";
$result = sql_query($sql);
if (!$result) {
    echo "-1";
}

echo autosave_count($member['mb_id']);