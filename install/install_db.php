<?php
@set_time_limit(0);
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

include_once ('../config.php');
include_once ('../lib/common.lib.php');
include_once('./install.function.php');    // 인스톨 과정 함수 모음

include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');    
include_once('../lib/uri.lib.php');    // URL 함수 파일
include_once('../lib/cache.lib.php');

$title = G5_VERSION." 설치 완료 3/3";
include_once ('./install.inc.php');

//print_r($_POST); exit;

$mysql_host  = isset($_POST['mysql_host']) ? safe_install_string_check($_POST['mysql_host']) : '';
$mysql_user  = isset($_POST['mysql_user']) ? safe_install_string_check($_POST['mysql_user']) : '';
$mysql_pass  = isset($_POST['mysql_pass']) ? safe_install_string_check($_POST['mysql_pass']) : '';
$mysql_db    = isset($_POST['mysql_db']) ? safe_install_string_check($_POST['mysql_db']) : '';
$table_prefix= isset($_POST['table_prefix']) ? safe_install_string_check($_POST['table_prefix']) : '';
$admin_id    = isset($_POST['admin_id']) ? $_POST['admin_id'] : '';
$admin_pass  = isset($_POST['admin_pass']) ? $_POST['admin_pass'] : '';
$admin_name  = isset($_POST['admin_name']) ? $_POST['admin_name'] : '';
$admin_email = isset($_POST['admin_email']) ? $_POST['admin_email'] : '';

if (preg_match("/[^0-9a-z_]+/i", $table_prefix) ) {
    die('<div class="ins_inner"><p>TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.</p><div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div></div>');
}

if (preg_match("/[^0-9a-z_]+/i", $admin_id)) {
    die('<div class="ins_inner"><p>관리자 아이디는 영문자, 숫자, _ 만 입력하세요.</p><div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div></div>');
}

$dblink = sql_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if (!$dblink) {
?>

<div class="ins_inner">
    <p>MySQL Host, User, Password 를 확인해 주십시오.</p>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>

<?php
    include_once ('./install.inc2.php');
    exit;
}

$g5['connect_db'] = $dblink;
$select_db = sql_select_db($mysql_db, $dblink);
if (!$select_db) {
?>

<div class="ins_inner">
    <p>MySQL DB 를 확인해 주십시오.</p>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>

<?php
    include_once ('./install.inc2.php');
    exit;
}

$mysql_set_mode = 'false';
sql_set_charset(G5_DB_CHARSET, $dblink);
$result = sql_query(" SELECT @@sql_mode as mode ", true, $dblink);
$row = sql_fetch_array($result);
if($row['mode']) {
    sql_query("SET SESSION sql_mode = ''", true, $dblink);
    $mysql_set_mode = 'true';
}
unset($result);
unset($row);
?>

<div class="ins_inner">
    <h2><?php echo G5_VERSION ?> 설치가 시작되었습니다.</h2>

    <ol>
<?php
// 테이블 생성 ------------------------------------
$file = implode('', file('./gnuboard5.sql'));
eval("\$file = \"$file\";");

$file = preg_replace('/^--.*$/m', '', $file);
$file = preg_replace('/`g5_([^`]+`)/', '`'.$table_prefix.'$1', $file);
$f = explode(';', $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == '') continue;

    $sql = get_db_create_replace($f[$i]);

    sql_query($sql, true, $dblink);
}
// 테이블 생성 ------------------------------------
?>

        <li>전체 테이블 생성 완료</li>

<?php
$read_point = 0;
$write_point = 0;
$comment_point = 0;
$download_point = 0;

