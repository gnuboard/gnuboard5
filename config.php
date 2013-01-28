<?php

// 절실함

if (function_exists("date_default_timezone_set")) 
    date_default_timezone_set("Asia/Seoul");

function g4_path()
{
    $path           = dirname(__FILE__);                                        // 예) /home/sir/www/g4s
    $linux_dir      = str_replace("\\", "/", $path);                            // 예) /home/sir/www/g4s
    $document_root  = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);        // 예) /home/sir/www
    $base_dir       = preg_replace('#^'.$document_root.'#i', '', $linux_dir);   // 예) /g4s
    $port           = $_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '';
    $http           = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';

    $result = array();
    $result['path']     = $path;
    $result['url']      = $http.$_SERVER['SERVER_NAME'].$port.$base_dir;
    $result['curr_url'] = $http.$_SERVER['SERVER_NAME'].$port.$_SERVER['PHP_SELF'];
    $result['curr_uri'] = $result['curr_url'] . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');

    return $result;
}

$g4_path = g4_path();

//==============================================================================
// 상수 선언
//------------------------------------------------------------------------------
// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define('_GNUBOARD_', true);

// URL 은 브라우저상에서의 경로 (도메인으로 부터의)
define('G4_URL',            $g4_path['url']);
define('G4_ADMIN_URL',      G4_URL.'/adm');
define('G4_BBS_URL',        G4_URL.'/bbs');
define('G4_CSS_URL',        G4_URL.'/css');
define('G4_DATA_URL',       G4_URL.'/data');
define('G4_IMG_URL',        G4_URL.'/img');
define('G4_JS_URL',         G4_URL.'/js');
define('G4_SKIN_URL',       G4_URL.'/skin');
define('G4_GCAPTCHA_URL',   G4_BBS_URL.'/gcaptcha');
define('G4_CKEDITOR_URL',   G4_BBS_URL.'/ckeditor'); // CKEDITOR 의 라이브러리 경로
define('G4_EDITOR_URL',     G4_DATA_URL.'/editor');  // CKEDITOR 에서 업로드한 파일이 저장되는 경로
define('G4_CACHE_URL',      G4_DATA_URL.'/cache');

// PATH 는 서버상에서의 절대경로
define('G4_PATH',           $g4_path['path']);
define('G4_ADMIN_PATH',     G4_PATH.'/adm');
define('G4_BBS_PATH',       G4_PATH.'/bbs');
define('G4_DATA_PATH',      G4_PATH.'/data');
define('G4_EXTEND_PATH',    G4_PATH.'/extend');
define('G4_LIB_PATH',       G4_PATH.'/lib');
define('G4_SKIN_PATH',      G4_PATH.'/skin');
define('G4_GCAPTCHA_PATH',  G4_BBS_PATH.'/gcaptcha');
define('G4_CKEDITOR_PATH',  G4_BBS_PATH.'/ckeditor');
define('G4_CACHE_PATH',     G4_DATA_PATH.'/cache');
define('G4_EDITOR_PATH',    G4_DATA_PATH.'/editor');

// 입력값 검사 상수 (숫자를 변경하시면 안됩니다.)
define('G4_ALPHAUPPER',   1); // 영대문자
define('G4_ALPHALOWER',   2); // 영소문자
define('G4_ALPHABETIC',   4); // 영대,소문자
define('G4_NUMERIC',      8); // 숫자
define('G4_HANGUL',      16); // 한글
define('G4_SPACE',       32); // 공백
define('G4_SPECIAL',     64); // 특수문자

// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']
define('G4_MOBILE_AGENT', 'phone|samsung|lgtel|mobile|skt|nokia|blackberry|android|sony');
//==============================================================================

