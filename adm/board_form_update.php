<?php
$sub_menu = "300100";
include_once('./_common.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

check_admin_token();

if (!$_POST['gr_id']) { alert('그룹 ID는 반드시 선택하세요.'); }
if (!$bo_table) { alert('게시판 TABLE명은 반드시 입력하세요.'); }
if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) { alert('게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)'); }
if (!$_POST['bo_subject']) { alert('게시판 제목을 입력하세요.'); }

$_POST['bo_include_head'] = preg_replace("#[\\\]+$#", "", substr($_POST['bo_include_head'], 0, 255));
$_POST['bo_include_tail'] = preg_replace("#[\\\]+$#", "", substr($_POST['bo_include_tail'], 0, 255));

if ($file = $_POST['bo_include_head']) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if( ! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) ) {
        alert('상단 파일 경로의 확장자는 php, html 만 허용합니다.');
    }
    $_POST['bo_include_head'] = $file;
}

if ($file = $_POST['bo_include_tail']) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if( ! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) ) {
        alert('하단 파일 경로의 확장자는 php, html 만 허용합니다.');
    }
    $_POST['bo_include_tail'] = $file;
}

if(!is_include_path_check($_POST['bo_include_head'], 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if(!is_include_path_check($_POST['bo_include_tail'], 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

$board_path = G5_DATA_PATH.'/file/'.$bo_table;

// 게시판 디렉토리 생성
@mkdir($board_path, G5_DIR_PERMISSION);
@chmod($board_path, G5_DIR_PERMISSION);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . '/index.php';
$f = @fopen($file, 'w');
@fwrite($f, '');
@fclose($f);
@chmod($file, G5_FILE_PERMISSION);

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓');
$bo_category_list = str_replace($src_char, $dst_char, $bo_category_list);
//https://github.com/gnuboard/gnuboard5/commit/f5f4925d4eb28ba1af728e1065fc2bdd9ce1da58 에 따른 조치
$str_bo_category_list = isset($_POST['bo_category_list']) ? preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $_POST['bo_category_list']) : '';

$sql_common = " gr_id               = '{$_POST['gr_id']}',
                bo_subject          = '{$_POST['bo_subject']}',
                bo_mobile_subject   = '{$_POST['bo_mobile_subject']}',
                bo_device           = '{$_POST['bo_device']}',
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
                bo_category_list    = '{$str_bo_category_list}',
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
                bo_use_list_file    = '{$_POST['bo_use_list_file']}',
                bo_use_list_content = '{$_POST['bo_use_list_content']}',
                bo_use_email        = '{$_POST['bo_use_email']}',
                bo_use_cert         = '{$_POST['bo_use_cert']}',
                bo_use_sns          = '{$_POST['bo_use_sns']}',
                bo_use_captcha      = '{$_POST['bo_use_captcha']}',
                bo_table_width      = '{$_POST['bo_table_width']}',
                bo_subject_len      = '{$_POST['bo_subject_len']}',
                bo_mobile_subject_len      = '{$_POST['bo_mobile_subject_len']}',
                bo_page_rows        = '{$_POST['bo_page_rows']}',
                bo_mobile_page_rows = '{$_POST['bo_mobile_page_rows']}',
                bo_new              = '{$_POST['bo_new']}',
                bo_hot              = '{$_POST['bo_hot']}',
                bo_image_width      = '{$_POST['bo_image_width']}',
                bo_skin             = '{$_POST['bo_skin']}',
                bo_mobile_skin      = '{$_POST['bo_mobile_skin']}',
                ";

// 최고 관리자인 경우에만 수정가능
if ($is_admin === 'super'){
$sql_common .= " bo_include_head     = '{$_POST['bo_include_head']}',
                bo_include_tail     = '{$_POST['bo_include_tail']}',
                bo_content_head     = '{$_POST['bo_content_head']}',
                bo_content_tail     = '{$_POST['bo_content_tail']}',
                bo_mobile_content_head     = '{$_POST['bo_mobile_content_head']}',
                bo_mobile_content_tail     = '{$_POST['bo_mobile_content_tail']}',
                ";
}

$sql_common .= " bo_insert_content   = '{$_POST['bo_insert_content']}',
                bo_gallery_cols     = '{$_POST['bo_gallery_cols']}',
                bo_gallery_width    = '{$_POST['bo_gallery_width']}',
                bo_gallery_height   = '{$_POST['bo_gallery_height']}',
                bo_mobile_gallery_width = '{$_POST['bo_mobile_gallery_width']}',
                bo_mobile_gallery_height= '{$_POST['bo_mobile_gallery_height']}',
                bo_upload_count     = '{$_POST['bo_upload_count']}',
                bo_upload_size      = '{$_POST['bo_upload_size']}',
                bo_reply_order      = '{$_POST['bo_reply_order']}',
                bo_use_search       = '{$_POST['bo_use_search']}',
                bo_order            = '{$_POST['bo_order']}',
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

    $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
    if ($row['cnt'])
        alert($bo_table.' 은(는) 이미 존재하는 TABLE 입니다.');

    $sql = " insert into {$g5['board_table']}
                set bo_table = '{$bo_table}',
                    bo_count_write = '0',
                    bo_count_comment = '0',
                    $sql_common ";
    sql_query($sql);

    // 게시판 테이블 생성
    $file = file('./sql_write.sql');
    $sql = implode($file, "\n");

    $create_table = $g5['write_prefix'] . $bo_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array('/__TABLE_NAME__/', '/;/');
    $target = array($create_table, '');
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, FALSE);

} else if ($w == 'u') {

    // 게시판의 글 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $bo_count_write = $row['cnt'];

    // 게시판의 코멘트 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    $bo_count_comment = $row['cnt'];

    // 글수 조정
    /*
        엔피씨님의 팁으로 교체합니다. 130308
        http://sir.kr/g5_tiptech/27207
    */
    if (isset($_POST['proc_count'])) {
        // 원글을 얻습니다.
        //$sql = " select wr_id from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
        $sql = " select a.wr_id, (count(b.wr_parent) - 1) as cnt from {$g5['write_prefix']}{$bo_table} a, {$g5['write_prefix']}{$bo_table} b where a.wr_id=b.wr_parent and a.wr_is_comment=0 group by a.wr_id ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            /*
            // 코멘트수를 얻습니다.
            $sql2 = " select count(*) as cnt from {$g5['write_prefix']}$bo_table where wr_parent = '{$row['wr_id']}' and wr_is_comment = 1 ";
            $row2 = sql_fetch($sql2);
            */

            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_comment = '{$row['cnt']}' where wr_id = '{$row['wr_id']}' ");
        }
    }

    // 공지사항에는 등록되어 있지만 실제 존재하지 않는 글 아이디는 삭제합니다.
    $bo_notice = "";
    $lf = "";
    if ($board['bo_notice']) {
        $tmp_array = explode(",", $board['bo_notice']);
        for ($i=0; $i<count($tmp_array); $i++) {
            $tmp_wr_id = trim($tmp_array[$i]);
            $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_id = '{$tmp_wr_id}' ");
            if ($row['cnt'])
            {
                $bo_notice .= $lf . $tmp_wr_id;
                $lf = ",";
            }
        }
    }

    $sql = " update {$g5['board_table']}
                set bo_notice = '{$bo_notice}',
                    bo_count_write = '{$bo_count_write}',
                    bo_count_comment = '{$bo_count_comment}',
                    {$sql_common}
              where bo_table = '{$bo_table}' ";
    sql_query($sql);

}