//-------------------------------------------------------------------------------------------------
// config 테이블 설정
$sql = " insert into `{$table_prefix}config`
            set cf_title = '".G5_VERSION."',
                cf_theme = 'basic',
                cf_admin = '$admin_id',
                cf_admin_email = '$admin_email',
                cf_admin_email_name = '".G5_VERSION."',
                cf_use_point = '1',
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
                cf_write_pages = '10',
                cf_mobile_pages = '5',
                cf_link_target = '_blank',
                cf_delay_sec = '30',
                cf_filter = '18아,18놈,18새끼,18뇬,18노,18것,18넘,개년,개놈,개뇬,개새,개색끼,개세끼,개세이,개쉐이,개쉑,개쉽,개시키,개자식,개좆,게색기,게색끼,광뇬,뇬,눈깔,뉘미럴,니귀미,니기미,니미,도촬,되질래,뒈져라,뒈진다,디져라,디진다,디질래,병쉰,병신,뻐큐,뻑큐,뽁큐,삐리넷,새꺄,쉬발,쉬밸,쉬팔,쉽알,스패킹,스팽,시벌,시부랄,시부럴,시부리,시불,시브랄,시팍,시팔,시펄,실밸,십8,십쌔,십창,싶알,쌉년,썅놈,쌔끼,쌩쑈,썅,써벌,썩을년,쎄꺄,쎄엑,쓰바,쓰발,쓰벌,쓰팔,씨8,씨댕,씨바,씨발,씨뱅,씨봉알,씨부랄,씨부럴,씨부렁,씨부리,씨불,씨브랄,씨빠,씨빨,씨뽀랄,씨팍,씨팔,씨펄,씹,아가리,아갈이,엄창,접년,잡놈,재랄,저주글,조까,조빠,조쟁이,조지냐,조진다,조질래,존나,존니,좀물,좁년,좃,좆,좇,쥐랄,쥐롤,쥬디,지랄,지럴,지롤,지미랄,쫍빱,凸,퍽큐,뻑큐,빠큐,ㅅㅂㄹㅁ',
                cf_possible_ip = '',
                cf_intercept_ip = '',
                cf_analytics = '',
                cf_member_skin = 'basic',
                cf_mobile_new_skin = 'basic',
                cf_mobile_search_skin = 'basic',
                cf_mobile_connect_skin = 'basic',
                cf_mobile_member_skin = 'basic',
                cf_faq_skin = 'basic',
                cf_mobile_faq_skin = 'basic',
                cf_editor = 'smarteditor2',
                cf_captcha_mp3 = 'basic',
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
                cf_member_img_size = '50000',
                cf_member_img_width = '60',
                cf_member_img_height = '60',
                cf_login_minutes = '10',
                cf_image_extension = 'gif|jpg|jpeg|png',
                cf_flash_extension = 'swf',
                cf_movie_extension = 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
                cf_formmail_is_member = '1',
                cf_page_rows = '15',
                cf_mobile_page_rows = '15',
                cf_cert_limit = '2',
                cf_stipulation = '해당 홈페이지에 맞는 회원가입약관을 입력합니다.',
                cf_privacy = '해당 홈페이지에 맞는 개인정보처리방침을 입력합니다.'
                ";
sql_query($sql, true, $dblink);

// 1:1문의 설정
$sql = " insert into `{$table_prefix}qa_config`
            ( qa_title, qa_category, qa_skin, qa_mobile_skin, qa_use_email, qa_req_email, qa_use_hp, qa_req_hp, qa_use_editor, qa_subject_len, qa_mobile_subject_len, qa_page_rows, qa_mobile_page_rows, qa_image_width, qa_upload_size, qa_insert_content )
          values
            ( '1:1문의', '회원|포인트', 'basic', 'basic', '1', '0', '1', '0', '1', '60', '30', '15', '15', '600', '1048576', '' ) ";
sql_query($sql, true, $dblink);

// 관리자 회원가입
$sql = " insert into `{$table_prefix}member`
            set mb_id = '$admin_id',
                 mb_password = '".get_encrypt_string($admin_pass)."',
                 mb_name = '$admin_name',
                 mb_nick = '$admin_name',
                 mb_email = '$admin_email',
                 mb_level = '10',
                 mb_mailling = '1',
                 mb_open = '1',
                 mb_nick_date = '".G5_TIME_YMDHIS."',
                 mb_email_certify = '".G5_TIME_YMDHIS."',
                 mb_datetime = '".G5_TIME_YMDHIS."',
                 mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                 ";
sql_query($sql, true, $dblink);

