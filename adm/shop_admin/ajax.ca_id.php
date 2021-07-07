<?php
include_once('./_common.php');

$ca_id = isset($_POST['ca_id']) ? trim($_POST['ca_id']) : '';
if (preg_match("/[^0-9a-z]/i", $ca_id)) {
    die("{\"error\":\"분류코드는 영문자 숫자 만 입력 가능합니다.\"}");
}

$sql = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$ca_id}' ";
$row = sql_fetch($sql);
if (isset($row['ca_name']) && $row['ca_name']) {
    $ca_name = addslashes($row['ca_name']);
    die("{\"error\":\"이미 등록된 분류코드 입니다.\\n\\n분류명 : {$ca_name}\"}");
}

die("{\"error\":\"\"}"); // 정상;