// 같은 그룹내 게시판 동일 옵션 적용
$grp_fields = '';
if (is_checked('chk_grp_device'))               $grp_fields .= " , bo_device = '{$bo_device}' ";
if (is_checked('chk_grp_admin'))                $grp_fields .= " , bo_admin = '{$bo_admin}' ";
if (is_checked('chk_grp_list_level'))           $grp_fields .= " , bo_list_level = '{$bo_list_level}' ";
if (is_checked('chk_grp_read_level'))           $grp_fields .= " , bo_read_level = '{$bo_read_level}' ";
if (is_checked('chk_grp_write_level'))          $grp_fields .= " , bo_write_level = '{$bo_write_level}' ";
if (is_checked('chk_grp_reply_level'))          $grp_fields .= " , bo_reply_level = '{$bo_reply_level}' ";
if (is_checked('chk_grp_comment_level'))        $grp_fields .= " , bo_comment_level = '{$bo_comment_level}' ";
if (is_checked('chk_grp_link_level'))           $grp_fields .= " , bo_link_level = '{$bo_link_level}' ";
if (is_checked('chk_grp_upload_level'))         $grp_fields .= " , bo_upload_level = '{$bo_upload_level}' ";
if (is_checked('chk_grp_download_level'))       $grp_fields .= " , bo_download_level = '{$bo_download_level}' ";
if (is_checked('chk_grp_html_level'))           $grp_fields .= " , bo_html_level = '{$bo_html_level}' ";
if (is_checked('chk_grp_count_modify'))         $grp_fields .= " , bo_count_modify = '{$bo_count_modify}' ";
if (is_checked('chk_grp_count_delete'))         $grp_fields .= " , bo_count_delete = '{$bo_count_delete}' ";
if (is_checked('chk_grp_read_point'))           $grp_fields .= " , bo_read_point = '{$bo_read_point}' ";
if (is_checked('chk_grp_write_point'))          $grp_fields .= " , bo_write_point = '{$bo_write_point}' ";
if (is_checked('chk_grp_comment_point'))        $grp_fields .= " , bo_comment_point = '{$bo_comment_point}' ";
if (is_checked('chk_grp_download_point'))       $grp_fields .= " , bo_download_point = '{$bo_download_point}' ";
if (is_checked('chk_grp_category_list')) {
    $grp_fields .= " , bo_category_list = '{$bo_category_list}' ";
    $grp_fields .= " , bo_use_category = '{$bo_use_category}' ";
}
if (is_checked('chk_grp_use_sideview'))         $grp_fields .= " , bo_use_sideview = '{$bo_use_sideview}' ";
if (is_checked('chk_grp_use_file_content'))     $grp_fields .= " , bo_use_file_content = '{$bo_use_file_content}' ";
if (is_checked('chk_grp_use_secret'))           $grp_fields .= " , bo_use_secret = '{$bo_use_secret}' ";
if (is_checked('chk_grp_use_dhtml_editor'))     $grp_fields .= " , bo_use_dhtml_editor = '{$bo_use_dhtml_editor}' ";
if (is_checked('chk_grp_use_rss_view'))         $grp_fields .= " , bo_use_rss_view = '{$bo_use_rss_view}' ";
if (is_checked('chk_grp_use_good'))             $grp_fields .= " , bo_use_good = '{$bo_use_good}' ";
if (is_checked('chk_grp_use_nogood'))           $grp_fields .= " , bo_use_nogood = '{$bo_use_nogood}' ";
if (is_checked('chk_grp_use_name'))             $grp_fields .= " , bo_use_name = '{$bo_use_name}' ";
if (is_checked('chk_grp_use_signature'))        $grp_fields .= " , bo_use_signature = '{$bo_use_signature}' ";
if (is_checked('chk_grp_use_ip_view'))          $grp_fields .= " , bo_use_ip_view = '{$bo_use_ip_view}' ";
if (is_checked('chk_grp_use_list_view'))        $grp_fields .= " , bo_use_list_view = '{$bo_use_list_view}' ";
if (is_checked('chk_grp_use_list_file'))        $grp_fields .= " , bo_use_list_file = '{$bo_use_list_file}' ";
if (is_checked('chk_grp_use_list_content'))     $grp_fields .= " , bo_use_list_content = '{$bo_use_list_content}' ";
if (is_checked('chk_grp_use_email'))            $grp_fields .= " , bo_use_email = '{$bo_use_email}' ";
if (is_checked('chk_grp_use_cert'))             $grp_fields .= " , bo_use_cert = '{$bo_use_cert}' ";
if (is_checked('chk_grp_use_sns'))              $grp_fields .= " , bo_use_sns = '{$bo_use_sns}' ";
if (is_checked('chk_grp_use_captcha'))          $grp_fields .= " , bo_use_captcha = '{$bo_use_captcha}' ";
if (is_checked('chk_grp_skin'))                 $grp_fields .= " , bo_skin = '{$bo_skin}' ";
if (is_checked('chk_grp_mobile_skin'))          $grp_fields .= " , bo_mobile_skin = '{$bo_mobile_skin}' ";
if (is_checked('chk_grp_gallery_cols'))         $grp_fields .= " , bo_gallery_cols = '{$bo_gallery_cols}' ";
if (is_checked('chk_grp_gallery_width'))        $grp_fields .= " , bo_gallery_width = '{$bo_gallery_width}' ";
if (is_checked('chk_grp_gallery_height'))       $grp_fields .= " , bo_gallery_height = '{$bo_gallery_height}' ";
if (is_checked('chk_grp_mobile_gallery_width')) $grp_fields .= " , bo_mobile_gallery_width = '{$bo_mobile_gallery_width}' ";
if (is_checked('chk_grp_mobile_gallery_height'))$grp_fields .= " , bo_mobile_gallery_height = '{$bo_mobile_gallery_height}' ";
if (is_checked('chk_grp_table_width'))          $grp_fields .= " , bo_table_width = '{$bo_table_width}' ";
if (is_checked('chk_grp_page_rows'))            $grp_fields .= " , bo_page_rows = '{$bo_page_rows}' ";
if (is_checked('chk_grp_mobile_page_rows'))            $grp_fields .= " , bo_mobile_page_rows = '{$bo_mobile_page_rows}' ";
if (is_checked('chk_grp_subject_len'))          $grp_fields .= " , bo_subject_len = '{$bo_subject_len}' ";
if (is_checked('chk_grp_mobile_subject_len'))          $grp_fields .= " , bo_mobile_subject_len = '{$bo_mobile_subject_len}' ";
if (is_checked('chk_grp_new'))                  $grp_fields .= " , bo_new = '{$bo_new}' ";
if (is_checked('chk_grp_hot'))                  $grp_fields .= " , bo_hot = '{$bo_hot}' ";
if (is_checked('chk_grp_image_width'))          $grp_fields .= " , bo_image_width = '{$bo_image_width}' ";
if (is_checked('chk_grp_reply_order'))          $grp_fields .= " , bo_reply_order = '{$bo_reply_order}' ";
if (is_checked('chk_grp_sort_field'))           $grp_fields .= " , bo_sort_field = '{$bo_sort_field}' ";
if (is_checked('chk_grp_write_min'))            $grp_fields .= " , bo_write_min = '{$bo_write_min}' ";
if (is_checked('chk_grp_write_max'))            $grp_fields .= " , bo_write_max = '{$bo_write_max}' ";
if (is_checked('chk_grp_comment_min'))          $grp_fields .= " , bo_comment_min = '{$bo_comment_min}' ";
if (is_checked('chk_grp_comment_max'))          $grp_fields .= " , bo_comment_max = '{$bo_comment_max}' ";
if (is_checked('chk_grp_upload_count'))         $grp_fields .= " , bo_upload_count = '{$bo_upload_count}' ";
if (is_checked('chk_grp_upload_size'))          $grp_fields .= " , bo_upload_size = '{$bo_upload_size}' ";

