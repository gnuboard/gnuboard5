<?php
$sub_menu = "100100";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$cf_title = isset($_POST['cf_title']) ? strip_tags(clean_xss_attributes($_POST['cf_title'])) : '';
$cf_admin = isset($_POST['cf_admin']) ? clean_xss_tags($_POST['cf_admin'], 1, 1) : '';
$posts = array();

$mb = get_member($cf_admin);

if (!(isset($mb['mb_id']) && $mb['mb_id'])) {
    alert('최고관리자 회원아이디가 존재하지 않습니다.');
}

check_admin_token();

$cf_social_servicelist = !empty($_POST['cf_social_servicelist']) ? implode(',', $_POST['cf_social_servicelist']) : '';

$check_keys = array('cf_cert_kcb_cd', 'cf_cert_kcp_cd', 'cf_editor', 'cf_recaptcha_site_key', 'cf_recaptcha_secret_key', 'cf_naver_clientid', 'cf_naver_secret', 'cf_facebook_appid', 'cf_facebook_secret', 'cf_twitter_key', 'cf_twitter_secret', 'cf_google_clientid', 'cf_google_secret', 'cf_googl_shorturl_apikey', 'cf_kakao_rest_key', 'cf_kakao_client_secret', 'cf_kakao_js_apikey', 'cf_payco_clientid', 'cf_payco_secret', 'cf_cert_kg_cd', 'cf_cert_kg_mid');

foreach ($check_keys as $key) {
    if (isset($_POST[$key]) && $_POST[$key]) {
        $posts[$key] = $_POST[$key] = preg_replace('/[^a-z0-9_\-\.]/i', '', $_POST[$key]);
    }
}

$posts['cf_icode_server_port'] = $_POST['cf_icode_server_port'] = isset($_POST['cf_icode_server_port']) ? preg_replace('/[^0-9]/', '', $_POST['cf_icode_server_port']) : '7295';

if (isset($_POST['cf_intercept_ip']) && $_POST['cf_intercept_ip']) {
    $pattern = explode("\n", trim($_POST['cf_intercept_ip']));
    for ($i = 0; $i < count($pattern); $i++) {
        $pattern[$i] = trim($pattern[$i]);
        if (empty($pattern[$i])) {
            continue;
        }

        $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
        $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
        $pat = "/^{$pattern[$i]}$/";

        if (preg_match($pat, $_SERVER['REMOTE_ADDR'])) {
            alert("현재 접속 IP : " . $_SERVER['REMOTE_ADDR'] . " 가 차단될수 있기 때문에, 다른 IP를 입력해 주세요.");
        }
    }
}

