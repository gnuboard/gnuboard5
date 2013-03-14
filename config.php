<?
// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define("_GNUBOARD_", TRUE);

if (function_exists("date_default_timezone_set"))
    date_default_timezone_set("Asia/Seoul");

// 디렉토리
$g4['bbs']            = "bbs";
$g4['bbs_path']       = $g4['path'] . "/" . $g4['bbs'];
$g4['bbs_img']        = "img";
$g4['bbs_img_path']   = $g4['path'] . "/" . $g4['bbs'] . "/" . $g4['bbs_img'];

$g4['admin']          = "adm";
$g4['admin_path']     = $g4['path'] . "/" . $g4['admin'];

$g4['editor']         = "cheditor";
$g4['editor_path']    = $g4['path'] . "/" . $g4['editor'];

$g4['cheditor4']      = "cheditor4";
$g4['cheditor4_path'] = $g4['path'] . "/" . $g4['cheditor4'];

$g4['is_cheditor5']   = true;

$g4['geditor']        = "geditor";
$g4['geditor_path']   = $g4['path'] . "/" . $g4['geditor'];

// 자주 사용하는 값
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
$g4['server_time'] = time();
$g4['time_ymd']    = date("Y-m-d", $g4['server_time']);
$g4['time_his']    = date("H:i:s", $g4['server_time']);
$g4['time_ymdhis'] = date("Y-m-d H:i:s", $g4['server_time']);

//
// 테이블 명
// (상수로 선언한것은 함수에서 global 선언을 하지 않아도 바로 사용할 수 있기 때문)
//
$g4['table_prefix']        = "g4_"; // 테이블명 접두사
$g4['write_prefix']        = $g4['table_prefix'] . "write_"; // 게시판 테이블명 접두사

$g4['auth_table']          = $g4['table_prefix'] . "auth";          // 관리권한 설정 테이블
$g4['config_table']        = $g4['table_prefix'] . "config";        // 기본환경 설정 테이블
$g4['group_table']         = $g4['table_prefix'] . "group";         // 게시판 그룹 테이블
$g4['group_member_table']  = $g4['table_prefix'] . "group_member";  // 게시판 그룹+회원 테이블
$g4['board_table']         = $g4['table_prefix'] . "board";         // 게시판 설정 테이블
$g4['board_file_table']    = $g4['table_prefix'] . "board_file";    // 게시판 첨부파일 테이블
$g4['board_good_table']    = $g4['table_prefix'] . "board_good";    // 게시물 추천,비추천 테이블
$g4['board_new_table']     = $g4['table_prefix'] . "board_new";     // 게시판 새글 테이블
$g4['login_table']         = $g4['table_prefix'] . "login";         // 로그인 테이블 (접속자수)
$g4['mail_table']          = $g4['table_prefix'] . "mail";          // 회원메일 테이블
$g4['member_table']        = $g4['table_prefix'] . "member";        // 회원 테이블
$g4['memo_table']          = $g4['table_prefix'] . "memo";          // 메모 테이블
$g4['poll_table']          = $g4['table_prefix'] . "poll";          // 투표 테이블
$g4['poll_etc_table']      = $g4['table_prefix'] . "poll_etc";      // 투표 기타의견 테이블
$g4['point_table']         = $g4['table_prefix'] . "point";         // 포인트 테이블
$g4['popular_table']       = $g4['table_prefix'] . "popular";       // 인기검색어 테이블
$g4['scrap_table']         = $g4['table_prefix'] . "scrap";         // 게시글 스크랩 테이블
$g4['visit_table']         = $g4['table_prefix'] . "visit";         // 방문자 테이블
$g4['visit_sum_table']     = $g4['table_prefix'] . "visit_sum";     // 방문자 합계 테이블
$g4['token_table']         = $g4['table_prefix'] . "token";         // 토큰 테이블

//
// 기타
//

// www.sir.co.kr 과 sir.co.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.co.kr 과 같이 입력하세요.
// 이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
$g4['cookie_domain'] = "";

// 게시판에서 링크의 기본갯수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
$g4['link_count'] = 2;

$g4['charset'] = "utf-8";

$g4['phpmyadmin_dir'] = $g4['admin'] . "/phpMyAdmin/";

$g4['token_time'] = 3; // 토큰 유효시간

// config.php 가 있는곳의 웹경로. 뒤에 / 를 붙이지 마세요.
// 예) http://g4.sir.co.kr
$g4['url'] = "";
$g4['https_url'] = "";
// 입력예
//$g4['url'] = "http://www.sir.co.kr";
//$g4['https_url'] = "https://www.sir.co.kr";
?>
