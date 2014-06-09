<?php
$sub_menu = "300500";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'w');

check_token();

$sql = " update {$g5['qa_config_table']}
            set qa_title                = '{$_POST['qa_title']}',
                qa_category             = '{$_POST['qa_category']}',
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
                qa_include_head         = '{$_POST['qa_include_head']}',
                qa_include_tail         = '{$_POST['qa_include_tail']}',
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

goto_url('./qa_config.php');
?>