$check_keys = array(
    'cf_use_email_certify' => 'int',
    'cf_use_homepage' => 'int',
    'cf_req_homepage' => 'int',
    'cf_use_tel' => 'int',
    'cf_req_tel' => 'int',
    'cf_use_hp' => 'int',
    'cf_req_hp' => 'int',
    'cf_use_addr' => 'int',
    'cf_req_addr' => 'int',
    'cf_use_signature' => 'int',
    'cf_req_signature' => 'int',
    'cf_use_profile' => 'int',
    'cf_req_profile' => 'int',
    'cf_register_level' => 'int',
    'cf_register_point' => 'int',
    'cf_icon_level' => 'int',
    'cf_use_recommend' => 'int',
    'cf_leave_day' => 'int',
    'cf_search_part' => 'int',
    'cf_email_use' => 'int',
    'cf_email_wr_super_admin' => 'int',
    'cf_email_wr_group_admin' => 'int',
    'cf_email_wr_board_admin' => 'int',
    'cf_email_wr_write' => 'int',
    'cf_email_wr_comment_all' => 'int',
    'cf_email_mb_super_admin' => 'int',
    'cf_email_mb_member' => 'int',
    'cf_email_po_super_admin' => 'int',
    'cf_prohibit_id' => 'text',
    'cf_prohibit_email' => 'text',
    'cf_new_del' => 'int',
    'cf_memo_del' => 'int',
    'cf_visit_del' => 'int',
    'cf_popular_del' => 'int',
    'cf_use_member_icon' => 'int',
    'cf_member_icon_size' => 'int',
    'cf_member_icon_width' => 'int',
    'cf_member_icon_height' => 'int',
    'cf_member_img_size' => 'int',
    'cf_member_img_width' => 'int',
    'cf_member_img_height' => 'int',
    'cf_login_minutes' => 'int',
    'cf_formmail_is_member' => 'int',
    'cf_page_rows' => 'int',
    'cf_mobile_page_rows' => 'int',
    'cf_social_login_use' => 'int',
    'cf_cert_req' => 'int',
    'cf_cert_use' => 'int',
    'cf_cert_find' => 'int',
    'cf_cert_ipin' => 'char',
    'cf_cert_hp' => 'char',
    'cf_cert_simple' => 'char',
    'cf_admin_email' => 'char',
    'cf_admin_email_name' => 'char',
    'cf_add_script' => 'text',
    'cf_use_point' => 'int',
    'cf_point_term' => 'int',
    'cf_use_copy_log' => 'int',
    'cf_login_point' => 'int',
    'cf_cut_name' => 'int',
    'cf_nick_modify' => 'int',
    'cf_new_skin' => 'char',
    'cf_new_rows' => 'int',
    'cf_search_skin' => 'char',
    'cf_connect_skin' => 'char',
    'cf_faq_skin' => 'char',
    'cf_read_point' => 'int',
    'cf_write_point' => 'int',
    'cf_comment_point' => 'int',
    'cf_download_point' => 'int',
    'cf_write_pages' => 'int',
    'cf_mobile_pages' => 'int',
    'cf_link_target' => 'char',
    'cf_delay_sec' => 'int',
    'cf_filter' => 'text',
    'cf_possible_ip' => 'text',
    'cf_analytics' => 'text',
    'cf_add_meta' => 'text',
    'cf_member_skin' => 'char',
    'cf_image_extension' => 'char',
    'cf_flash_extension' => 'char',
    'cf_movie_extension' => 'char',
    'cf_visit' => 'char',
    'cf_stipulation' => 'text',
    'cf_privacy' => 'text',
    'cf_open_modify' => 'int',
    'cf_memo_send_point' => 'int',
    'cf_mobile_new_skin' => 'char',
    'cf_mobile_search_skin' => 'char',
    'cf_mobile_connect_skin' => 'char',
    'cf_mobile_faq_skin' => 'char',
    'cf_mobile_member_skin' => 'char',
    'cf_captcha_mp3' => 'char',
    'cf_cert_limit' => 'int',
    'cf_sms_use' => 'char',
    'cf_sms_type' => 'char',
    'cf_icode_id' => 'char',
    'cf_icode_pw' => 'char',
    'cf_icode_server_ip' => 'char',
    'cf_captcha' => 'char',
    'cf_syndi_token' => '',
    'cf_syndi_except' => ''
);

for ($i = 1; $i <= 10; $i++) {
    $check_keys['cf_' . $i . '_subj'] = isset($_POST['cf_' . $i . '_subj']) ? $_POST['cf_' . $i . '_subj'] : '';
    $check_keys['cf_' . $i] = isset($_POST['cf_' . $i]) ? $_POST['cf_' . $i] : '';
}

foreach ($check_keys as $k => $v) {
    if ($v === 'int') {
        $posts[$key] = $_POST[$k] = isset($_POST[$k]) ? (int) $_POST[$k] : 0;
    } else {
        if (in_array($k, array('cf_analytics', 'cf_add_meta', 'cf_add_script', 'cf_stipulation', 'cf_privacy'))) {
            $posts[$key] = $_POST[$k] = isset($_POST[$k]) ? $_POST[$k] : '';
        } else {
            $posts[$key] = $_POST[$k] = isset($_POST[$k]) ? strip_tags(clean_xss_attributes($_POST[$k])) : '';
        }
    }
}

