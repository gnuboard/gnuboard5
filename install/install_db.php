<?
set_time_limit(0);

include_once ('../config.php');
include_once ('./install.inc.php');

$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

//print_r($_POST); exit;

$mysql_host  = $_POST['mysql_host'];
$mysql_user  = $_POST['mysql_user'];
$mysql_pass  = $_POST['mysql_pass'];
$mysql_db    = $_POST['mysql_db'];
$mysql_port  = $_POST['mysql_port'];
$table_prefix= $_POST['table_prefix'];
$admin_id    = $_POST['admin_id'];
$admin_pass  = $_POST['admin_pass'];
$admin_name  = $_POST['admin_name'];
$admin_email = $_POST['admin_email'];

@mysql_query('set names utf8');
$dblink = @mysql_connect($mysql_host.':'.$mysql_port, $mysql_user, $mysql_pass);
if (!$dblink) {
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.PHP_EOL;
    echo '<div>MySQL Host, User, Password 를 확인해 주십시오.</div>'.PHP_EOL;
    echo '<div><a href="./install_config.php">뒤로가기</a></div>'.PHP_EOL;
    exit;
}

@mysql_query('set names utf8');
$select_db = @mysql_select_db($mysql_db, $dblink);
if (!$select_db) {
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.PHP_EOL;
    echo '<div>MySQL DB 를 확인해 주십시오.</div>'.PHP_EOL;
    echo '<div><a href="./install_config.php">뒤로가기</a></div>'.PHP_EOL;
    exit;
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>그누보드4 설치 (3/3) - DB</title>
</head>

<body>
<div>그누보드4S 설치시작</div>
<?
// 테이블 생성 ------------------------------------
$file = implode('', file('./gnuboard4s.sql'));
eval("\$file = \"$file\";");

$file = preg_replace('/^--.*$/m', '', $file);
$file = preg_replace('/`g4s_([^`]+`)/', '`'.$table_prefix.'$1', $file);
$f = explode(';', $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == '') continue;
    mysql_query($f[$i]) or die(mysql_error());
}
// 테이블 생성 ------------------------------------

echo '<div>전체 테이블 생성 완료</div>';

$read_point = -1;
$write_point = 5;
$comment_point = 1;
$download_point = -20;

