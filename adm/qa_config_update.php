<?php
$sub_menu = "300500";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$error_msg = '';

$qaconfig = get_qa_config();

$check_keys = array('qa_title', 'qa_category', 'qa_skin', 'qa_mobile_skin', 'qa_use_email', 'qa_req_email', 'qa_use_hp', 'qa_req_hp', 'qa_use_sms', 'qa_send_number', 'qa_admin_hp', 'qa_admin_email', 'qa_subject_len', 'qa_mobile_subject_len', 'qa_page_rows', 'qa_mobile_page_rows', 'qa_image_width', 'qa_upload_size');

foreach ($check_keys as $key) {
    $$key = $_POST[$key] = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

$qa_include_head = isset($qa_include_head) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($qa_include_head, 0, 255)) : '';
$qa_include_tail = isset($qa_include_tail) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($qa_include_tail, 0, 255)) : '';

// 관리자가 자동등록방지를 사용해야 할 경우
if ($board && ($qaconfig['qa_include_head'] !== $qa_include_head || $qaconfig['qa_include_tail'] !== $qa_include_tail) && function_exists('get_admin_captcha_by') && get_admin_captcha_by()) {
    include_once G5_CAPTCHA_PATH . '/captcha.lib.php';

    if (!chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }
}

if ($qa_include_head) {
    $file_ext = pathinfo($qa_include_head, PATHINFO_EXTENSION);

    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $qa_include_head)) {
        alert('상단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if ($qa_include_tail) {
    $file_ext = pathinfo($qa_include_tail, PATHINFO_EXTENSION);

    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $qa_include_tail)) {
        alert('하단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if ($qa_include_head && !is_include_path_check($qa_include_head, 1)) {
    $qa_include_head = '';
    $error_msg = '/data/file/ 또는 /data/editor/ 포함된 문자를 상단 파일 경로에 포함시킬수 없습니다.';
}

if ($qa_include_tail && !is_include_path_check($qa_include_tail, 1)) {
    $qa_include_tail = '';
    $error_msg = '/data/file/ 또는 /data/editor/ 포함된 문자를 하단 파일 경로에 포함시킬수 없습니다.';
}

if (function_exists('filter_input_include_path')) {
    $qa_include_head = filter_input_include_path($qa_include_head);
    $qa_include_tail = filter_input_include_path($qa_include_tail);
}

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓');
$qa_category = str_replace($src_char, $dst_char, $_POST['qa_category']);

//https://github.com/gnuboard/gnuboard5/commit/f5f4925d4eb28ba1af728e1065fc2bdd9ce1da58 에 따른 조치
$qa_category = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", (string)$qa_category);

$sql = " update {$g5['qa_config_table']}
            set qa_title                = '{$_POST['qa_title']}',
                qa_category             = '{$qa_category}',
                qa_skin                 = '{$_POST['qa_skin']}',
                qa_mobile_skin          = '{$_POST['qa_mobile_skin']}',
                qa_use_email            = '{$_POST['qa_use_email']}',
                qa_req_email            = '{$_POST['qa_req_email']}',
                qa_use_hp               = '{$_POST['qa_use_hp']}',
                qa_req_hp               = '{$_POST['qa_req_hp']}',
                qa_use_sms              = '{$_POST['qa_use_sms']}',
                qa_send_number          = '{$_POST['qa_send_number']}',
                qa_admin_hp             = '{$_POST['qa_admin_hp']}',
                qa_admin_email          = '{$_POST['qa_admin_email']}',
                qa_use_editor           = '{$_POST['qa_use_editor']}',
                qa_subject_len          = '{$_POST['qa_subject_len']}',
                qa_mobile_subject_len   = '{$_POST['qa_mobile_subject_len']}',
                qa_page_rows            = '{$_POST['qa_page_rows']}',
                qa_mobile_page_rows     = '{$_POST['qa_mobile_page_rows']}',
                qa_image_width          = '{$_POST['qa_image_width']}',
                qa_upload_size          = '{$_POST['qa_upload_size']}',
                qa_insert_content       = '{$_POST['qa_insert_content']}',
                qa_include_head         = '{$qa_include_head}',
                qa_include_tail         = '{$qa_include_tail}',
                qa_content_head         = '{$_POST['qa_content_head']}',
                qa_content_tail         = '{$_POST['qa_content_tail']}',
                qa_mobile_content_head  = '{$_POST['qa_mobile_content_head']}',
                qa_mobile_content_tail  = '{$_POST['qa_mobile_content_tail']}',
                qa_1_subj               = '{$_POST['qa_1_subj']}',
                qa_2_subj               = '{$_POST['qa_2_subj']}',
                qa_3_subj               = '{$_POST['qa_3_subj']}',
                qa_4_subj               = '{$_POST['qa_4_subj']}',
                qa_5_subj               = '{$_POST['qa_5_subj']}',
                qa_1                    = '{$_POST['qa_1']}',
                qa_2                    = '{$_POST['qa_2']}',
                qa_3                    = '{$_POST['qa_3']}',
                qa_4                    = '{$_POST['qa_4']}',
                qa_5                    = '{$_POST['qa_5']}' ";
sql_query($sql);

if (function_exists('get_admin_captcha_by')) {
    get_admin_captcha_by('remove');
}

if ($error_msg) {
    alert($error_msg, './qa_config.php');
} else {
    goto_url('./qa_config.php');
}
