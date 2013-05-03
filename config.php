<?php

/********************
    상수 선언
********************/

// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define('_GNUBOARD_', true);

if (PHP_VERSION >= '5.3.0') {
    //if (function_exists("date_default_timezone_set")) date_default_timezone_set("Asia/Seoul");
    date_default_timezone_set("Asia/Seoul");
}


/********************
    경로 상수
********************/

/*
보안서버 도메인
회원가입, 글쓰기에 사용되는 https 로 시작되는 주소를 말합니다. 
포트가 있다면 도메인 뒤에 :443 과 같이 입력하세요.
보안서버주소가 없다면 공란으로 두시면 되며 보안서버주소 뒤에 / 는 붙이지 않습니다.
입력예) https://www.domain.com:443/gnuboard4s
*/
define('G4_DOMAIN', '');
define('G4_HTTPS_DOMAIN', '');

/*
www.sir.co.kr 과 sir.co.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.co.kr 과 같이 입력하세요.
이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
*/
define('G4_COOKIE_DOMAIN',  '.sirgle.com');

define('G4_DBCONFIG_FILE',  'dbconfig.php');

define('G4_ADMIN_DIR',      'adm');
define('G4_BBS_DIR',        'bbs');
define('G4_CSS_DIR',        'css');
define('G4_DATA_DIR',       'data');
define('G4_EXTEND_DIR',     'extend');
define('G4_IMG_DIR',        'img');
define('G4_JS_DIR',         'js');
define('G4_LIB_DIR',        'lib');
define('G4_PLUGIN_DIR',     'plugin');
define('G4_SKIN_DIR',       'skin');
define('G4_GCAPTCHA_DIR',   'gcaptcha');
define('G4_CKEDITOR_DIR',   'ckeditor');
define('G4_MOBILE_DIR',     'mobile');
define('G4_KCP_DIR',        'kcp');
define('G4_SNS_DIR',        'sns');
define('G4_SYNDI_DIR',      'syndi');

// URL 은 브라우저상에서의 경로 (도메인으로 부터의)
if (G4_DOMAIN) {
    define('G4_URL', G4_DOMAIN);
} else {
    if (isset($g4_path['url'])) 
        define('G4_URL', $g4_path['url']);
    else 
        define('G4_URL', '');
}

if (isset($g4_path['path'])) {
    define('G4_PATH', $g4_path['path']);
} else {
    define('G4_PATH', '');
}

define('G4_ADMIN_URL',      G4_URL.'/'.G4_ADMIN_DIR);
define('G4_BBS_URL',        G4_URL.'/'.G4_BBS_DIR);
define('G4_CSS_URL',        G4_URL.'/'.G4_CSS_DIR);
define('G4_DATA_URL',       G4_URL.'/'.G4_DATA_DIR);
define('G4_IMG_URL',        G4_URL.'/'.G4_IMG_DIR);
define('G4_JS_URL',         G4_URL.'/'.G4_JS_DIR);
define('G4_SKIN_URL',       G4_URL.'/'.G4_SKIN_DIR);
define('G4_PLUGIN_URL',     G4_URL.'/'.G4_PLUGIN_DIR);
define('G4_GCAPTCHA_URL',   G4_PLUGIN_URL.'/'.G4_GCAPTCHA_DIR);
define('G4_CKEDITOR_URL',   G4_PLUGIN_URL.'/'.G4_CKEDITOR_DIR); // CKEDITOR 의 라이브러리 경로
define('G4_KCP_URL',        G4_PLUGIN_URL.'/'.G4_KCP_DIR);
define('G4_SNS_URL',        G4_PLUGIN_URL.'/'.G4_SNS_DIR);
define('G4_SYNDI_URL',      G4_PLUGIN_URL.'/'.G4_SYNDI_DIR);
define('G4_MOBILE_URL',     G4_URL.'/'.G4_MOBILE_DIR);

// PATH 는 서버상에서의 절대경로
define('G4_ADMIN_PATH',     G4_PATH.'/'.G4_ADMIN_DIR);
define('G4_BBS_PATH',       G4_PATH.'/'.G4_BBS_DIR);
define('G4_DATA_PATH',      G4_PATH.'/'.G4_DATA_DIR);
define('G4_EXTEND_PATH',    G4_PATH.'/'.G4_EXTEND_DIR);
define('G4_LIB_PATH',       G4_PATH.'/'.G4_LIB_DIR);
define('G4_PLUGIN_PATH',    G4_PATH.'/'.G4_PLUGIN_DIR);
define('G4_SKIN_PATH',      G4_PATH.'/'.G4_SKIN_DIR);
define('G4_GCAPTCHA_PATH',  G4_PLUGIN_PATH.'/'.G4_GCAPTCHA_DIR);
define('G4_CKEDITOR_PATH',  G4_PLUGIN_PATH.'/'.G4_CKEDITOR_DIR);
define('G4_KCP_PATH',       G4_PLUGIN_PATH.'/'.G4_KCP_DIR);
define('G4_SNS_PATH',       G4_PLUGIN_PATH.'/'.G4_SNS_DIR);
define('G4_SYNDI_PATH',     G4_PLUGIN_PATH.'/'.G4_SYNDI_DIR);
define('G4_MOBILE_PATH',    G4_PATH.'/'.G4_MOBILE_DIR);
//==============================================================================


define('G4_USE_CACHE',  true); // 최신글등에 cache 기능 사용 여부


/********************
    시간 상수
********************/
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
define('G4_SERVER_TIME',    time());
define('G4_TIME_YMDHIS',    date('Y-m-d H:i:s', G4_SERVER_TIME));
define('G4_TIME_YMD',       substr(G4_TIME_YMDHIS, 0, 10));
define('G4_TIME_HIS',       substr(G4_TIME_YMDHIS, 11, 8));

// 입력값 검사 상수 (숫자를 변경하시면 안됩니다.)
define('G4_ALPHAUPPER',      1); // 영대문자
define('G4_ALPHALOWER',      2); // 영소문자
define('G4_ALPHABETIC',      4); // 영대,소문자
define('G4_NUMERIC',         8); // 숫자
define('G4_HANGUL',         16); // 한글
define('G4_SPACE',          32); // 공백
define('G4_SPECIAL',        64); // 특수문자

// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']
define('G4_MOBILE_AGENT',   'phone|samsung|lgtel|mobile|skt|nokia|blackberry|android|sony');


/********************
    SNS 상수
********************/

define('G4_FACEBOOK_APPID',     '119146498278078');
define('G4_FACEBOOK_SECRET',    '311e0d6ff8ff43cfe0e75fe82d71777c');
define('G4_FACEBOOK_CALLBACK',  G4_SNS_URL.'/facebook/callback.php');


/********************
    기타 상수
********************/

// 게시판에서 링크의 기본갯수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
define('G4_LINK_COUNT', 2);
?>