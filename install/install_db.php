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

$g5_path['path'] = '..';
include_once('../config.php');
include_once('../lib/common.lib.php');
include_once('./install.function.php');    // 인스톨 과정 함수 모음

include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');
include_once('../lib/uri.lib.php');    // URL 함수 파일
include_once('../lib/cache.lib.php');

$title = G5_VERSION." 설치 완료 3/3";
include_once('./install.inc.php');

$tmp_bo_table   = array ("notice", "qa", "free", "gallery");


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

$g5_install = isset($_POST['g5_install']) ? (int) $_POST['g5_install'] : 0;
$g5_shop_prefix = isset($_POST['g5_shop_prefix']) ? safe_install_string_check($_POST['g5_shop_prefix']) : 'yc5_';
$g5_shop_install = isset($_POST['g5_shop_install']) ? (int) $_POST['g5_shop_install'] : 0;

$dblink = sql_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if (!$dblink) {
?>

<div class="ins_inner">
    <p>MySQL Host, User, Password 를 확인해 주십시오.</p>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>

<?php
    include_once('./install.inc2.php');
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
    include_once('./install.inc2.php');
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
$sql = "SHOW TABLES LIKE '{$table_prefix}config'";
$is_install = sql_query($sql, false, $dblink)->num_rows > 0;

// 그누보드5 재설치에 체크하였거나 그누보드5가 설치되어 있지 않다면
if ($g5_install || $is_install === false) {
    // 테이블 생성 ------------------------------------
    $file = implode('', file('./gnuboard5.sql'));
    eval("\$file = \"$file\";");

    $file = preg_replace('/^--.*$/m', '', $file);
    $file = preg_replace('/`g5_([^`]+`)/', '`'.$table_prefix.'$1', $file);
    $f = explode(';', $file);
    for ($i=0; $i<count($f); $i++) {
        if (trim($f[$i]) == '') {
            continue;
        }

        $sql = get_db_create_replace($f[$i]);
        sql_query($sql, true, $dblink);
    }
}

// 쇼핑몰 테이블 생성 -----------------------------
if($g5_shop_install) {
    $file = implode('', file('./gnuboard5shop.sql'));

    $file = preg_replace('/^--.*$/m', '', $file);
    $file = preg_replace('/`g5_shop_([^`]+`)/', '`'.$g5_shop_prefix.'$1', $file);
    $f = explode(';', $file);
    for ($i=0; $i<count($f); $i++) {
        if (trim($f[$i]) == '') {
            continue;
        }

        $sql = get_db_create_replace($f[$i]);
        sql_query($sql, true, $dblink);
    }
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
if ($g5_install || $is_install === false) {
    // 기본 이미지 확장자를 설정하고
    $image_extension = "gif|jpg|jpeg|png";
    // 서버에서 webp 를 지원하면 확장자를 추가한다.
    if (function_exists("imagewebp")) {
        $image_extension .= "|webp";
    }

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
                    cf_image_extension = '{$image_extension}',
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
    sql_query(" insert into `{$table_prefix}content` set co_id = 'company', co_html = '1', co_subject = '회사소개', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b></p>', co_skin = 'basic', co_mobile_skin = 'basic' ", true, $dblink);
    sql_query(" insert into `{$table_prefix}content` set co_id = 'privacy', co_html = '1', co_subject = '개인정보 처리방침', co_content= '<p align=center><b>개인정보 처리방침에 대한 내용을 입력하십시오.</b></p>', co_skin = 'basic', co_mobile_skin = 'basic' ", true, $dblink);
    sql_query(" insert into `{$table_prefix}content` set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.</b></p>', co_skin = 'basic', co_mobile_skin = 'basic' ", true, $dblink);

    // FAQ Master
    sql_query(" insert into `{$table_prefix}faq_master` set fm_id = '1', fm_subject = '자주하시는 질문' ", true, $dblink);

    // 그누보드, 영카트 통합으로 인하여 게시판그룹을 커뮤니티(community)로 생성 (NaviGator님,210624)
    // $tmp_gr_id = defined('G5_YOUNGCART_VER') ? 'shop' : 'community';
    // $tmp_gr_subject = defined('G5_YOUNGCART_VER') ? '쇼핑몰' : '커뮤니티';
    $tmp_gr_id = 'community';
    $tmp_gr_subject = '커뮤니티';

    // 게시판 그룹 생성
    sql_query(" insert into `{$table_prefix}group` set gr_id = '$tmp_gr_id', gr_subject = '$tmp_gr_subject' ", true, $dblink);

    // 게시판 생성
    $tmp_bo_subject = array ("공지사항", "질문답변", "자유게시판", "갤러리");
    for ($i=0; $i<count($tmp_bo_table); $i++)
    {

        $bo_skin = ($tmp_bo_table[$i] === 'gallery') ? 'gallery' : 'basic';

        if (in_array($tmp_bo_table[$i], array('gallery', 'qa'))) {
            $read_bo_point = -1;
            $write_bo_point = 5;
            $comment_bo_point = 1;
            $download_bo_point = -20;
        } else {
            $read_bo_point = $read_point;
            $write_bo_point = $write_point;
            $comment_bo_point = $comment_point;
            $download_bo_point = $download_point;
        }

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
                        bo_read_point       = '$read_bo_point',
                        bo_write_point      = '$write_bo_point',
                        bo_comment_point    = '$comment_bo_point',
                        bo_download_point   = '$download_bo_point',
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
}

if($g5_shop_install) {
    // 이미지 사이즈
    $ssimg_width = 160;
    $ssimg_height = 160;
    $simg_width = 215;
    $simg_height = 215;
    $mimg_width = 230;
    $mimg_height = 230;
    $mmimg_width = 300;
    $mmimg_height = 300;
    $msimg_width = 80;
    $msimg_height = 80;
    $list_img_width = 225;
    $list_img_height = 225;

    // default 설정 (쇼핑몰 설정)
    $sql = " insert into `{$g5_shop_prefix}default`
                set de_admin_company_name = '회사명',
                    de_admin_company_saupja_no = '123-45-67890',
                    de_admin_company_owner = '대표자명',
                    de_admin_company_tel = '02-123-4567',
                    de_admin_company_fax = '02-123-4568',
                    de_admin_tongsin_no = '제 OO구 - 123호',
                    de_admin_buga_no = '12345호',
                    de_admin_company_zip = '123-456',
                    de_admin_company_addr = 'OO도 OO시 OO구 OO동 123-45',
                    de_admin_info_name = '정보책임자명',
                    de_admin_info_email = '정보책임자 E-mail',
                    de_shop_skin = 'basic',
                    de_shop_mobile_skin = 'basic',
                    de_type1_list_use = '1',
                    de_type1_list_skin = 'main.10.skin.php',
                    de_type1_list_mod = '5',
                    de_type1_list_row = '1',
                    de_type1_img_width = '$ssimg_width',
                    de_type1_img_height = '$ssimg_height',
                    de_type2_list_use = '1',
                    de_type2_list_skin = 'main.20.skin.php',
                    de_type2_list_mod = '4',
                    de_type2_list_row = '1',
                    de_type2_img_width = '$simg_width',
                    de_type2_img_height = '$simg_height',
                    de_type3_list_use = '1',
                    de_type3_list_skin = 'main.40.skin.php',
                    de_type3_list_mod = '4',
                    de_type3_list_row = '1',
                    de_type3_img_width = '$simg_width',
                    de_type3_img_height = '$simg_height',
                    de_type4_list_use = '1',
                    de_type4_list_skin = 'main.50.skin.php',
                    de_type4_list_mod = '5',
                    de_type4_list_row = '1',
                    de_type4_img_width = '$simg_width',
                    de_type4_img_height = '$simg_height',
                    de_type5_list_use = '1',
                    de_type5_list_skin = 'main.30.skin.php',
                    de_type5_list_mod = '4',
                    de_type5_list_row = '1',
                    de_type5_img_width = '$simg_width',
                    de_type5_img_height = '$simg_height',
                    de_mobile_type1_list_use = '1',
                    de_mobile_type1_list_skin = 'main.30.skin.php',
                    de_mobile_type1_list_mod = '2',
                    de_mobile_type1_list_row = '4',
                    de_mobile_type1_img_width = '$mimg_width',
                    de_mobile_type1_img_height = '$mimg_height',
                    de_mobile_type2_list_use = '1',
                    de_mobile_type2_list_skin = 'main.10.skin.php',
                    de_mobile_type2_list_mod = '2',
                    de_mobile_type2_list_row = '2',
                    de_mobile_type2_img_width = '$mimg_width',
                    de_mobile_type2_img_height = '$mimg_height',
                    de_mobile_type3_list_use = '1',
                    de_mobile_type3_list_skin = 'main.10.skin.php',
                    de_mobile_type3_list_mod = '2',
                    de_mobile_type3_list_row = '4',
                    de_mobile_type3_img_width = '$mmimg_width',
                    de_mobile_type3_img_height = '$mmimg_height',
                    de_mobile_type4_list_use = '1',
                    de_mobile_type4_list_skin = 'main.20.skin.php',
                    de_mobile_type4_list_mod = '2',
                    de_mobile_type4_list_row = '2',
                    de_mobile_type4_img_width = '$msimg_width',
                    de_mobile_type4_img_height = '$msimg_height',
                    de_mobile_type5_list_use = '1',
                    de_mobile_type5_list_skin = 'main.10.skin.php',
                    de_mobile_type5_list_mod = '2',
                    de_mobile_type5_list_row = '2',
                    de_mobile_type5_img_width = '$mimg_width',
                    de_mobile_type5_img_height = '$mimg_height',
                    de_bank_use = '1',
                    de_bank_account = 'OO은행 12345-67-89012 예금주명',
                    de_vbank_use = '0',
                    de_iche_use = '0',
                    de_card_use = '0',
                    de_settle_min_point = '5000',
                    de_settle_max_point = '50000',
                    de_settle_point_unit = '100',
                    de_cart_keep_term = '15',
                    de_card_point = '0',
                    de_point_days = '7',
                    de_pg_service = 'kcp',
                    de_kcp_mid = '',
                    de_send_cost_case = '차등',
                    de_send_cost_limit = '20000;30000;40000',
                    de_send_cost_list = '4000;3000;2000',
                    de_hope_date_use = '0',
                    de_hope_date_after = '3',
                    de_baesong_content = '배송 안내 입력전입니다.',
                    de_change_content = '교환/반품 안내 입력전입니다.',
                    de_rel_list_use = '1',
                    de_rel_list_skin = 'relation.10.skin.php',
                    de_rel_list_mod = '5',
                    de_rel_img_width = '$simg_width',
                    de_rel_img_height = '$simg_height',
                    de_mobile_rel_list_use = '1',
                    de_mobile_rel_list_skin = 'relation.10.skin.php',
                    de_mobile_rel_list_mod = '3',
                    de_mobile_rel_img_width = '$mimg_width',
                    de_mobile_rel_img_height = '$mimg_height',
                    de_search_list_skin = 'list.10.skin.php',
                    de_search_img_width = '$list_img_width',
                    de_search_img_height = '$list_img_height',
                    de_search_list_mod = '5',
                    de_search_list_row = '5',
                    de_mobile_search_list_skin = 'list.10.skin.php',
                    de_mobile_search_img_width = '$mimg_width',
                    de_mobile_search_img_height = '$mimg_height',
                    de_mobile_search_list_mod = '2',
                    de_mobile_search_list_row = '5',
                    de_listtype_list_skin = 'list.10.skin.php',
                    de_listtype_img_width = '$list_img_width',
                    de_listtype_img_height = '$list_img_height',
                    de_listtype_list_mod = '5',
                    de_listtype_list_row = '5',
                    de_mobile_listtype_list_skin = 'list.10.skin.php',
                    de_mobile_listtype_img_width = '$mimg_width',
                    de_mobile_listtype_img_height = '$mimg_height',
                    de_mobile_listtype_list_mod = '2',
                    de_mobile_listtype_list_row = '5',
                    de_simg_width = '$mimg_width',
                    de_simg_height = '$mimg_height',
                    de_mimg_width = '$mmimg_width',
                    de_mimg_height = '$mmimg_height',
                    de_item_use_use = '1',
                    de_level_sell = '1',
                    de_code_dup_use = '1',
                    de_card_test = '1',
                    de_sms_cont1 = '{이름}님의 회원가입을 축하드립니다.\nID:{회원아이디}\n{회사명}',
                    de_sms_cont2 = '{이름}님 주문해주셔서 고맙습니다.\n{주문번호}\n{주문금액}원\n{회사명}',
                    de_sms_cont3 = '{이름}님께서 주문하셨습니다.\n{주문번호}\n{주문금액}원\n{회사명}',
                    de_sms_cont4 = '{이름}님 입금 감사합니다.\n{입금액}원\n주문번호:\n{주문번호}\n{회사명}',
                    de_sms_cont5 = '{이름}님 배송합니다.\n택배:{택배회사}\n운송장번호:\n{운송장번호}\n{회사명}'
                    ";
    sql_query($sql, true, $dblink);
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

// 게시판 디렉토리 생성 (작은별님,211206)
for ($i=0; $i<count($tmp_bo_table); $i++) {
    $board_dir = $data_path.'/file/'.$tmp_bo_table[$i];
    @mkdir($board_dir, G5_DIR_PERMISSION);
    @chmod($board_dir, G5_DIR_PERMISSION);
}

if($g5_shop_install) {
    $dir_arr = array (
        $data_path.'/banner',
        $data_path.'/common',
        $data_path.'/event',
        $data_path.'/item'
    );

    for ($i=0; $i<count($dir_arr); $i++) {
        @mkdir($dir_arr[$i], G5_DIR_PERMISSION);
        @chmod($dir_arr[$i], G5_DIR_PERMISSION);
    }
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
fwrite($f, "define('G5_TOKEN_ENCRYPTION_KEY', '".get_random_token_string(16)."'); // 토큰 암호화에 사용할 키\n\n");
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
fwrite($f, "\$g5['member_cert_history_table'] = G5_TABLE_PREFIX.'member_cert_history'; // 본인인증 변경내역 테이블\n");

if($g5_shop_install) {
    fwrite($f, "\n");
    fwrite($f, "define('G5_USE_SHOP', true);\n\n");
    fwrite($f, "define('G5_SHOP_TABLE_PREFIX', '{$g5_shop_prefix}');\n\n");
    fwrite($f, "\$g5['g5_shop_default_table'] = G5_SHOP_TABLE_PREFIX.'default'; // 쇼핑몰설정 테이블\n");
    fwrite($f, "\$g5['g5_shop_banner_table'] = G5_SHOP_TABLE_PREFIX.'banner'; // 배너 테이블\n");
    fwrite($f, "\$g5['g5_shop_cart_table'] = G5_SHOP_TABLE_PREFIX.'cart'; // 장바구니 테이블\n");
    fwrite($f, "\$g5['g5_shop_category_table'] = G5_SHOP_TABLE_PREFIX.'category'; // 상품분류 테이블\n");
    fwrite($f, "\$g5['g5_shop_event_table'] = G5_SHOP_TABLE_PREFIX.'event'; // 이벤트 테이블\n");
    fwrite($f, "\$g5['g5_shop_event_item_table'] = G5_SHOP_TABLE_PREFIX.'event_item'; // 상품, 이벤트 연결 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_table'] = G5_SHOP_TABLE_PREFIX.'item'; // 상품 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_option_table'] = G5_SHOP_TABLE_PREFIX.'item_option'; // 상품옵션 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_use_table'] = G5_SHOP_TABLE_PREFIX.'item_use'; // 상품 사용후기 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_qa_table'] = G5_SHOP_TABLE_PREFIX.'item_qa'; // 상품 질문답변 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_relation_table'] = G5_SHOP_TABLE_PREFIX.'item_relation'; // 관련 상품 테이블\n");
    fwrite($f, "\$g5['g5_shop_order_table'] = G5_SHOP_TABLE_PREFIX.'order'; // 주문서 테이블\n");
    fwrite($f, "\$g5['g5_shop_order_delete_table'] = G5_SHOP_TABLE_PREFIX.'order_delete'; // 주문서 삭제 테이블\n");
    fwrite($f, "\$g5['g5_shop_wish_table'] = G5_SHOP_TABLE_PREFIX.'wish'; // 보관함(위시리스트) 테이블\n");
    fwrite($f, "\$g5['g5_shop_coupon_table'] = G5_SHOP_TABLE_PREFIX.'coupon'; // 쿠폰정보 테이블\n");
    fwrite($f, "\$g5['g5_shop_coupon_zone_table'] = G5_SHOP_TABLE_PREFIX.'coupon_zone'; // 쿠폰존 테이블\n");
    fwrite($f, "\$g5['g5_shop_coupon_log_table'] = G5_SHOP_TABLE_PREFIX.'coupon_log'; // 쿠폰사용정보 테이블\n");
    fwrite($f, "\$g5['g5_shop_sendcost_table'] = G5_SHOP_TABLE_PREFIX.'sendcost'; // 추가배송비 테이블\n");
    fwrite($f, "\$g5['g5_shop_personalpay_table'] = G5_SHOP_TABLE_PREFIX.'personalpay'; // 개인결제 정보 테이블\n");
    fwrite($f, "\$g5['g5_shop_order_address_table'] = G5_SHOP_TABLE_PREFIX.'order_address'; // 배송지이력 정보 테이블\n");
    fwrite($f, "\$g5['g5_shop_item_stocksms_table'] = G5_SHOP_TABLE_PREFIX.'item_stocksms'; // 재입고SMS 알림 정보 테이블\n");
    fwrite($f, "\$g5['g5_shop_post_log_table'] = G5_SHOP_TABLE_PREFIX.'order_post_log'; // 주문요청 로그 테이블\n");
    fwrite($f, "\$g5['g5_shop_order_data_table'] = G5_SHOP_TABLE_PREFIX.'order_data'; // 모바일 결제정보 임시저장 테이블\n");
    fwrite($f, "\$g5['g5_shop_inicis_log_table'] = G5_SHOP_TABLE_PREFIX.'inicis_log'; // 이니시스 모바일 계좌이체 로그 테이블\n");
}

fwrite($f, "?>");

fclose($f);
@chmod($file, G5_FILE_PERMISSION);
?>

        <li>DB설정 파일 생성 완료 (<?php echo $file ?>)</li>

<?php
// data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl .phar 파일을 실행할수 없게함.
$f = fopen($data_path.'/.htaccess', 'w');
$str = <<<EOD
<FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp][Hh][Tt]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll]|[Pp][Hh][Aa][Rr])">
Order allow,deny
Deny from all
</FilesMatch>
RedirectMatch 403 /session/.*
EOD;
fwrite($f, $str);
fclose($f);

if($g5_shop_install) {
    @copy('./logo_img', $data_path.'/common/logo_img');
    @copy('./logo_img', $data_path.'/common/logo_img2');
    @copy('./mobile_logo_img', $data_path.'/common/mobile_logo_img');
    @copy('./mobile_logo_img', $data_path.'/common/mobile_logo_img2');
}
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
