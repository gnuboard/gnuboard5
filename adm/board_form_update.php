<?
$sub_menu = "300100";
include_once("./_common.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], "w");

if ($member[mb_password] != sql_password($_POST['admin_password'])) {
    alert("패스워드가 다릅니다.");
}

if (!$_POST[gr_id]) { alert("그룹 ID는 반드시 선택하세요."); }
if (!$bo_table) { alert("게시판 TABLE명은 반드시 입력하세요."); }
if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) { alert("게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)"); }
if (!$_POST[bo_subject]) { alert("게시판 제목을 입력하세요."); }

if ($img = $_FILES[bo_image_head][name]) {
    if (!preg_match("/\.(gif|jpg|png)$/i", $img)) {
        alert("상단 이미지가 gif, jpg, png 파일이 아닙니다.");
    }
}

if ($img = $_FILES[bo_image_tail][name]) {
    if (!preg_match("/\.(gif|jpg|png)$/i", $img)) {
        alert("하단 이미지가 gif, jpg, png 파일이 아닙니다.");
    }
}

if ($file = $_POST[bo_include_head]) {
    if (!preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("상단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($file = $_POST[bo_include_tail]) {
    if (!preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("하단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

check_token();

$board_path = "$g4[path]/data/file/$bo_table";

// 게시판 디렉토리 생성
@mkdir($board_path, 0707);
@chmod($board_path, 0707);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . "/index.php";
$f = @fopen($file, "w");
@fwrite($f, "");
@fclose($f);
@chmod($file, 0606);

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓'); 
$bo_category_list = str_replace($src_char, $dst_char, $bo_category_list);

$sql_common = " gr_id               = '$_POST[gr_id]',
                bo_subject          = '$_POST[bo_subject]',
                bo_admin            = '$_POST[bo_admin]',
                bo_list_level       = '$_POST[bo_list_level]',
                bo_read_level       = '$_POST[bo_read_level]',
                bo_write_level      = '$_POST[bo_write_level]',
                bo_reply_level      = '$_POST[bo_reply_level]',
                bo_comment_level    = '$_POST[bo_comment_level]',
                bo_html_level       = '$_POST[bo_html_level]',
                bo_link_level       = '$_POST[bo_link_level]',
                bo_trackback_level  = '$_POST[bo_trackback_level]',
                bo_count_modify     = '$_POST[bo_count_modify]',
                bo_count_delete     = '$_POST[bo_count_delete]',
                bo_upload_level     = '$_POST[bo_upload_level]',
                bo_download_level   = '$_POST[bo_download_level]',
                bo_read_point       = '$_POST[bo_read_point]',
                bo_write_point      = '$_POST[bo_write_point]',
                bo_comment_point    = '$_POST[bo_comment_point]',
                bo_download_point   = '$_POST[bo_download_point]',
                bo_use_category     = '$_POST[bo_use_category]',
                bo_category_list    = '$_POST[bo_category_list]',
                bo_disable_tags     = '$_POST[bo_disable_tags]',
                bo_use_sideview     = '$_POST[bo_use_sideview]',
                bo_use_file_content = '$_POST[bo_use_file_content]',
                bo_use_secret       = '$_POST[bo_use_secret]',
                bo_use_dhtml_editor = '$_POST[bo_use_dhtml_editor]',
                bo_use_rss_view     = '$_POST[bo_use_rss_view]',
                bo_use_comment      = '$_POST[bo_use_comment]',
                bo_use_good         = '$_POST[bo_use_good]',
                bo_use_nogood       = '$_POST[bo_use_nogood]',
                bo_use_name         = '$_POST[bo_use_name]',
                bo_use_signature    = '$_POST[bo_use_signature]',
                bo_use_ip_view      = '$_POST[bo_use_ip_view]',
                bo_use_trackback    = '$_POST[bo_use_trackback]',
                bo_use_list_view    = '$_POST[bo_use_list_view]',
                bo_use_list_content = '$_POST[bo_use_list_content]',
                bo_use_email        = '$_POST[bo_use_email]',
                bo_table_width      = '$_POST[bo_table_width]',
                bo_subject_len      = '$_POST[bo_subject_len]',
                bo_page_rows        = '$_POST[bo_page_rows]',
                bo_new              = '$_POST[bo_new]',
                bo_hot              = '$_POST[bo_hot]',
                bo_image_width      = '$_POST[bo_image_width]',
                bo_skin             = '$_POST[bo_skin]',
                bo_include_head     = '$_POST[bo_include_head]',
                bo_include_tail     = '$_POST[bo_include_tail]',
                bo_content_head     = '$_POST[bo_content_head]',
                bo_content_tail     = '$_POST[bo_content_tail]',
                bo_insert_content   = '$_POST[bo_insert_content]',
                bo_gallery_cols     = '$_POST[bo_gallery_cols]',
                bo_upload_count     = '$_POST[bo_upload_count]',
                bo_upload_size      = '$_POST[bo_upload_size]',
                bo_reply_order      = '$_POST[bo_reply_order]',
                bo_use_search       = '$_POST[bo_use_search]',
                bo_order_search     = '$_POST[bo_order_search]',
                bo_write_min        = '$_POST[bo_write_min]',
                bo_write_max        = '$_POST[bo_write_max]',
                bo_comment_min      = '$_POST[bo_comment_min]',
                bo_comment_max      = '$_POST[bo_comment_max]',
                bo_sort_field       = '$_POST[bo_sort_field]',
                bo_1_subj           = '$_POST[bo_1_subj]',
                bo_2_subj           = '$_POST[bo_2_subj]',
                bo_3_subj           = '$_POST[bo_3_subj]',
                bo_4_subj           = '$_POST[bo_4_subj]',
                bo_5_subj           = '$_POST[bo_5_subj]',
                bo_6_subj           = '$_POST[bo_6_subj]',
                bo_7_subj           = '$_POST[bo_7_subj]',
                bo_8_subj           = '$_POST[bo_8_subj]',
                bo_9_subj           = '$_POST[bo_9_subj]',
                bo_10_subj          = '$_POST[bo_10_subj]',
                bo_1                = '$_POST[bo_1]',
                bo_2                = '$_POST[bo_2]',
                bo_3                = '$_POST[bo_3]',
                bo_4                = '$_POST[bo_4]',
                bo_5                = '$_POST[bo_5]',
                bo_6                = '$_POST[bo_6]',
                bo_7                = '$_POST[bo_7]',
                bo_8                = '$_POST[bo_8]',
                bo_9                = '$_POST[bo_9]',
                bo_10               = '$_POST[bo_10]' ";

if ($bo_image_head_del) {
    @unlink("$board_path/$bo_image_head_del");
    $sql_common .= " , bo_image_head = '' ";
}

if ($bo_image_tail_del) {
    @unlink("$board_path/$bo_image_tail_del");
    $sql_common .= " , bo_image_tail = '' ";
}

if ($_FILES[bo_image_head][name]) {
    //$bo_image_head_urlencode = urlencode($_FILES[bo_image_head][name]);
    $bo_image_head_urlencode = $bo_table."_head_".time();
    $sql_common .= " , bo_image_head = '$bo_image_head_urlencode' ";
}

if ($_FILES[bo_image_tail][name]) {
    //$bo_image_tail_urlencode = urlencode($_FILES[bo_image_tail][name]);
    $bo_image_tail_urlencode = $bo_table."_tail_".time();
    $sql_common .= " , bo_image_tail = '$bo_image_tail_urlencode' ";
}

if ($w == "") {
    $row = sql_fetch(" select count(*) as cnt from $g4[board_table] where bo_table = '$bo_table' ");
    if ($row[cnt])
        alert("{$bo_table} 은(는) 이미 존재하는 TABLE 입니다.");

    $sql = " insert into $g4[board_table]
                set bo_table = '$bo_table',
                    bo_count_write = '0',
                    bo_count_comment = '0',
                    $sql_common ";
    sql_query($sql);

    // 게시판 테이블 생성
    $file = file("./sql_write.sql");
    $sql = implode($file, "\n");

    $create_table = $g4[write_prefix] . $bo_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array("/__TABLE_NAME__/", "/;/");
    $target = array($create_table, "");
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, FALSE);
} else if ($w == "u") {
    // 게시판의 글 수
    $sql = " select count(*) as cnt from $g4[write_prefix]$bo_table where wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $bo_count_write = $row[cnt];

    // 게시판의 코멘트 수
    $sql = " select count(*) as cnt from $g4[write_prefix]$bo_table where wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    $bo_count_comment = $row[cnt];

    // 글수 조정
    if ($proc_count) {
        // 원글을 얻습니다.
        $sql = " select wr_id from $g4[write_prefix]$bo_table where wr_is_comment = 0 ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 코멘트수를 얻습니다.
            $sql2 = " select count(*) as cnt from $g4[write_prefix]$bo_table where wr_parent = '$row[wr_id]' and wr_is_comment = 1 ";
            $row2 = sql_fetch($sql2);

            sql_query(" update $g4[write_prefix]$bo_table set wr_comment = '$row2[cnt]' where wr_id = '$row[wr_id]' ");
        }
    }

    // 공지사항에는 등록되어 있지만 실제 존재하지 않는 글 아이디는 삭제합니다.
    $bo_notice = "";
    $lf = "";
    if ($board[bo_notice]) {
        $tmp_array = explode("\n", $board[bo_notice]);
        for ($i=0; $i<count($tmp_array); $i++) {
            $tmp_wr_id = trim($tmp_array[$i]);
            $row = sql_fetch(" select count(*) as cnt from $g4[write_prefix]$bo_table where wr_id = '$tmp_wr_id' ");
            if ($row[cnt]) 
            {
                $bo_notice .= $lf . $tmp_wr_id;
                $lf = "\n";
            }
        }
    }

    $sql = " update $g4[board_table]
                set bo_notice = '$bo_notice',
                    bo_count_write = '$bo_count_write',
                    bo_count_comment = '$bo_count_comment',
                    $sql_common
              where bo_table = '$bo_table' ";
    sql_query($sql);
}


// 같은 그룹내 게시판 동일 옵션 적용
$s = "";
if ($chk_admin) $s .= " , bo_admin = '$bo_admin' ";
if ($chk_list_level) $s .= " , bo_list_level = '$bo_list_level' ";
if ($chk_read_level) $s .= " , bo_read_level = '$bo_read_level' ";
if ($chk_write_level) $s .= " , bo_write_level = '$bo_write_level' ";
if ($chk_reply_level) $s .= " , bo_reply_level = '$bo_reply_level' ";
if ($chk_comment_level) $s .= " , bo_comment_level = '$bo_comment_level' ";
if ($chk_link_level) $s .= " , bo_link_level = '$bo_link_level' ";
if ($chk_upload_level) $s .= " , bo_upload_level = '$bo_upload_level' ";
if ($chk_download_level) $s .= " , bo_download_level = '$bo_download_level' ";
if ($chk_html_level) $s .= " , bo_html_level = '$bo_html_level' ";
if ($chk_trackback_level) $s .= " , bo_trackback_level = '$bo_trackback_level' ";
if ($chk_count_modify) $s .= " , bo_count_modify = '$bo_count_modify' ";
if ($chk_count_delete) $s .= " , bo_count_delete = '$bo_count_delete' ";
if ($chk_read_point) $s .= " , bo_read_point = '$bo_read_point' ";
if ($chk_write_point) $s .= " , bo_write_point = '$bo_write_point' ";
if ($chk_comment_point) $s .= " , bo_comment_point = '$bo_comment_point' ";
if ($chk_download_point) $s .= " , bo_download_point = '$bo_download_point' ";
if ($chk_category_list) {
    $s .= " , bo_category_list = '$bo_category_list' ";
    $s .= " , bo_use_category = '$bo_use_category' ";
}
if ($chk_use_sideview) $s .= " , bo_use_sideview = '$bo_use_sideview' ";
if ($chk_use_file_content) $s .= " , bo_use_file_content = '$bo_use_file_content' ";
if ($chk_use_comment) $s .= " , bo_use_comment = '$bo_use_comment' ";
if ($chk_use_secret) $s .= " , bo_use_secret = '$bo_use_secret' ";
if ($chk_use_dhtml_editor) $s .= " , bo_use_dhtml_editor = '$bo_use_dhtml_editor' ";
if ($chk_use_rss_view) $s .= " , bo_use_rss_view = '$bo_use_rss_view' ";
if ($chk_use_good) $s .= " , bo_use_good = '$bo_use_good' ";
if ($chk_use_nogood) $s .= " , bo_use_nogood = '$bo_use_nogood' ";
if ($chk_use_name) $s .= " , bo_use_name = '$bo_use_name' ";
if ($chk_use_signature) $s .= " , bo_use_signature = '$bo_use_signature' ";
if ($chk_use_ip_view) $s .= " , bo_use_ip_view = '$bo_use_ip_view' ";
if ($chk_use_trackback) $s .= " , bo_use_trackback = '$bo_use_trackback' ";
if ($chk_use_list_view) $s .= " , bo_use_list_view = '$bo_use_list_view' ";
if ($chk_use_list_content) $s .= " , bo_use_list_content = '$bo_use_list_content' ";
if ($chk_use_email) $s .= " , bo_use_email = '$bo_use_email' ";
if ($chk_skin) $s .= " , bo_skin = '$bo_skin' ";
if ($chk_gallery_cols) $s .= " , bo_gallery_cols = '$bo_gallery_cols' ";
if ($chk_table_width) $s .= " , bo_table_width = '$bo_table_width' ";
if ($chk_page_rows) $s .= " , bo_page_rows = '$bo_page_rows' ";
if ($chk_subject_len) $s .= " , bo_subject_len = '$bo_subject_len' ";
if ($chk_new) $s .= " , bo_new = '$bo_new' ";
if ($chk_hot) $s .= " , bo_hot = '$bo_hot' ";
if ($chk_image_width) $s .= " , bo_image_width = '$bo_image_width' ";
if ($chk_reply_order) $s .= " , bo_reply_order = '$bo_reply_order' ";
if ($chk_disable_tags) $s .= " , bo_disable_tags = '$bo_disable_tags' ";
if ($chk_sort_field) $s .= " , bo_sort_field = '$bo_sort_field' ";
if ($chk_write_min) $s .= " , bo_write_min = '$bo_write_min' ";
if ($chk_write_max) $s .= " , bo_write_max = '$bo_write_max' ";
if ($chk_comment_min) $s .= " , bo_comment_min = '$bo_comment_min' ";
if ($chk_comment_max) $s .= " , bo_comment_max = '$bo_comment_max' ";
if ($chk_upload_count) $s .= " , bo_upload_count = '$bo_upload_count' ";
if ($chk_upload_size) $s .= " , bo_upload_size = '$bo_upload_size' ";
if ($chk_include_head) $s .= " , bo_include_head = '$bo_include_head' ";
if ($chk_include_tail) $s .= " , bo_include_tail = '$bo_include_tail' ";
if ($chk_content_head) $s .= " , bo_content_head = '$bo_content_head' ";
if ($chk_content_tail) $s .= " , bo_content_tail = '$bo_content_tail' ";
if ($chk_insert_content) $s .= " , bo_insert_content = '$bo_insert_content' ";
if ($chk_use_search) $s .= " , bo_use_search = '$bo_use_search' ";
if ($chk_order_search) $s .= " , bo_order_search = '$bo_order_search' ";
for ($i=1; $i<=10; $i++) {
    if ($_POST["chk_{$i}"]) {
        $s .= " , bo_{$i}_subj = '".$_POST["bo_{$i}_subj"]."' ";
        $s .= " , bo_{$i} = '".$_POST["bo_{$i}"]."' ";
    }
}

if ($s) {
        $sql = " update $g4[board_table]
                    set bo_table = bo_table
                        {$s}
                  where gr_id = '$gr_id' ";
        sql_query($sql);
}


if ($_FILES[bo_image_head][name]) { 
    $bo_image_head_path = "$board_path/$bo_image_head_urlencode";
    move_uploaded_file($_FILES[bo_image_head][tmp_name], $bo_image_head_path);
    chmod($bo_image_head_path, 0606);
}

if ($_FILES[bo_image_tail][name]) { 
    $bo_image_tail_path = "$board_path/$bo_image_tail_urlencode";
    move_uploaded_file($_FILES[bo_image_tail][tmp_name], $bo_image_tail_path);
    chmod($bo_image_tail_path, 0606);
}

goto_url("./board_form.php?w=u&bo_table=$bo_table&$qstr");
?>