//최고관리자만 수정가능
if ($is_admin === 'super'){
    if (is_checked('chk_grp_include_head'))         $grp_fields .= " , bo_include_head = '{$bo_include_head}' ";
    if (is_checked('chk_grp_include_tail'))         $grp_fields .= " , bo_include_tail = '{$bo_include_tail}' ";
    if (is_checked('chk_grp_content_head'))         $grp_fields .= " , bo_content_head = '{$bo_content_head}' ";
    if (is_checked('chk_grp_content_tail'))         $grp_fields .= " , bo_content_tail = '{$bo_content_tail}' ";
    if (is_checked('chk_grp_mobile_content_head'))         $grp_fields .= " , bo_mobile_content_head = '{$bo_mobile_content_head}' ";
    if (is_checked('chk_grp_mobile_content_tail'))         $grp_fields .= " , bo_mobile_content_tail = '{$bo_mobile_content_tail}' ";
}

if (is_checked('chk_grp_insert_content'))       $grp_fields .= " , bo_insert_content = '{$bo_insert_content}' ";
if (is_checked('chk_grp_use_search'))           $grp_fields .= " , bo_use_search = '{$bo_use_search}' ";
if (is_checked('chk_grp_order'))                $grp_fields .= " , bo_order = '{$bo_order}' ";
for ($i=1; $i<=10; $i++) {
    if (is_checked('chk_grp_'.$i)) {
        $grp_fields .= " , bo_{$i}_subj = '".$_POST['bo_'.$i.'_subj']."' ";
        $grp_fields .= " , bo_{$i} = '".$_POST['bo_'.$i]."' ";
    }
}

