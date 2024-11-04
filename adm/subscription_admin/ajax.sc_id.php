<?php
include_once('./_common.php');

$sc_id = isset($_POST['sc_id']) ? trim($_POST['sc_id']) : '';
if (preg_match("/[^0-9a-z]/i", $sc_id)) {
    die("{\"error\":\"분류코드는 영문자 숫자 만 입력 가능합니다.\"}");
}

$sql = " select sc_name from {$g5['g5_shop_subscription_table']} where sc_id = '{$sc_id}' ";
$row = sql_fetch($sql);
if (isset($row['sc_name']) && $row['sc_name']) {
    $sc_name = addslashes($row['sc_name']);
    die("{\"error\":\"이미 등록된 분류코드 입니다.\\n\\n분류명 : {$sc_name}\"}");
}

die("{\"error\":\"\"}"); // 정상;