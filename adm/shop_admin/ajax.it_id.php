<?php
include_once('./_common.php');

$it_id = isset($_POST['it_id']) ? trim($_POST['it_id']) : '';
if (preg_match("/[^\w\-]/", $it_id)) { // \w : 0-9 A-Z a-z _
    die("{\"error\":\"상품코드는 영문자 숫자 _ - 만 입력 가능합니다.\"}");
}

$row = get_shop_item($it_id, true);
if (isset($row['it_name']) && $row['it_name']) {
    $it_name = addslashes($row['it_name']);
    die("{\"error\":\"이미 등록된 상품코드 입니다.\\n\\n상품명 : {$it_name}\"}");
}

die("{\"error\":\"\"}"); // 정상;