if ($grp_fields) {
    sql_query(" update {$g5['board_table']} set bo_table = bo_table {$grp_fields} where gr_id = '$gr_id' ");
}


// 모든 게시판 동일 옵션 적용
$all_fields = '';
if (is_checked('chk_all_device'))               $all_fields .= " , bo_device = '{$bo_device}' ";
if (is_checked('chk_all_admin'))                $all_fields .= " , bo_admin = '{$bo_admin}' ";
if (is_checked('chk_all_list_level'))           $all_fields .= " , bo_list_level = '{$bo_list_level}' ";
if (is_checked('chk_all_read_level'))           $all_fields .= " , bo_read_level = '{$bo_read_level}' ";
if (is_checked('chk_all_write_level'))          $all_fields .= " , bo_write_level = '{$bo_write_level}' ";
if (is_checked('chk_all_reply_level'))          $all_fields .= " , bo_reply_level = '{$bo_reply_level}' ";
if (is_checked('chk_all_comment_level'))        $all_fields .= " , bo_comment_level = '{$bo_comment_level}' ";
if (is_checked('chk_all_link_level'))           $all_fields .= " , bo_link_level = '{$bo_link_level}' ";
if (is_checked('chk_all_upload_level'))         $all_fields .= " , bo_upload_level = '{$bo_upload_level}' ";
if (is_checked('chk_all_download_level'))       $all_fields .= " , bo_download_level = '{$bo_download_level}' ";
if (is_checked('chk_all_html_level'))           $all_fields .= " , bo_html_level = '{$bo_html_level}' ";
if (is_checked('chk_all_count_modify'))         $all_fields .= " , bo_count_modify = '{$bo_count_modify}' ";
if (is_checked('chk_all_count_delete'))         $all_fields .= " , bo_count_delete = '{$bo_count_delete}' ";
if (is_checked('chk_all_read_point'))           $all_fields .= " , bo_read_point = '{$bo_read_point}' ";
if (is_checked('chk_all_write_point'))          $all_fields .= " , bo_write_point = '{$bo_write_point}' ";
if (is_checked('chk_all_comment_point'))        $all_fields .= " , bo_comment_point = '{$bo_comment_point}' ";
if (is_checked('chk_all_download_point'))       $all_fields .= " , bo_download_point = '{$bo_download_point}' ";
if (is_checked('chk_all_category_list')) {
    $all_fields .= " , bo_category_list = '{$bo_category_list}' ";
    $all_fields .= " , bo_use_category = '{$bo_use_category}' ";
}
if (is_checked('chk_all_use_sideview'))         $all_fields .= " , bo_use_sideview = '{$bo_use_sideview}' ";
if (is_checked('chk_all_use_file_content'))     $all_fields .= " , bo_use_file_content = '{$bo_use_file_content}' ";
if (is_checked('chk_all_use_secret'))           $all_fields .= " , bo_use_secret = '{$bo_use_secret}' ";
if (is_checked('chk_all_use_dhtml_editor'))     $all_fields .= " , bo_use_dhtml_editor = '{$bo_use_dhtml_editor}' ";
if (is_checked('chk_all_use_rss_view'))         $all_fields .= " , bo_use_rss_view = '{$bo_use_rss_view}' ";
if (is_checked('chk_all_use_good'))             $all_fields .= " , bo_use_good = '{$bo_use_good}' ";
if (is_checked('chk_all_use_nogood'))           $all_fields .= " , bo_use_nogood = '{$bo_use_nogood}' ";
if (is_checked('chk_all_use_name'))             $all_fields .= " , bo_use_name = '{$bo_use_name}' ";
if (is_checked('chk_all_use_signature'))        $all_fields .= " , bo_use_signature = '{$bo_use_signature}' ";
if (is_checked('chk_all_use_ip_view'))          $all_fields .= " , bo_use_ip_view = '{$bo_use_ip_view}' ";
if (is_checked('chk_all_use_list_view'))        $all_fields .= " , bo_use_list_view = '{$bo_use_list_view}' ";
if (is_checked('chk_all_use_list_file'))        $all_fields .= " , bo_use_list_file = '{$bo_use_list_file}' ";
if (is_checked('chk_all_use_list_content'))     $all_fields .= " , bo_use_list_content = '{$bo_use_list_content}' ";
if (is_checked('chk_all_use_email'))            $all_fields .= " , bo_use_email = '{$bo_use_email}' ";
if (is_checked('chk_all_use_cert'))             $all_fields .= " , bo_use_cert = '{$bo_use_cert}' ";
if (is_checked('chk_all_use_sns'))              $all_fields .= " , bo_use_sns = '{$bo_use_sns}' ";
if (is_checked('chk_all_use_captcha'))          $all_fields .= " , bo_use_captcha = '{$bo_use_captcha}' ";
if (is_checked('chk_all_skin'))                 $all_fields .= " , bo_skin = '{$bo_skin}' ";
if (is_checked('chk_all_mobile_skin'))          $all_fields .= " , bo_mobile_skin = '{$bo_mobile_skin}' ";
if (is_checked('chk_all_gallery_cols'))         $all_fields .= " , bo_gallery_cols = '{$bo_gallery_cols}' ";
if (is_checked('chk_all_gallery_width'))        $all_fields .= " , bo_gallery_width = '{$bo_gallery_width}' ";
if (is_checked('chk_all_gallery_height'))       $all_fields .= " , bo_gallery_height = '{$bo_gallery_height}' ";
if (is_checked('chk_all_mobile_gallery_width')) $all_fields .= " , bo_mobile_gallery_width = '{$bo_mobile_gallery_width}' ";
if (is_checked('chk_all_mobile_gallery_height'))$all_fields .= " , bo_mobile_gallery_height = '{$bo_mobile_gallery_height}' ";
if (is_checked('chk_all_table_width'))          $all_fields .= " , bo_table_width = '{$bo_table_width}' ";
if (is_checked('chk_all_page_rows'))            $all_fields .= " , bo_page_rows = '{$bo_page_rows}' ";
if (is_checked('chk_all_mobile_page_rows'))            $all_fields .= " , bo_mobile_page_rows = '{$bo_mobile_page_rows}' ";
if (is_checked('chk_all_subject_len'))          $all_fields .= " , bo_subject_len = '{$bo_subject_len}' ";
if (is_checked('chk_all_mobile_subject_len'))          $all_fields .= " , bo_mobile_subject_len = '{$bo_mobile_subject_len}' ";
if (is_checked('chk_all_new'))                  $all_fields .= " , bo_new = '{$bo_new}' ";
if (is_checked('chk_all_hot'))                  $all_fields .= " , bo_hot = '{$bo_hot}' ";
if (is_checked('chk_all_image_width'))          $all_fields .= " , bo_image_width = '{$bo_image_width}' ";
if (is_checked('chk_all_reply_order'))          $all_fields .= " , bo_reply_order = '{$bo_reply_order}' ";
if (is_checked('chk_all_sort_field'))           $all_fields .= " , bo_sort_field = '{$bo_sort_field}' ";
if (is_checked('chk_all_write_min'))            $all_fields .= " , bo_write_min = '{$bo_write_min}' ";
if (is_checked('chk_all_write_max'))            $all_fields .= " , bo_write_max = '{$bo_write_max}' ";
if (is_checked('chk_all_comment_min'))          $all_fields .= " , bo_comment_min = '{$bo_comment_min}' ";
if (is_checked('chk_all_comment_max'))          $all_fields .= " , bo_comment_max = '{$bo_comment_max}' ";
if (is_checked('chk_all_upload_count'))         $all_fields .= " , bo_upload_count = '{$bo_upload_count}' ";
if (is_checked('chk_all_upload_size'))          $all_fields .= " , bo_upload_size = '{$bo_upload_size}' ";

