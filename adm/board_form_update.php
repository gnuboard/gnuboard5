<?
$sub_menu = "300100";
include_once('./_common.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

if ($_POST['admin_password']) {
    if ($member['mb_password'] != sql_password($_POST['admin_password'])) {
        alert('관리자 패스워드가 틀립니다.');
    }
} else {
    alert('관리자 패스워드를 입력하세요.');
}


if (!$_POST['gr_id']) { alert('그룹 ID는 반드시 선택하세요.'); }
if (!$bo_table) { alert('게시판 TABLE명은 반드시 입력하세요.'); }
if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) { alert('게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)'); }
if (!$_POST['bo_subject']) { alert('게시판 제목을 입력하세요.'); }

if ($file = $_POST['bo_include_head']) {
    if (!preg_match("/\.(php|htm['l']?)$/i", $file)) {
        alert('상단 파일 경로가 php, html 파일이 아닙니다.');
    }
}

if ($file = $_POST['bo_include_tail']) {
    if (!preg_match("/\.(php|htm['l']?)$/i", $file)) {
        alert('하단 파일 경로가 php, html 파일이 아닙니다.');
    }
}

$board_path = G4_DATA_PATH.'/file/'.$bo_table;

// 게시판 디렉토리 생성
@mkdir($board_path, 0707);
@chmod($board_path, 0707);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . '/index.php';
$f = @fopen($file, 'w');
@fwrite($f, '');
@fclose($f);
@chmod($file, 0606);

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓');
$bo_category_list = str_replace($src_char, $dst_char, $bo_category_list);

