<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');

if(function_exists('social_provider_logout')){
    social_provider_logout();
}

// 이호경님 제안 코드
session_unset(); // 모든 세션변수를 언레지스터 시켜줌
session_destroy(); // 세션해제함

// 자동로그인 해제 --------------------------------
set_cookie('ck_mb_id', '', 0);
set_cookie('ck_auto', '', 0);
// 자동로그인 해제 end --------------------------------

if ($url) {
    if ( substr($url, 0, 2) == '//' )
        $url = 'http:' . $url;

    if (preg_match('#\\\0#', $url) || preg_match('/^\/{1,}\\\/', $url)) {
        alert('url 에 올바르지 않은 값이 포함되어 있습니다.', G5_URL);
    }

    $p = @parse_url(urldecode(str_replace('\\', '', $url)));
    /*
        // OpenRediect 취약점관련, PHP 5.3 이하버전에서는 parse_url 버그가 있음 ( Safflower 님 제보 ) 아래 url 예제
        // http://localhost/bbs/logout.php?url=http://sir.kr%23@/
    */
    if (preg_match('/^https?:\/\//i', $url) || $p['scheme'] || $p['host']) {
        alert('url에 도메인을 지정할 수 없습니다.', G5_URL);
    }

    if($url == 'shop')
        $link = G5_SHOP_URL;
    else
        $link = $url;
} else if ($bo_table) {
    $link = get_pretty_url($bo_table);
} else {
    $link = G5_URL;
}

run_event('member_logout', $link);

goto_url($link);