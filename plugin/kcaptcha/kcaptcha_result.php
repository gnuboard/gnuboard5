<?
// 캡챠 세션값과 비교하여 맞는지? 틀린지? 결과값을 출력합니다.
include_once("_common.php");
//header("Content-Type: text/html; charset=$g4[charset]");

$count = (int)get_session("ss_captcha_count");
if ($count >= 5) { // 설정값 이상이면 자동등록방지 입력 문자가 맞아도 오류 처리
    echo false;
} else {
    set_session("ss_captcha_count", $count + 1);
    echo (get_session("ss_captcha_key") == $_POST['captcha_key']) ? true : false;
}
?>