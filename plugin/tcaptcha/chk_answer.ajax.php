<?
include_once("./_common.php");
include_once("$g4[path]/plugin/tcaptcha/tcaptcha.lib.php");

$user_token = trim($_POST['user_token']);
$tcaptcha_token = get_session("ss_tcaptcha_token");
if (!$user_token) {
    die("{\"error\":\"자동등록방지용 사용자 토큰값이 없습니다.\"}");
}

if ($user_token != $tcaptcha_token) {
    die("{\"error\":\"자동등록방지용 토큰값이 틀립니다.\"}");
}

$tcaptcha_error_count = (int)get_session("ss_tcaptcha_error_count");
if ($tcaptcha_error_count >= _ANSWER_COUNT_) {
    die("{\"error\":\"입력하신 답변 횟수가 "._ANSWER_COUNT_."회를 넘었습니다.\n\n문제를 클릭하신후 다시 답변해 주십시오.\"}");
}

$user_answer = $_POST['user_answer'];
$is_answer = (get_session("ss_tcaptcha_answer") == $user_answer);
if ($is_answer == false) {
    $tcaptcha_error_count++;
    set_session("ss_tcaptcha_error_count", $tcaptcha_error_count);
    // 토큰을 다시 생성
    $token = _token();
    set_session("ss_tcaptcha_token", $token);
    die("{\"error\":\"입력하신 답이 틀렸습니다.\",\"token\":\"$token\"}");
}

die("{\"error\":\"\"}");
?>