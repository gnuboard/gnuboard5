<?php
// common.php 로그인 분기를 실행하여 회원 조회 실패와 기존 인증 처리를 검증한다.
if (PHP_SAPI !== 'cli') exit(1);
$source = file_get_contents(dirname(__DIR__).'/common.php');
$start = strpos($source, "if (isset(\$_SESSION['ss_mb_id'])");
$end = strpos($source, "} else {\n    // 자동로그인", $start);
if ($start === false || $end === false) exit(2);
$code = substr($source, $start, $end - $start)."}";
define('G5_SERVER_TIME', time());
define('G5_TIME_YMD', date('Y-m-d'));
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
set_error_handler(function($level, $message) { throw new Exception($message); });
function get_member($id) { global $fixture; return $fixture; }
function set_session($key, $value) { $_SESSION[$key] = $value; }
function check_auth_session_token($value) {
    global $token_ok, $token_checks;
    $token_checks++;
    return $token_ok;
}
function insert_point() { throw new Exception('회원 조회 실패 후 포인트 지급'); }
function sql_query() { throw new Exception('회원 조회 실패 후 SQL 갱신'); }
$valid = array('mb_id'=>'member','mb_intercept_date'=>'','mb_leave_date'=>'',
    'mb_datetime'=>'2026-01-01 00:00:00','mb_today_login'=>G5_TIME_YMDHIS);
$cases = array(
    array(null, true, false), array(array(), true, false),
    array($valid, true, true), array($valid, false, false),
    array(array_merge($valid, array('mb_intercept_date'=>date('Ymd'))), true, false),
    array(array_merge($valid, array('mb_leave_date'=>date('Ymd'))), true, false),
);
foreach ($cases as $case) {
    list($fixture,$token_ok,$logged_in) = $case;
    $token_checks = 0;
    $_SESSION = array('ss_mb_id'=>'member','ss_mb_token_key'=>'token','ss_cart_id'=>'cart');
    eval($code);
    if ((bool)$_SESSION['ss_mb_id'] !== $logged_in
        || (bool)$_SESSION['ss_mb_token_key'] !== $logged_in
        || $_SESSION['ss_cart_id'] !== 'cart') throw new Exception('로그인 세션 상태 불일치');
    if (empty($fixture['mb_id']) && $token_checks) throw new Exception('없는 회원의 토큰 검사');
}
echo "통과: 조회 실패·빈 배열·정상 회원·인증 만료·차단·탈퇴 6건\n";
