<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

// 회원 ID 열거 공격 방지를 위한 Rate Limit (분당 30회)
if (function_exists('check_rate_limit') && !check_rate_limit('ajax_mb_id_check', 30, 60)) {
    die('너무 많은 요청이 발생했습니다. 잠시 후 다시 시도해 주세요.');
}

$mb_id = isset($_POST['reg_mb_id']) ? trim($_POST['reg_mb_id']) : '';

set_session('ss_check_mb_id', '');

if ($msg = empty_mb_id($mb_id))     die($msg);
if ($msg = valid_mb_id($mb_id))     die($msg);
if ($msg = count_mb_id($mb_id))     die($msg);
if ($msg = exist_mb_id($mb_id))     die($msg);
if ($msg = reserve_mb_id($mb_id))   die($msg);

set_session('ss_check_mb_id', $mb_id);