//-------------------------------------------------------------------------------------------------
// config 테이블 설정
$sql = " insert into `{$table_prefix}config`
            set cf_title = '그누보드4',
                cf_admin = '$admin_id',
                cf_use_point = '1',
                cf_use_norobot = '1',
                cf_use_copy_log = '1',
                cf_login_point = '100',
                cf_memo_send_point = '500',
                cf_cut_name = '15',
                cf_nick_modify = '60',
                cf_new_skin = 'basic',
                cf_new_rows = '15',
                cf_search_skin = 'basic',
                cf_connect_skin = 'basic',
                cf_read_point = '$read_point',
                cf_write_point = '$write_point',
                cf_comment_point = '$comment_point',
                cf_download_point = '$download_point',
                cf_search_bgcolor = 'YELLOW',
                cf_search_color = 'RED',
                cf_write_pages = '10',
                cf_link_target = '_blank',
                cf_delay_sec = '30',
                cf_filter = '18아,18놈,18새끼,18년,18뇬,18노,18것,18넘,개년,개놈,개뇬,개새,개색끼,개세끼,개세이,개쉐이,개쉑,개쉽,개시키,개자식,개좆,게색기,게색끼,광뇬,뇬,눈깔,뉘미럴,니귀미,니기미,니미,도촬,되질래,뒈져라,뒈진다,디져라,디진다,디질래,병쉰,병신,뻐큐,뻑큐,뽁큐,삐리넷,새꺄,쉬발,쉬밸,쉬팔,쉽알,스패킹,스팽,시벌,시부랄,시부럴,시부리,시불,시브랄,시팍,시팔,시펄,실밸,십8,십쌔,십창,싶알,쌉년,썅놈,쌔끼,쌩쑈,썅,써벌,썩을년,쎄꺄,쎄엑,쓰바,쓰발,쓰벌,쓰팔,씨8,씨댕,씨바,씨발,씨뱅,씨봉알,씨부랄,씨부럴,씨부렁,씨부리,씨불,씨브랄,씨빠,씨빨,씨뽀랄,씨팍,씨팔,씨펄,씹,아가리,아갈이,엄창,접년,잡놈,재랄,저주글,조까,조빠,조쟁이,조지냐,조진다,조질래,존나,존니,좀물,좁년,좃,좆,좇,쥐랄,쥐롤,쥬디,지랄,지럴,지롤,지미랄,쫍빱,凸,퍽큐,뻑큐,빠큐,ㅅㅂㄹㅁ',
                cf_possible_ip = '',
                cf_intercept_ip = '',
                cf_member_skin = 'basic',
                cf_register_level = '2',
                cf_register_point = '1000',
                cf_icon_level = '2',
                cf_leave_day = '30',
                cf_search_part = '10000',
                cf_email_use = '1',
                cf_prohibit_id = 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객',
                cf_prohibit_email = '',
                cf_new_del = '30',
                cf_memo_del = '180',
                cf_visit_del = '180',
                cf_popular_del = '180',
                cf_use_member_icon = '2',
                cf_member_icon_size = '5000',
                cf_member_icon_width = '22',
                cf_member_icon_height = '22',
                cf_login_minutes = '10',
                cf_image_extension = 'gif|jpg|jpeg|png',
                cf_flash_extension = 'swf',
                cf_movie_extension = 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
                cf_formmail_is_member = '1',
                cf_page_rows = '15',
                cf_stipulation = '해당 홈페이지에 맞는 회원가입약관을 입력합니다.',
                cf_privacy = '해당 홈페이지에 맞는 개인정보취급방침을 입력합니다.'
                ";
mysql_query($sql) or die(mysql_error() . "<p>" . $sql);

// 운영자 회원가입
$sql = " insert into `{$table_prefix}member`
            set mb_id = '$admin_id',
                mb_password = PASSWORD('$admin_pass'),
                mb_name = '$admin_name',
                mb_nick = '$admin_name',
                mb_email = '$admin_email',
                mb_level = '10',
                mb_mailling = '1',
                mb_open = '1',
                mb_email_certify = '".G4_TIME_YMDHIS."',
                mb_datetime = '".G4_TIME_YMDHIS."',
                mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                ";
@mysql_query($sql);

echo '<div>DB설정 완료</div>';
//-------------------------------------------------------------------------------------------------


// 디렉토리 생성
$dir_arr = array (
    $data_path.'/cache',
    $data_path.'/editor',
    $data_path.'/file',
    $data_path.'/log',
    $data_path.'/member',
    $data_path.'/session'
);

for ($i=0; $i<count($dir_arr); $i++) {
    @mkdir($dir_arr[$i], 0707);
    @chmod($dir_arr[$i], 0707);
}

echo '<div>데이터 디렉토리 생성 완료</div>';
//-------------------------------------------------------------------------------------------------

// DB 설정 파일 생성
$file = '../'.G4_DATA_DIR.'/'.G4_DBCONFIG_FILE;
$f = @fopen($file, 'w');