$sql_common = " gr_id               = '{$_POST['gr_id']}',
                bo_subject          = '{$_POST['bo_subject']}',
                bo_device        = '{$_POST['bo_device']}',
                bo_admin            = '{$_POST['bo_admin']}',
                bo_list_level       = '{$_POST['bo_list_level']}',
                bo_read_level       = '{$_POST['bo_read_level']}',
                bo_write_level      = '{$_POST['bo_write_level']}',
                bo_reply_level      = '{$_POST['bo_reply_level']}',
                bo_comment_level    = '{$_POST['bo_comment_level']}',
                bo_html_level       = '{$_POST['bo_html_level']}',
                bo_link_level       = '{$_POST['bo_link_level']}',
                bo_count_modify     = '{$_POST['bo_count_modify']}',
                bo_count_delete     = '{$_POST['bo_count_delete']}',
                bo_upload_level     = '{$_POST['bo_upload_level']}',
                bo_download_level   = '{$_POST['bo_download_level']}',
                bo_read_point       = '{$_POST['bo_read_point']}',
                bo_write_point      = '{$_POST['bo_write_point']}',
                bo_comment_point    = '{$_POST['bo_comment_point']}',
                bo_download_point   = '{$_POST['bo_download_point']}',
                bo_use_category     = '{$_POST['bo_use_category']}',
                bo_category_list    = '{$_POST['bo_category_list']}',
                bo_use_sideview     = '{$_POST['bo_use_sideview']}',
                bo_use_file_content = '{$_POST['bo_use_file_content']}',
                bo_use_secret       = '{$_POST['bo_use_secret']}',
                bo_use_dhtml_editor = '{$_POST['bo_use_dhtml_editor']}',
                bo_use_rss_view     = '{$_POST['bo_use_rss_view']}',
                bo_use_good         = '{$_POST['bo_use_good']}',
                bo_use_nogood       = '{$_POST['bo_use_nogood']}',
                bo_use_name         = '{$_POST['bo_use_name']}',
                bo_use_signature    = '{$_POST['bo_use_signature']}',
                bo_use_ip_view      = '{$_POST['bo_use_ip_view']}',
                bo_use_list_view    = '{$_POST['bo_use_list_view']}',
                bo_use_list_content = '{$_POST['bo_use_list_content']}',
                bo_use_email        = '{$_POST['bo_use_email']}',
                bo_table_width      = '{$_POST['bo_table_width']}',
                bo_subject_len      = '{$_POST['bo_subject_len']}',
                bo_page_rows        = '{$_POST['bo_page_rows']}',
                bo_new              = '{$_POST['bo_new']}',
                bo_hot              = '{$_POST['bo_hot']}',
                bo_image_width      = '{$_POST['bo_image_width']}',
                bo_skin             = '{$_POST['bo_skin']}',
                bo_include_head     = '{$_POST['bo_include_head']}',
                bo_include_tail     = '{$_POST['bo_include_tail']}',
                bo_content_head     = '{$_POST['bo_content_head']}',
                bo_content_tail     = '{$_POST['bo_content_tail']}',
                bo_insert_content   = '{$_POST['bo_insert_content']}',
                bo_gallery_cols     = '{$_POST['bo_gallery_cols']}',
                bo_upload_count     = '{$_POST['bo_upload_count']}',
                bo_upload_size      = '{$_POST['bo_upload_size']}',
                bo_reply_order      = '{$_POST['bo_reply_order']}',
                bo_use_search       = '{$_POST['bo_use_search']}',
                bo_order_search     = '{$_POST['bo_order_search']}',
                bo_write_min        = '{$_POST['bo_write_min']}',
                bo_write_max        = '{$_POST['bo_write_max']}',
                bo_comment_min      = '{$_POST['bo_comment_min']}',
                bo_comment_max      = '{$_POST['bo_comment_max']}',
                bo_sort_field       = '{$_POST['bo_sort_field']}',
                bo_1_subj           = '{$_POST['bo_1_subj']}',
                bo_2_subj           = '{$_POST['bo_2_subj']}',
                bo_3_subj           = '{$_POST['bo_3_subj']}',
                bo_4_subj           = '{$_POST['bo_4_subj']}',
                bo_5_subj           = '{$_POST['bo_5_subj']}',
                bo_6_subj           = '{$_POST['bo_6_subj']}',
                bo_7_subj           = '{$_POST['bo_7_subj']}',
                bo_8_subj           = '{$_POST['bo_8_subj']}',
                bo_9_subj           = '{$_POST['bo_9_subj']}',
                bo_10_subj          = '{$_POST['bo_10_subj']}',
                bo_1                = '{$_POST['bo_1']}',
                bo_2                = '{$_POST['bo_2']}',
                bo_3                = '{$_POST['bo_3']}',
                bo_4                = '{$_POST['bo_4']}',
                bo_5                = '{$_POST['bo_5']}',
                bo_6                = '{$_POST['bo_6']}',
                bo_7                = '{$_POST['bo_7']}',
                bo_8                = '{$_POST['bo_8']}',
                bo_9                = '{$_POST['bo_9']}',
                bo_10               = '{$_POST['bo_10']}' ";

if ($w == '') {

    $row = sql_fetch(" select count(*) as cnt from {$g4['board_table']} where bo_table = '{$bo_table}' ");
    if ($row['cnt'])
        alert($bo_table.' 은(는) 이미 존재하는 TABLE 입니다.');

    $sql = " insert into {$g4['board_table']}
                set bo_table = '{$bo_table}',
                    bo_count_write = '0',
                    bo_count_comment = '0',
                    $sql_common ";
    sql_query($sql);

    // 게시판 테이블 생성
    $file = file('./sql_write.sql');
    $sql = implode($file, "\n");

    $create_table = $g4['write_prefix'] . $bo_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array('/__TABLE_NAME__/', '/;/');
    $target = array($create_table, '');
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, FALSE);

} else if ($w == 'u') {

    // 게시판의 글 수
    $sql = " select count(*) as cnt from {$g4['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $bo_count_write = $row['cnt'];

    // 게시판의 코멘트 수
    $sql = " select count(*) as cnt from {$g4['write_prefix']}{$bo_table} where wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    $bo_count_comment = $row['cnt'];

    // 글수 조정
    if (isset($_POST['proc_count'])) {
        // 원글을 얻습니다.
        $sql = " select wr_id from {$g4['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 코멘트수를 얻습니다.
            $sql2 = " select count(*) as cnt from {$g4['write_prefix']}$bo_table where wr_parent = '{$row['wr_id']}' and wr_is_comment = 1 ";
            $row2 = sql_fetch($sql2);

            sql_query(" update {$g4['write_prefix']}{$bo_table} set wr_comment = '{$row2['cnt']}' where wr_id = '{$row['wr_id']}' ");
        }
    }

    // 공지사항에는 등록되어 있지만 실제 존재하지 않는 글 아이디는 삭제합니다.
    $bo_notice = "";
    $lf = "";
    if ($board['bo_notice']) {
        $tmp_array = explode("\n", $board['bo_notice']);
        for ($i=0; $i<count($tmp_array); $i++) {
            $tmp_wr_id = trim($tmp_array[$i]);
            $row = sql_fetch(" select count(*) as cnt from {$g4['write_prefix']}{$bo_table} where wr_id = '{$tmp_wr_id}' ");
            if ($row['cnt'])
            {
                $bo_notice .= $lf . $tmp_wr_id;
                $lf = "\n";
            }
        }
    }

    $sql = " update {$g4['board_table']}
                set bo_notice = '{$bo_notice}',
                    bo_count_write = '{$bo_count_write}',
                    bo_count_comment = '{$bo_count_comment}',
                    {$sql_common}
              where bo_table = '{$bo_table}' ";
    sql_query($sql);

}