// 본인확인을 사용할 경우 아이핀, 휴대폰인증 중 하나는 선택되어야 함
if ($_POST['cf_cert_use'] && !$_POST['cf_cert_ipin'] && !$_POST['cf_cert_hp'] && !$_POST['cf_cert_simple']) {
    alert('본인확인을 위해 아이핀, 휴대폰 본인확인, KG이니시스 간편인증 서비스 중 하나 이상 선택해 주십시오.');
}

if (!$_POST['cf_cert_use']) {
    $posts[$key] = $_POST['cf_cert_ipin'] = '';
    $posts[$key] = $_POST['cf_cert_hp'] = '';
    $posts[$key] = $_POST['cf_cert_simple'] = '';
}

$sql = " update {$g5['config_table']}
            set cf_title = '{$cf_title}',
                cf_admin = '{$cf_admin}',
                cf_admin_email = '{$_POST['cf_admin_email']}',
                cf_admin_email_name = '{$_POST['cf_admin_email_name']}',
                cf_add_script = '{$_POST['cf_add_script']}',
                cf_use_point = '{$_POST['cf_use_point']}',
                cf_point_term = '{$_POST['cf_point_term']}',
                cf_use_copy_log = '{$_POST['cf_use_copy_log']}',
                cf_use_email_certify = '{$_POST['cf_use_email_certify']}',
                cf_login_point = '{$_POST['cf_login_point']}',
                cf_cut_name = '{$_POST['cf_cut_name']}',
                cf_nick_modify = '{$_POST['cf_nick_modify']}',
                cf_new_skin = '{$_POST['cf_new_skin']}',
                cf_new_rows = '{$_POST['cf_new_rows']}',
                cf_search_skin = '{$_POST['cf_search_skin']}',
                cf_connect_skin = '{$_POST['cf_connect_skin']}',
                cf_faq_skin = '{$_POST['cf_faq_skin']}',
                cf_read_point = '{$_POST['cf_read_point']}',
                cf_write_point = '{$_POST['cf_write_point']}',
                cf_comment_point = '{$_POST['cf_comment_point']}',
                cf_download_point = '{$_POST['cf_download_point']}',
                cf_write_pages = '{$_POST['cf_write_pages']}',
                cf_mobile_pages = '{$_POST['cf_mobile_pages']}',
                cf_link_target = '{$_POST['cf_link_target']}',
                cf_delay_sec = '{$_POST['cf_delay_sec']}',
                cf_filter = '{$_POST['cf_filter']}',
                cf_possible_ip = '" . trim($_POST['cf_possible_ip']) . "',
                cf_intercept_ip = '" . trim($_POST['cf_intercept_ip']) . "',
                cf_analytics = '{$_POST['cf_analytics']}',
                cf_add_meta = '{$_POST['cf_add_meta']}',
                cf_syndi_token = '{$_POST['cf_syndi_token']}',
                cf_syndi_except = '{$_POST['cf_syndi_except']}',
                cf_bbs_rewrite = '{$_POST['cf_bbs_rewrite']}',
                cf_member_skin = '{$_POST['cf_member_skin']}',
                cf_use_homepage = '{$_POST['cf_use_homepage']}',
                cf_req_homepage = '{$_POST['cf_req_homepage']}',
                cf_use_tel = '{$_POST['cf_use_tel']}',
                cf_req_tel = '{$_POST['cf_req_tel']}',
                cf_use_hp = '{$_POST['cf_use_hp']}',
                cf_req_hp = '{$_POST['cf_req_hp']}',
                cf_use_addr = '{$_POST['cf_use_addr']}',
                cf_req_addr = '{$_POST['cf_req_addr']}',
                cf_use_signature = '{$_POST['cf_use_signature']}',
                cf_req_signature = '{$_POST['cf_req_signature']}',
                cf_use_profile = '{$_POST['cf_use_profile']}',
                cf_req_profile = '{$_POST['cf_req_profile']}',
                cf_register_level = '{$_POST['cf_register_level']}',
                cf_register_point = '{$_POST['cf_register_point']}',
                cf_icon_level = '{$_POST['cf_icon_level']}',
                cf_use_recommend = '{$_POST['cf_use_recommend']}',
                cf_recommend_point = '{$_POST['cf_recommend_point']}',
                cf_leave_day = '{$_POST['cf_leave_day']}',
                cf_search_part = '{$_POST['cf_search_part']}',
                cf_email_use = '{$_POST['cf_email_use']}',
                cf_email_wr_super_admin = '{$_POST['cf_email_wr_super_admin']}',
                cf_email_wr_group_admin = '{$_POST['cf_email_wr_group_admin']}',
                cf_email_wr_board_admin = '{$_POST['cf_email_wr_board_admin']}',
                cf_email_wr_write = '{$_POST['cf_email_wr_write']}',
                cf_email_wr_comment_all = '{$_POST['cf_email_wr_comment_all']}',
                cf_email_mb_super_admin = '{$_POST['cf_email_mb_super_admin']}',
                cf_email_mb_member = '{$_POST['cf_email_mb_member']}',
                cf_email_po_super_admin = '{$_POST['cf_email_po_super_admin']}',
                cf_prohibit_id = '{$_POST['cf_prohibit_id']}',
                cf_prohibit_email = '{$_POST['cf_prohibit_email']}',
                cf_new_del = '{$_POST['cf_new_del']}',
                cf_memo_del = '{$_POST['cf_memo_del']}',
                cf_visit_del = '{$_POST['cf_visit_del']}',
                cf_popular_del = '{$_POST['cf_popular_del']}',
                cf_use_member_icon = '{$_POST['cf_use_member_icon']}',
                cf_member_icon_size = '{$_POST['cf_member_icon_size']}',
                cf_member_icon_width = '{$_POST['cf_member_icon_width']}',
                cf_member_icon_height = '{$_POST['cf_member_icon_height']}',
                cf_member_img_size = '{$_POST['cf_member_img_size']}',
                cf_member_img_width = '{$_POST['cf_member_img_width']}',
                cf_member_img_height = '{$_POST['cf_member_img_height']}',
                cf_login_minutes = '{$_POST['cf_login_minutes']}',
                cf_image_extension = '{$_POST['cf_image_extension']}',
                cf_flash_extension = '{$_POST['cf_flash_extension']}',
                cf_movie_extension = '{$_POST['cf_movie_extension']}',
                cf_formmail_is_member = '{$_POST['cf_formmail_is_member']}',
                cf_page_rows = '{$_POST['cf_page_rows']}',
                cf_mobile_page_rows = '{$_POST['cf_mobile_page_rows']}',
                cf_stipulation = '{$_POST['cf_stipulation']}',
                cf_privacy = '{$_POST['cf_privacy']}',
                cf_open_modify = '{$_POST['cf_open_modify']}',
                cf_memo_send_point = '{$_POST['cf_memo_send_point']}',
                cf_mobile_new_skin = '{$_POST['cf_mobile_new_skin']}',
                cf_mobile_search_skin = '{$_POST['cf_mobile_search_skin']}',
                cf_mobile_connect_skin = '{$_POST['cf_mobile_connect_skin']}',
                cf_mobile_faq_skin = '{$_POST['cf_mobile_faq_skin']}',
                cf_mobile_member_skin = '{$_POST['cf_mobile_member_skin']}',
                cf_captcha_mp3 = '{$_POST['cf_captcha_mp3']}',
                cf_editor = '{$_POST['cf_editor']}',
                cf_cert_use = '{$_POST['cf_cert_use']}',
                cf_cert_find = '{$_POST['cf_cert_find']}',
                cf_cert_ipin = '{$_POST['cf_cert_ipin']}',
                cf_cert_hp = '{$_POST['cf_cert_hp']}',
                cf_cert_simple = '{$_POST['cf_cert_simple']}',
                cf_cert_kg_cd = '{$_POST['cf_cert_kg_cd']}',
                cf_cert_kg_mid = '" . trim($_POST['cf_cert_kg_mid']) . "',
                cf_cert_kcb_cd = '{$_POST['cf_cert_kcb_cd']}',
                cf_cert_kcp_cd = '{$_POST['cf_cert_kcp_cd']}',
                cf_cert_limit = '{$_POST['cf_cert_limit']}',
                cf_cert_req = '{$_POST['cf_cert_req']}',
                cf_sms_use = '{$_POST['cf_sms_use']}',
                cf_sms_type = '{$_POST['cf_sms_type']}',
                cf_icode_id = '{$_POST['cf_icode_id']}',
                cf_icode_pw = '{$_POST['cf_icode_pw']}',
                cf_icode_token_key = '{$_POST['cf_icode_token_key']}',
                cf_icode_server_ip = '{$_POST['cf_icode_server_ip']}',
                cf_icode_server_port = '{$_POST['cf_icode_server_port']}',
                cf_googl_shorturl_apikey = '{$_POST['cf_googl_shorturl_apikey']}',
                cf_kakao_js_apikey = '{$_POST['cf_kakao_js_apikey']}',
                cf_facebook_appid = '{$_POST['cf_facebook_appid']}',
                cf_facebook_secret = '{$_POST['cf_facebook_secret']}',
                cf_twitter_key = '{$_POST['cf_twitter_key']}',
                cf_twitter_secret = '{$_POST['cf_twitter_secret']}',
                cf_social_login_use = '{$_POST['cf_social_login_use']}',
                cf_naver_clientid = '{$_POST['cf_naver_clientid']}',
                cf_naver_secret = '{$_POST['cf_naver_secret']}',
                cf_google_clientid = '{$_POST['cf_google_clientid']}',
                cf_google_secret = '{$_POST['cf_google_secret']}',
                cf_kakao_rest_key = '{$_POST['cf_kakao_rest_key']}',
                cf_kakao_client_secret = '{$_POST['cf_kakao_client_secret']}',
                cf_social_servicelist   =   '{$cf_social_servicelist}',
                cf_captcha = '{$_POST['cf_captcha']}',
                cf_recaptcha_site_key = '{$_POST['cf_recaptcha_site_key']}',
                cf_recaptcha_secret_key   =   '{$_POST['cf_recaptcha_secret_key']}',
                cf_payco_clientid = '{$_POST['cf_payco_clientid']}',
                cf_payco_secret = '{$_POST['cf_payco_secret']}',
                cf_1_subj = '{$_POST['cf_1_subj']}',
                cf_2_subj = '{$_POST['cf_2_subj']}',
                cf_3_subj = '{$_POST['cf_3_subj']}',
                cf_4_subj = '{$_POST['cf_4_subj']}',
                cf_5_subj = '{$_POST['cf_5_subj']}',
                cf_6_subj = '{$_POST['cf_6_subj']}',
                cf_7_subj = '{$_POST['cf_7_subj']}',
                cf_8_subj = '{$_POST['cf_8_subj']}',
                cf_9_subj = '{$_POST['cf_9_subj']}',
                cf_10_subj = '{$_POST['cf_10_subj']}',
                cf_1 = '{$_POST['cf_1']}',
                cf_2 = '{$_POST['cf_2']}',
                cf_3 = '{$_POST['cf_3']}',
                cf_4 = '{$_POST['cf_4']}',
                cf_5 = '{$_POST['cf_5']}',
                cf_6 = '{$_POST['cf_6']}',
                cf_7 = '{$_POST['cf_7']}',
                cf_8 = '{$_POST['cf_8']}',
                cf_9 = '{$_POST['cf_9']}',
                cf_10 = '{$_POST['cf_10']}' ";
sql_query($sql);

//sql_query(" OPTIMIZE TABLE `$g5[config_table]` ");

if (isset($_POST['cf_bbs_rewrite'])) {
    g5_delete_all_cache();
}

run_event('admin_config_form_update');

update_rewrite_rules();

goto_url('./config_form.php', false);