fwrite($f, "<?php\n");
fwrite($f, "if (!defined('_GNUBOARD_')) exit;\n");
fwrite($f, "define('G4_MYSQL_HOST', '{$mysql_host}:{$mysql_port}');\n");
fwrite($f, "define('G4_MYSQL_USER', '{$mysql_user}');\n");
fwrite($f, "define('G4_MYSQL_PASSWORD', '{$mysql_pass}');\n");
fwrite($f, "define('G4_MYSQL_DB', '{$mysql_db}');\n\n");
fwrite($f, "define('G4_TABLE_PREFIX', '{$table_prefix}');\n\n");
fwrite($f, "\$g4['write_prefix'] = G4_TABLE_PREFIX.'write_'; // 게시판 테이블명 접두사\n\n");
fwrite($f, "\$g4['auth_table'] = G4_TABLE_PREFIX.'auth'; // 관리권한 설정 테이블\n");
fwrite($f, "\$g4['config_table'] = G4_TABLE_PREFIX.'config'; // 기본환경 설정 테이블\n");
fwrite($f, "\$g4['group_table'] = G4_TABLE_PREFIX.'group'; // 게시판 그룹 테이블\n");
fwrite($f, "\$g4['group_member_table'] = G4_TABLE_PREFIX.'group_member'; // 게시판 그룹+회원 테이블\n");
fwrite($f, "\$g4['board_table'] = G4_TABLE_PREFIX.'board'; // 게시판 설정 테이블\n");
fwrite($f, "\$g4['board_file_table'] = G4_TABLE_PREFIX.'board_file'; // 게시판 첨부파일 테이블\n");
fwrite($f, "\$g4['board_good_table'] = G4_TABLE_PREFIX.'board_good'; // 게시물 추천,비추천 테이블\n");
fwrite($f, "\$g4['board_new_table'] = G4_TABLE_PREFIX.'board_new'; // 게시판 새글 테이블\n");
fwrite($f, "\$g4['login_table'] = G4_TABLE_PREFIX.'login'; // 로그인 테이블 (접속자수)\n");
fwrite($f, "\$g4['mail_table'] = G4_TABLE_PREFIX.'mail'; // 회원메일 테이블\n");
fwrite($f, "\$g4['member_table'] = G4_TABLE_PREFIX.'member'; // 회원 테이블\n");
fwrite($f, "\$g4['memo_table'] = G4_TABLE_PREFIX.'memo'; // 메모 테이블\n");
fwrite($f, "\$g4['poll_table'] = G4_TABLE_PREFIX.'poll'; // 투표 테이블\n");
fwrite($f, "\$g4['poll_etc_table'] = G4_TABLE_PREFIX.'poll_etc'; // 투표 기타의견 테이블\n");
fwrite($f, "\$g4['point_table'] = G4_TABLE_PREFIX.'point'; // 포인트 테이블\n");
fwrite($f, "\$g4['popular_table'] = G4_TABLE_PREFIX.'popular'; // 인기검색어 테이블\n");
fwrite($f, "\$g4['scrap_table'] = G4_TABLE_PREFIX.'scrap'; // 게시글 스크랩 테이블\n");
fwrite($f, "\$g4['visit_table'] = G4_TABLE_PREFIX.'visit'; // 방문자 테이블\n");
fwrite($f, "\$g4['visit_sum_table'] = G4_TABLE_PREFIX.'visit_sum'; // 방문자 합계 테이블\n");
fwrite($f, "\$g4['uniqid_table'] = G4_TABLE_PREFIX.'uniqid'; // 유니크한 값을 만드는 테이블\n");
fwrite($f, "?>");

fclose($f);
@chmod($file, 0606);
echo "<div>DB설정 파일 생성 완료 ($file)";

// data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl 파일을 실행할수 없게함.
$f = fopen($data_path.'/.htaccess', 'w');
$str = <<<EOD
<FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll])">
Order allow,deny
Deny from all
</FilesMatch>
EOD;
fwrite($f, $str);
fclose($f);
//-------------------------------------------------------------------------------------------------

echo '<div>필요한 DB Table, File, 디렉토리 생성을 모두 완료 하였습니다.</div>'.PHP_EOL;
echo '<div>메인화면에서 운영자 로그인을 한 후 운영자 화면으로 이동하여 환경설정을 변경해 주십시오.</div>';
echo '<div><a href="../index.php">메인화면으로 가기</a></div>';
?>

</body>
</html>