//최고관리자만 수정가능
if ($is_admin === 'super'){
    if (is_checked('chk_all_include_head'))         $all_fields .= " , bo_include_head = '{$bo_include_head}' ";
    if (is_checked('chk_all_include_tail'))         $all_fields .= " , bo_include_tail = '{$bo_include_tail}' ";
    if (is_checked('chk_all_content_head'))         $all_fields .= " , bo_content_head = '{$bo_content_head}' ";
    if (is_checked('chk_all_content_tail'))         $all_fields .= " , bo_content_tail = '{$bo_content_tail}' ";
    if (is_checked('chk_all_mobile_content_head'))         $all_fields .= " , bo_mobile_content_head = '{$bo_mobile_content_head}' ";
    if (is_checked('chk_all_mobile_content_tail'))         $all_fields .= " , bo_mobile_content_tail = '{$bo_mobile_content_tail}' ";
}

if (is_checked('chk_all_insert_content'))       $all_fields .= " , bo_insert_content = '{$bo_insert_content}' ";
if (is_checked('chk_all_use_search'))           $all_fields .= " , bo_use_search = '{$bo_use_search}' ";
if (is_checked('chk_all_order'))                $all_fields .= " , bo_order = '{$bo_order}' ";
for ($i=1; $i<=10; $i++) {
    if (is_checked('chk_all_'.$i)) {
        $all_fields .= " , bo_{$i}_subj = '".$_POST['bo_'.$i.'_subj']."' ";
        $all_fields .= " , bo_{$i} = '".$_POST['bo_'.$i]."' ";
    }
}

if ($all_fields) {
    sql_query(" update {$g5['board_table']} set bo_table = bo_table {$all_fields} ");
}

delete_cache_latest($bo_table);

goto_url("./board_form.php?w=u&bo_table={$bo_table}&amp;{$qstr}");
?>