// 내용관리 생성
sql_query(" insert into `{$table_prefix}content` set co_id = 'company', co_html = '1', co_subject = '회사소개', co_seo_title = '".generate_seo_title('회사소개')."', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b></p>' ", true, $dblink);
sql_query(" insert into `{$table_prefix}content` set co_id = 'privacy', co_html = '1', co_subject = '개인정보 처리방침', co_seo_title = '".generate_seo_title('개인정보 처리방침')."', co_content= '<p align=center><b>개인정보 처리방침에 대한 내용을 입력하십시오.</b></p>' ", true, $dblink);
sql_query(" insert into `{$table_prefix}content` set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_seo_title = '".generate_seo_title('서비스 이용약관')."', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.</b></p>' ", true, $dblink);

// FAQ Master
sql_query(" insert into `{$table_prefix}faq_master` set fm_id = '1', fm_subject = '자주하시는 질문' ", true, $dblink);

$tmp_gr_id = defined('G5_YOUNGCART_VER') ? 'shop' : 'community';
$tmp_gr_subject = defined('G5_YOUNGCART_VER') ? '쇼핑몰' : '커뮤니티';

// 게시판 그룹 생성
sql_query(" insert into `{$table_prefix}group` set gr_id = '$tmp_gr_id', gr_subject = '$tmp_gr_subject' ", true, $dblink);

// 게시판 생성
$tmp_bo_table   = array ("notice", "qa", "free", "gallery");
$tmp_bo_subject = array ("공지사항", "질문답변", "자유게시판", "갤러리");
for ($i=0; $i<count($tmp_bo_table); $i++)
{

    $bo_skin = ($tmp_bo_table[$i] === 'gallery') ? 'gallery' : 'basic';

    $sql = " insert into `{$table_prefix}board`
                set bo_table = '$tmp_bo_table[$i]',
                    gr_id = '$tmp_gr_id',
                    bo_subject = '$tmp_bo_subject[$i]',
                    bo_device           = 'both',
                    bo_admin            = '',
                    bo_list_level       = '1',
                    bo_read_level       = '1',
                    bo_write_level      = '1',
                    bo_reply_level      = '1',
                    bo_comment_level    = '1',
                    bo_html_level       = '1',
                    bo_link_level       = '1',
                    bo_count_modify     = '1',
                    bo_count_delete     = '1',
                    bo_upload_level     = '1',
                    bo_download_level   = '1',
                    bo_read_point       = '-1',
                    bo_write_point      = '5',
                    bo_comment_point    = '1',
                    bo_download_point   = '-20',
                    bo_use_category     = '0',
                    bo_category_list    = '',
                    bo_use_sideview     = '0',
                    bo_use_file_content = '0',
                    bo_use_secret       = '0',
                    bo_use_dhtml_editor = '0',
                    bo_use_rss_view     = '0',
                    bo_use_good         = '0',
                    bo_use_nogood       = '0',
                    bo_use_name         = '0',
                    bo_use_signature    = '0',
                    bo_use_ip_view      = '0',
                    bo_use_list_view    = '0',
                    bo_use_list_content = '0',
                    bo_use_email        = '0',
                    bo_table_width      = '100',
                    bo_subject_len      = '60',
                    bo_mobile_subject_len      = '30',
                    bo_page_rows        = '15',
                    bo_mobile_page_rows = '15',
                    bo_new              = '24',
                    bo_hot              = '100',
                    bo_image_width      = '835',
                    bo_skin             = '$bo_skin',
                    bo_mobile_skin      = '$bo_skin',
                    bo_include_head     = '_head.php',
                    bo_include_tail     = '_tail.php',
                    bo_content_head     = '',
                    bo_content_tail     = '',
                    bo_mobile_content_head     = '',
                    bo_mobile_content_tail     = '',
                    bo_insert_content   = '',
                    bo_gallery_cols     = '4',
                    bo_gallery_width    = '202',
                    bo_gallery_height   = '150',
                    bo_mobile_gallery_width = '125',
                    bo_mobile_gallery_height= '100',
                    bo_upload_count     = '2',
                    bo_upload_size      = '1048576',
                    bo_reply_order      = '1',
                    bo_use_search       = '0',
                    bo_order            = '0'
                    ";
    sql_query($sql, true, $dblink);

    // 게시판 테이블 생성
    $file = file("../".G5_ADMIN_DIR."/sql_write.sql");
    $file = get_db_create_replace($file);
    $sql = implode("\n", $file);

    $create_table = $table_prefix.'write_' . $tmp_bo_table[$i];

    // sql_board.sql 파일의 테이블명을 변환
    $source = array("/__TABLE_NAME__/", "/;/");
    $target = array($create_table, "");
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, false, $dblink);
}
?>

        <li>DB설정 완료</li>

