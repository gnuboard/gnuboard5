<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

//  norobot.inc.php 가 선행된 후 사용

// 자동등록방지 검사
if ($config[cf_use_norobot]) {
    /*
    // 우선 이 URL 로 부터 온것인지 검사
    $parse = parse_url($_SERVER[HTTP_REFERER]);
    // 3.35
    // 포트번호가 존재할 경우의 처리 (mumu님께서 알려주셨습니다)
    $parse2 = explode(":", $_SERVER[HTTP_HOST]);
    if ($parse[host] != $parse2[0]) {
    //if ($parse[host] != $_SERVER[HTTP_HOST]) {
        alert("올바른 접근이 아닌것 같습니다.", "./");
    }
    */

    $key = $_SESSION[ss_norobot_key];
    if (($w=='' || $w=='c') && !$member[mb_id]) {
        if ($key) {
            if ($key != $_POST[wr_key]) {
                alert("정상적인 등록이 아닌것 같습니다.");
            }
        } else {
            alert("정상적인 접근이 아닌것 같습니다.");
        }
    }
}
?>