// 자주 사용하는 값
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
$g4['server_time'] = time();
$g4['time_ymd']    = date("Y-m-d", $g4['server_time']);
$g4['time_his']    = date("H:i:s", $g4['server_time']);
$g4['time_ymdhis'] = date("Y-m-d H:i:s", $g4['server_time']);
define('G4_SERVER_TIME',    time());
define('G4_TIME_YMDHIS',    date("Y-m-d H:i:s", G4_SERVER_TIME));
define('G4_TIME_YMD',       substr(G4_TIME_YMDHIS,  0, 10));
define('G4_TIME_HIS',       substr(G4_TIME_YMDHIS, 11,  8));


//
// 테이블 명
// (상수로 선언한것은 함수에서 global 선언을 하지 않아도 바로 사용할 수 있기 때문)
//
$g4['table_prefix']        = G4_TABLE_PREFIX; // 테이블명 접두사
$g4['write_prefix']        = $g4['table_prefix'] . 'write_'; // 게시판 테이블명 접두사

$g4['auth_table']          = $g4['table_prefix'] . 'auth';          // 관리권한 설정 테이블
$g4['config_table']        = $g4['table_prefix'] . 'config';        // 기본환경 설정 테이블
$g4['group_table']         = $g4['table_prefix'] . 'group';         // 게시판 그룹 테이블
$g4['group_member_table']  = $g4['table_prefix'] . 'group_member';  // 게시판 그룹+회원 테이블
$g4['board_table']         = $g4['table_prefix'] . 'board';         // 게시판 설정 테이블
$g4['board_file_table']    = $g4['table_prefix'] . 'board_file';    // 게시판 첨부파일 테이블
$g4['board_good_table']    = $g4['table_prefix'] . 'board_good';    // 게시물 추천,비추천 테이블
$g4['board_new_table']     = $g4['table_prefix'] . 'board_new';     // 게시판 새글 테이블
$g4['login_table']         = $g4['table_prefix'] . 'login';         // 로그인 테이블 (접속자수)
$g4['mail_table']          = $g4['table_prefix'] . 'mail';          // 회원메일 테이블
$g4['member_table']        = $g4['table_prefix'] . 'member';        // 회원 테이블
$g4['memo_table']          = $g4['table_prefix'] . 'memo';          // 메모 테이블
$g4['poll_table']          = $g4['table_prefix'] . 'poll';          // 투표 테이블
$g4['poll_etc_table']      = $g4['table_prefix'] . 'poll_etc';      // 투표 기타의견 테이블
$g4['point_table']         = $g4['table_prefix'] . 'point';         // 포인트 테이블
$g4['popular_table']       = $g4['table_prefix'] . 'popular';       // 인기검색어 테이블
$g4['scrap_table']         = $g4['table_prefix'] . 'scrap';         // 게시글 스크랩 테이블
$g4['visit_table']         = $g4['table_prefix'] . 'visit';         // 방문자 테이블
$g4['visit_sum_table']     = $g4['table_prefix'] . 'visit_sum';     // 방문자 합계 테이블
$g4['token_table']         = $g4['table_prefix'] . 'token';         // 토큰 테이블

//
// 기타
//

// www.sir.co.kr 과 sir.co.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.co.kr 과 같이 입력하세요.
// 이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
$g4['cookie_domain'] = '';
define('G4_COOKIE_DOMAIN', '');

// 게시판에서 링크의 기본갯수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
//$g4['link_count'] = 2;
define('G4_LINK_COUNT', 2);

//$g4['charset'] = 'utf-8';

//$g4['token_time'] = 3; // 토큰 유효시간

// config.php 가 있는곳의 웹경로. 뒤에 / 를 붙이지 마세요.
// 예) http://g4.sir.co.kr
//$g4['url'] = '';
$g4['https_url'] = '';
define('G4_HTTPS_URL', '');
// 입력예
//$g4['url'] = "http://www.sir.co.kr";
//$g4['https_url'] = "https://www.sir.co.kr";

//$g4['dbconfig'] = 'data/dbconfig.php';

//$g4['js_file']        = array();

unset($g4_path);
?>