// 같은 그룹내 게시판 동일 옵션 적용
$fields = "";
if (is_checked('chk_use'))              $fields .= " , bo_use = '{$bo_use}' ";
if (is_checked('chk_admin'))            $fields .= " , bo_admin = '{$bo_admin}' ";
if (is_checked('chk_list_level'))       $fields .= " , bo_list_level = '{$bo_list_level}' ";
if (is_checked('chk_read_level'))       $fields .= " , bo_read_level = '{$bo_read_level}' ";
if (is_checked('chk_write_level'))      $fields .= " , bo_write_level = '{$bo_write_level}' ";
if (is_checked('chk_reply_level'))      $fields .= " , bo_reply_level = '{$bo_reply_level}' ";
if (is_checked('chk_comment_level'))    $fields .= " , bo_comment_level = '{$bo_comment_level}' ";
if (is_checked('chk_link_level'))       $fields .= " , bo_link_level = '{$bo_link_level}' ";
if (is_checked('chk_upload_level'))     $fields .= " , bo_upload_level = '{$bo_upload_level}' ";
if (is_checked('chk_download_level'))   $fields .= " , bo_download_level = '{$bo_download_level}' ";
if (is_checked('chk_html_level'))       $fields .= " , bo_html_level = '{$bo_html_level}' ";
if (is_checked('chk_count_modify'))     $fields .= " , bo_count_modify = '{$bo_count_modify}' ";
if (is_checked('chk_count_delete'))     $fields .= " , bo_count_delete = '{$bo_count_delete}' ";
if (is_checked('chk_read_point'))       $fields .= " , bo_read_point = '{$bo_read_point}' ";
if (is_checked('chk_write_point'))      $fields .= " , bo_write_point = '{$bo_write_point}' ";
if (is_checked('chk_comment_point'))    $fields .= " , bo_comment_point = '{$bo_comment_point}' ";
if (is_checked('chk_download_point'))   $fields .= " , bo_download_point = '{$bo_download_point}' ";
if (is_checked('chk_category_list')) {
    $fields .= " , bo_category_list = '{$bo_category_list}' ";
    $fields .= " , bo_use_category = '{$bo_use_category}' ";
}
if (is_checked('chk_use_sideview'))     $fields .= " , bo_use_sideview = '{$bo_use_sideview}' ";
if (is_checked('chk_use_file_content')) $fields .= " , bo_use_file_content = '{$bo_use_file_content}' ";
if (is_checked('chk_use_secret'))       $fields .= " , bo_use_secret = '{$bo_use_secret}' ";
if (is_checked('chk_use_dhtml_editor')) $fields .= " , bo_use_dhtml_editor = '{$bo_use_dhtml_editor}' ";
if (is_checked('chk_use_rss_view'))     $fields .= " , bo_use_rss_view = '{$bo_use_rss_view}' ";
if (is_checked('chk_use_good'))         $fields .= " , bo_use_good = '{$bo_use_good}' ";
if (is_checked('chk_use_nogood'))       $fields .= " , bo_use_nogood = '{$bo_use_nogood}' ";
if (is_checked('chk_use_name'))         $fields .= " , bo_use_name = '{$bo_use_name}' ";
if (is_checked('chk_use_signature'))    $fields .= " , bo_use_signature = '{$bo_use_signature}' ";
if (is_checked('chk_use_ip_view'))      $fields .= " , bo_use_ip_view = '{$bo_use_ip_view}' ";
if (is_checked('chk_use_list_view'))    $fields .= " , bo_use_list_view = '{$bo_use_list_view}' ";
if (is_checked('chk_use_list_content')) $fields .= " , bo_use_list_content = '{$bo_use_list_content}' ";
if (is_checked('chk_use_email'))        $fields .= " , bo_use_email = '{$bo_use_email}' ";
if (is_checked('chk_skin'))             $fields .= " , bo_skin = '{$bo_skin}' ";
if (is_checked('chk_gallery_cols'))     $fields .= " , bo_gallery_cols = '{$bo_gallery_cols}' ";
if (is_checked('chk_table_width'))      $fields .= " , bo_table_width = '{$bo_table_width}' ";
if (is_checked('chk_page_rows'))        $fields .= " , bo_page_rows = '{$bo_page_rows}' ";
if (is_checked('chk_subject_len'))      $fields .= " , bo_subject_len = '{$bo_subject_len}' ";
if (is_checked('chk_new'))              $fields .= " , bo_new = '{$bo_new}' ";
if (is_checked('chk_hot'))              $fields .= " , bo_hot = '{$bo_hot}' ";
if (is_checked('chk_image_width'))      $fields .= " , bo_image_width = '{$bo_image_width}' ";
if (is_checked('chk_reply_order'))      $fields .= " , bo_reply_order = '{$bo_reply_order}' ";
if (is_checked('chk_sort_field'))       $fields .= " , bo_sort_field = '{$bo_sort_field}' ";
if (is_checked('chk_write_min'))        $fields .= " , bo_write_min = '{$bo_write_min}' ";
if (is_checked('chk_write_max'))        $fields .= " , bo_write_max = '{$bo_write_max}' ";
if (is_checked('chk_comment_min'))      $fields .= " , bo_comment_min = '{$bo_comment_min}' ";
if (is_checked('chk_comment_max'))      $fields .= " , bo_comment_max = '{$bo_comment_max}' ";
if (is_checked('chk_upload_count'))     $fields .= " , bo_upload_count = '{$bo_upload_count}' ";
if (is_checked('chk_upload_size'))      $fields .= " , bo_upload_size = '{$bo_upload_size}' ";
if (is_checked('chk_include_head'))     $fields .= " , bo_include_head = '{$bo_include_head}' ";
if (is_checked('chk_include_tail'))     $fields .= " , bo_include_tail = '{$bo_include_tail}' ";
if (is_checked('chk_content_head'))     $fields .= " , bo_content_head = '{$bo_content_head}' ";
if (is_checked('chk_content_tail'))     $fields .= " , bo_content_tail = '{$bo_content_tail}' ";
if (is_checked('chk_insert_content'))   $fields .= " , bo_insert_content = '{$bo_insert_content}' ";
if (is_checked('chk_use_search'))       $fields .= " , bo_use_search = '{$bo_use_search}' ";
if (is_checked('chk_order_search'))     $fields .= " , bo_order_search = '{$bo_order_search}' ";
for ($i=1; $i<=10; $i++) {
    if (is_checked('chk_'.$i)) {
        $fields .= " , bo_{$i}_subj = '".$_POST['bo_'.$i.'_subj']."' ";
        $fields .= " , bo_{$i} = '".$_POST['bo_'.$i]."' ";
    }
}

if ($fields) {
        $sql = " update {$g4['board_table']} set bo_table = bo_table {$fields} where gr_id = '$gr_id' ";
        sql_query($sql);
}

delete_cache_latest($bo_table);

goto_url("./board_form.php?w=u&bo_table={$bo_table}&amp;{$qstr}");
?>