<?php
//-------------------------------------------------------------------------------------------------

// 디렉토리 생성
$dir_arr = array (
    $data_path.'/cache',
    $data_path.'/editor',
    $data_path.'/file',
    $data_path.'/log',
    $data_path.'/member',
    $data_path.'/member_image',
    $data_path.'/session',
    $data_path.'/content',
    $data_path.'/faq',
    $data_path.'/tmp'
);

for ($i=0; $i<count($dir_arr); $i++) {
    @mkdir($dir_arr[$i], G5_DIR_PERMISSION);
    @chmod($dir_arr[$i], G5_DIR_PERMISSION);
}
?>

        <li>데이터 디렉토리 생성 완료</li>

<?php
//-------------------------------------------------------------------------------------------------

// DB 설정 파일 생성
$file = '../'.G5_DATA_DIR.'/'.G5_DBCONFIG_FILE;
$f = @fopen($file, 'a');

fwrite($f, "<?php\n");
fwrite($f, "if (!defined('_GNUBOARD_')) exit;\n");
fwrite($f, "define('G5_MYSQL_HOST', '".addcslashes($mysql_host, "\\'")."');\n");
fwrite($f, "define('G5_MYSQL_USER', '".addcslashes($mysql_user, "\\'")."');\n");
fwrite($f, "define('G5_MYSQL_PASSWORD', '".addcslashes($mysql_pass, "\\'")."');\n");
fwrite($f, "define('G5_MYSQL_DB', '".addcslashes($mysql_db, "\\'")."');\n");
fwrite($f, "define('G5_MYSQL_SET_MODE', {$mysql_set_mode});\n\n");
fwrite($f, "define('G5_TABLE_PREFIX', '{$table_prefix}');\n\n");
fwrite($f, "\$g5['write_prefix'] = G5_TABLE_PREFIX.'write_'; // 게시판 테이블명 접두사\n\n");
fwrite($f, "\$g5['auth_table'] = G5_TABLE_PREFIX.'auth'; // 관리권한 설정 테이블\n");
fwrite($f, "\$g5['config_table'] = G5_TABLE_PREFIX.'config'; // 기본환경 설정 테이블\n");
fwrite($f, "\$g5['group_table'] = G5_TABLE_PREFIX.'group'; // 게시판 그룹 테이블\n");
fwrite($f, "\$g5['group_member_table'] = G5_TABLE_PREFIX.'group_member'; // 게시판 그룹+회원 테이블\n");
fwrite($f, "\$g5['board_table'] = G5_TABLE_PREFIX.'board'; // 게시판 설정 테이블\n");
fwrite($f, "\$g5['board_file_table'] = G5_TABLE_PREFIX.'board_file'; // 게시판 첨부파일 테이블\n");
fwrite($f, "\$g5['board_good_table'] = G5_TABLE_PREFIX.'board_good'; // 게시물 추천,비추천 테이블\n");
fwrite($f, "\$g5['board_new_table'] = G5_TABLE_PREFIX.'board_new'; // 게시판 새글 테이블\n");
fwrite($f, "\$g5['login_table'] = G5_TABLE_PREFIX.'login'; // 로그인 테이블 (접속자수)\n");
fwrite($f, "\$g5['mail_table'] = G5_TABLE_PREFIX.'mail'; // 회원메일 테이블\n");
fwrite($f, "\$g5['member_table'] = G5_TABLE_PREFIX.'member'; // 회원 테이블\n");
fwrite($f, "\$g5['memo_table'] = G5_TABLE_PREFIX.'memo'; // 메모 테이블\n");
fwrite($f, "\$g5['poll_table'] = G5_TABLE_PREFIX.'poll'; // 투표 테이블\n");
fwrite($f, "\$g5['poll_etc_table'] = G5_TABLE_PREFIX.'poll_etc'; // 투표 기타의견 테이블\n");
fwrite($f, "\$g5['point_table'] = G5_TABLE_PREFIX.'point'; // 포인트 테이블\n");
fwrite($f, "\$g5['popular_table'] = G5_TABLE_PREFIX.'popular'; // 인기검색어 테이블\n");
fwrite($f, "\$g5['scrap_table'] = G5_TABLE_PREFIX.'scrap'; // 게시글 스크랩 테이블\n");
fwrite($f, "\$g5['visit_table'] = G5_TABLE_PREFIX.'visit'; // 방문자 테이블\n");
fwrite($f, "\$g5['visit_sum_table'] = G5_TABLE_PREFIX.'visit_sum'; // 방문자 합계 테이블\n");
fwrite($f, "\$g5['uniqid_table'] = G5_TABLE_PREFIX.'uniqid'; // 유니크한 값을 만드는 테이블\n");
fwrite($f, "\$g5['autosave_table'] = G5_TABLE_PREFIX.'autosave'; // 게시글 작성시 일정시간마다 글을 임시 저장하는 테이블\n");
fwrite($f, "\$g5['cert_history_table'] = G5_TABLE_PREFIX.'cert_history'; // 인증내역 테이블\n");
fwrite($f, "\$g5['qa_config_table'] = G5_TABLE_PREFIX.'qa_config'; // 1:1문의 설정테이블\n");
fwrite($f, "\$g5['qa_content_table'] = G5_TABLE_PREFIX.'qa_content'; // 1:1문의 테이블\n");
fwrite($f, "\$g5['content_table'] = G5_TABLE_PREFIX.'content'; // 내용(컨텐츠)정보 테이블\n");
fwrite($f, "\$g5['faq_table'] = G5_TABLE_PREFIX.'faq'; // 자주하시는 질문 테이블\n");
fwrite($f, "\$g5['faq_master_table'] = G5_TABLE_PREFIX.'faq_master'; // 자주하시는 질문 마스터 테이블\n");
fwrite($f, "\$g5['new_win_table'] = G5_TABLE_PREFIX.'new_win'; // 새창 테이블\n");
fwrite($f, "\$g5['menu_table'] = G5_TABLE_PREFIX.'menu'; // 메뉴관리 테이블\n");
fwrite($f, "\$g5['social_profile_table'] = G5_TABLE_PREFIX.'member_social_profiles'; // 소셜 로그인 테이블\n");
fwrite($f, "?>");

fclose($f);
@chmod($file, G5_FILE_PERMISSION);
?>

        <li>DB설정 파일 생성 완료 (<?php echo $file ?>)</li>

<?php
// data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl 파일을 실행할수 없게함.
$f = fopen($data_path.'/.htaccess', 'w');
$str = <<<EOD
<FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp][Hh][Tt]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll])">
Order allow,deny
Deny from all
</FilesMatch>
EOD;
fwrite($f, $str);
fclose($f);
//-------------------------------------------------------------------------------------------------
?>
    </ol>

    <p>축하합니다. <?php echo G5_VERSION ?> 설치가 완료되었습니다.</p>

</div>

<div class="ins_inner">

    <h2>환경설정 변경은 다음의 과정을 따르십시오.</h2>

    <ol>
        <li>메인화면으로 이동</li>
        <li>관리자 로그인</li>
        <li>관리자 모드 접속</li>
        <li>환경설정 메뉴의 기본환경설정 페이지로 이동</li>
    </ol>

    <div class="inner_btn">
        <a href="../index.php">새로운 그누보드5로 이동</a>
    </div>

</div>

<?php
include_once ('./install.inc2.php');