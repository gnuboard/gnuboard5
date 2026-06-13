<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

// 추천인 ID 열거 공격 방지를 위한 Rate Limit (분당 30회)
if (function_exists('check_rate_limit') && !check_rate_limit('ajax_mb_recommend_check', 30, 60)) {
    die('너무 많은 요청이 발생했습니다. 잠시 후 다시 시도해 주세요.');
}

$mb_recommend = isset($_POST["reg_mb_recommend"]) ? trim($_POST["reg_mb_recommend"]) : '';

if ($msg = valid_mb_id($mb_recommend)) {
    die("추천인의 아이디는 영문자, 숫자, _ 만 입력하세요.");
}
if (!($msg = exist_mb_id($mb_recommend))) {
    die("입력하신 추천인은 존재하지 않는 아이디 입니다.");
}