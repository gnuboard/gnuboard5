<?php
$sub_menu = "100100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

if (!isset($config['cf_add_script'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_add_script` TEXT NOT NULL AFTER `cf_admin_email_name` ", true);
}

if (!isset($config['cf_mobile_new_skin'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_mobile_new_skin` VARCHAR(255) NOT NULL AFTER `cf_memo_send_point`,
                    ADD `cf_mobile_search_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_new_skin`,
                    ADD `cf_mobile_connect_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_search_skin`,
                    ADD `cf_mobile_member_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_connect_skin` ", true);
}

if (isset($config['cf_gcaptcha_mp3'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    CHANGE `cf_gcaptcha_mp3` `cf_captcha_mp3` VARCHAR(255) NOT NULL DEFAULT '' ", true);
} else if (!isset($config['cf_captcha_mp3'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_captcha_mp3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_mobile_member_skin` ", true);
}

if(!isset($config['cf_editor'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_editor` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_captcha_mp3` ", true);
}

if(!isset($config['cf_googl_shorturl_apikey'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_googl_shorturl_apikey` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_captcha_mp3` ", true);
}

if(!isset($config['cf_mobile_pages'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_mobile_pages` INT(11) NOT NULL DEFAULT '0' AFTER `cf_write_pages` ", true);
    sql_query(" UPDATE `{$g5['config_table']}` SET cf_mobile_pages = '5' ", true);
}

if(!isset($config['cf_facebook_appid'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_facebook_appid` VARCHAR(255) NOT NULL AFTER `cf_googl_shorturl_apikey`,
                    ADD `cf_facebook_secret` VARCHAR(255) NOT NULL AFTER `cf_facebook_appid`,
                    ADD `cf_twitter_key` VARCHAR(255) NOT NULL AFTER `cf_facebook_secret`,
                    ADD `cf_twitter_secret` VARCHAR(255) NOT NULL AFTER `cf_twitter_key` ", true);
}

// uniqid 테이블이 없을 경우 생성
if(!sql_query(" DESC {$g5['uniqid_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['uniqid_table']}` (
                  `uq_id` bigint(20) unsigned NOT NULL,
                  `uq_ip` varchar(255) NOT NULL,
                  PRIMARY KEY (`uq_id`)
                ) ", false);
}

if(!sql_query(" SELECT uq_ip from {$g5['uniqid_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE {$g5['uniqid_table']} ADD `uq_ip` VARCHAR(255) NOT NULL ");
}

// 임시저장 테이블이 없을 경우 생성
if(!sql_query(" DESC {$g5['autosave_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['autosave_table']}` (
                  `as_id` int(11) NOT NULL AUTO_INCREMENT,
                  `mb_id` varchar(20) NOT NULL,
                  `as_uid` bigint(20) unsigned NOT NULL,
                  `as_subject` varchar(255) NOT NULL,
                  `as_content` text NOT NULL,
                  `as_datetime` datetime NOT NULL,
                  PRIMARY KEY (`as_id`),
                  UNIQUE KEY `as_uid` (`as_uid`),
                  KEY `mb_id` (`mb_id`)
                ) ", false);
}

if(!isset($config['cf_admin_email'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_admin_email` VARCHAR(255) NOT NULL AFTER `cf_admin` ", true);
}

if(!isset($config['cf_admin_email_name'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_admin_email_name` VARCHAR(255) NOT NULL AFTER `cf_admin_email` ", true);
}

if(!isset($config['cf_cert_use'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_cert_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `cf_editor`,
                    ADD `cf_cert_ipin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_cert_use`,
                    ADD `cf_cert_hp` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_cert_ipin`,
                    ADD `cf_cert_kcb_cd` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_cert_hp`,
                    ADD `cf_cert_kcp_cd` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_cert_kcb_cd`,
                    ADD `cf_cert_limit` INT(11) NOT NULL DEFAULT '0' AFTER `cf_cert_kcp_cd` ", true);
    sql_query(" ALTER TABLE `{$g5['member_table']}`
                    CHANGE `mb_hp_certify` `mb_certify` VARCHAR(20) NOT NULL DEFAULT '' ", true);
    sql_query(" update {$g5['member_table']} set mb_certify = 'hp' where mb_certify = '1' ");
    sql_query(" update {$g5['member_table']} set mb_certify = '' where mb_certify = '0' ");
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['cert_history_table']}` (
                  `cr_id` int(11) NOT NULL auto_increment,
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `cr_company` varchar(255) NOT NULL DEFAULT '',
                  `cr_method` varchar(255) NOT NULL DEFAULT '',
                  `cr_ip` varchar(255) NOT NULL DEFAULT '',
                  `cr_date` date NOT NULL DEFAULT '0000-00-00',
                  `cr_time` time NOT NULL DEFAULT '00:00:00',
                  PRIMARY KEY (`cr_id`),
                  KEY `mb_id` (`mb_id`)
                )", true);
}

if(!isset($config['cf_analytics'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_analytics` TEXT NOT NULL AFTER `cf_intercept_ip` ", true);
}

if(!isset($config['cf_add_meta'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_add_meta` TEXT NOT NULL AFTER `cf_analytics` ", true);
}

if (!isset($config['cf_syndi_token'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_syndi_token` VARCHAR(255) NOT NULL AFTER `cf_add_meta` ", true);
}

if (!isset($config['cf_syndi_except'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_syndi_except` TEXT NOT NULL AFTER `cf_syndi_token` ", true);
}

if(!isset($config['cf_sms_use'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_sms_use` varchar(255) NOT NULL DEFAULT '' AFTER `cf_cert_limit`,
                    ADD `cf_icode_id` varchar(255) NOT NULL DEFAULT '' AFTER `cf_sms_use`,
                    ADD `cf_icode_pw` varchar(255) NOT NULL DEFAULT '' AFTER `cf_icode_id`,
                    ADD `cf_icode_server_ip` varchar(255) NOT NULL DEFAULT '' AFTER `cf_icode_pw`,
                    ADD `cf_icode_server_port` varchar(255) NOT NULL DEFAULT '' AFTER `cf_icode_server_ip` ", true);
}

if(!isset($config['cf_mobile_page_rows'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_mobile_page_rows` int(11) NOT NULL DEFAULT '0' AFTER `cf_page_rows` ", true);
}

if(!isset($config['cf_cert_req'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_cert_req` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_cert_limit` ", true);
}

if(!isset($config['cf_faq_skin'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_faq_skin` varchar(255) NOT NULL DEFAULT '' AFTER `cf_connect_skin`,
                    ADD `cf_mobile_faq_skin` varchar(255) NOT NULL DEFAULT '' AFTER `cf_mobile_connect_skin` ", true);
}

// LG유플러스 본인확인 필드 추가
if(!isset($config['cf_lg_mid'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_lg_mid` varchar(255) NOT NULL DEFAULT '' AFTER `cf_cert_kcp_cd`,
                    ADD `cf_lg_mert_key` varchar(255) NOT NULL DEFAULT '' AFTER `cf_lg_mid` ", true);
}

if(!isset($config['cf_optimize_date'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_optimize_date` date NOT NULL default '0000-00-00' AFTER `cf_popular_del` ", true);
}

// 카카오톡링크 api 키
if(!isset($config['cf_kakao_js_apikey'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_kakao_js_apikey` varchar(255) NOT NULL DEFAULT '' AFTER `cf_googl_shorturl_apikey` ", true);
}

// SMS 전송유형 필드 추가
if(!isset($config['cf_sms_type'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_sms_type` varchar(10) NOT NULL DEFAULT '' AFTER `cf_sms_use` ", true);
}

// 접속자 정보 필드 추가
if(!sql_query(" select vi_browser from {$g5['visit_table']} limit 1 ")) {
    sql_query(" ALTER TABLE `{$g5['visit_table']}`
                    ADD `vi_browser` varchar(255) NOT NULL DEFAULT '' AFTER `vi_agent`,
                    ADD `vi_os` varchar(255) NOT NULL DEFAULT '' AFTER `vi_browser`,
                    ADD `vi_device` varchar(255) NOT NULL DEFAULT '' AFTER `vi_os` ", true);
}

//소셜 로그인 관련 필드 및 구글 리챕챠 필드 추가
if(!isset($config['cf_social_login_use'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}`
                ADD `cf_social_login_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_googl_shorturl_apikey`,
                ADD `cf_google_clientid` varchar(100) NOT NULL DEFAULT '' AFTER `cf_twitter_secret`,
                ADD `cf_google_secret` varchar(100) NOT NULL DEFAULT '' AFTER `cf_google_clientid`,
                ADD `cf_naver_clientid` varchar(100) NOT NULL DEFAULT '' AFTER `cf_google_secret`,
                ADD `cf_naver_secret` varchar(100) NOT NULL DEFAULT '' AFTER `cf_naver_clientid`,
                ADD `cf_kakao_rest_key` varchar(100) NOT NULL DEFAULT '' AFTER `cf_naver_secret`,
                ADD `cf_social_servicelist` varchar(255) NOT NULL DEFAULT '' AFTER `cf_social_login_use`,
                ADD `cf_payco_clientid` varchar(100) NOT NULL DEFAULT '' AFTER `cf_social_servicelist`,
                ADD `cf_payco_secret` varchar(100) NOT NULL DEFAULT '' AFTER `cf_payco_clientid`,
                ADD `cf_captcha` varchar(100) NOT NULL DEFAULT '' AFTER `cf_kakao_js_apikey`,
                ADD `cf_recaptcha_site_key` varchar(100) NOT NULL DEFAULT '' AFTER `cf_captcha`,
                ADD `cf_recaptcha_secret_key` varchar(100) NOT NULL DEFAULT '' AFTER `cf_recaptcha_site_key`
    ", true);
}

//소셜 로그인 관련 필드 카카오 클라이언트 시크릿 추가
if(!isset($config['cf_kakao_client_secret'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}`
                ADD `cf_kakao_client_secret` varchar(100) NOT NULL DEFAULT '' AFTER `cf_kakao_rest_key`
    ", true);
}

// 회원 이미지 관련 필드 추가
if(!isset($config['cf_member_img_size'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}`
                ADD `cf_member_img_size` int(11) NOT NULL DEFAULT '0' AFTER `cf_member_icon_height`,
                ADD `cf_member_img_width` int(11) NOT NULL DEFAULT '0' AFTER `cf_member_img_size`,
                ADD `cf_member_img_height` int(11) NOT NULL DEFAULT '0' AFTER `cf_member_img_width`
    ", true);

    $sql = " update {$g5['config_table']} set cf_member_img_size = 50000, cf_member_img_width = 60, cf_member_img_height = 60 ";
    sql_query($sql, false);

    $config['cf_member_img_size'] = 50000;
    $config['cf_member_img_width'] = 60;
    $config['cf_member_img_height'] = 60;
}

// 소셜 로그인 관리 테이블 없을 경우 생성
if(!sql_query(" DESC {$g5['social_profile_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['social_profile_table']}` (
                  `mp_no` int(11) NOT NULL AUTO_INCREMENT,
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `provider` varchar(50) NOT NULL DEFAULT '',
                  `object_sha` varchar(45) NOT NULL DEFAULT '',
                  `identifier` varchar(255) NOT NULL DEFAULT '',
                  `profileurl` varchar(255) NOT NULL DEFAULT '',
                  `photourl` varchar(255) NOT NULL DEFAULT '',
                  `displayname` varchar(150) NOT NULL DEFAULT '',
                  `description` varchar(255) NOT NULL DEFAULT '',
                  `mp_register_day` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `mp_latest_day` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  UNIQUE KEY `mp_no` (`mp_no`),
                  KEY `mb_id` (`mb_id`),
                  KEY `provider` (`provider`)
                ) ", true);
}

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '환경설정';
include_once ('./admin.head.php');

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_basic">기본환경</a></li>
    <li><a href="#anc_cf_board">게시판기본</a></li>
    <li><a href="#anc_cf_join">회원가입</a></li>
    <li><a href="#anc_cf_cert">본인확인</a></li>
    <li><a href="#anc_cf_mail">기본메일환경</a></li>
    <li><a href="#anc_cf_article_mail">글작성메일</a></li>
    <li><a href="#anc_cf_join_mail">가입메일</a></li>
    <li><a href="#anc_cf_vote_mail">투표메일</a></li>
    <li><a href="#anc_cf_sns">SNS</a></li>
    <li><a href="#anc_cf_lay">레이아웃 추가설정</a></li>
    <li><a href="#anc_cf_sms">SMS</a></li>
    <li><a href="#anc_cf_extra">여분필드</a></li>
</ul>';


if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
    $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_basic">
    <h2 class="h2_frm">홈페이지 기본환경 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>홈페이지 기본환경 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_title">홈페이지 제목<strong class="sound_only">필수</strong></label></th>
            <td colspan="3"><input type="text" name="cf_title" value="<?php echo $config['cf_title'] ?>" id="cf_title" required class="required frm_input" size="40"></td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_admin">최고관리자<strong class="sound_only">필수</strong></label></th>
            <td colspan="3"><?php echo get_member_id_select('cf_admin', 10, $config['cf_admin'], 'required') ?></td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_admin_email">관리자 메일 주소<strong class="sound_only">필수</strong></label></th>
            <td colspan="3">
                <?php echo help('관리자가 보내고 받는 용도로 사용하는 메일 주소를 입력합니다. (회원가입, 인증메일, 테스트, 회원메일발송 등에서 사용)') ?>
                <input type="text" name="cf_admin_email" value="<?php echo $config['cf_admin_email'] ?>" id="cf_admin_email" required class="required email frm_input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_admin_email_name">관리자 메일 발송이름<strong class="sound_only">필수</strong></label></th>
            <td colspan="3">
                <?php echo help('관리자가 보내고 받는 용도로 사용하는 메일의 발송이름을 입력합니다. (회원가입, 인증메일, 테스트, 회원메일발송 등에서 사용)') ?>
                <input type="text" name="cf_admin_email_name" value="<?php echo $config['cf_admin_email_name'] ?>" id="cf_admin_email_name" required class="required frm_input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
            <td colspan="3"><input type="checkbox" name="cf_use_point" value="1" id="cf_use_point" <?php echo $config['cf_use_point']?'checked':''; ?>> 사용</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_login_point">로그인시 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('회원이 로그인시 하루에 한번만 적립') ?>
                <input type="text" name="cf_login_point" value="<?php echo $config['cf_login_point'] ?>" id="cf_login_point" required class="required frm_input" size="5"> 점
            </td>
            <th scope="row"><label for="cf_memo_send_point">쪽지보낼시 차감 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                 <?php echo help('양수로 입력하십시오. 0점은 쪽지 보낼시 포인트를 차감하지 않습니다.') ?>
                <input type="text" name="cf_memo_send_point" value="<?php echo $config['cf_memo_send_point'] ?>" id="cf_memo_send_point" required class="required frm_input" size="5"> 점
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_cut_name">이름(닉네임) 표시</label></th>
            <td colspan="3">
                <input type="text" name="cf_cut_name" value="<?php echo $config['cf_cut_name'] ?>" id="cf_cut_name" class="frm_input" size="5"> 자리만 표시
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_nick_modify">닉네임 수정</label></th>
            <td>수정하면 <input type="text" name="cf_nick_modify" value="<?php echo $config['cf_nick_modify'] ?>" id="cf_nick_modify" class="frm_input" size="3"> 일 동안 바꿀 수 없음</td>
            <th scope="row"><label for="cf_open_modify">정보공개 수정</label></th>
            <td>수정하면 <input type="text" name="cf_open_modify" value="<?php echo $config['cf_open_modify'] ?>" id="cf_open_modify" class="frm_input" size="3"> 일 동안 바꿀 수 없음</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_new_del">최근게시물 삭제</label></th>
            <td>
                <?php echo help('설정일이 지난 최근게시물 자동 삭제') ?>
                <input type="text" name="cf_new_del" value="<?php echo $config['cf_new_del'] ?>" id="cf_new_del" class="frm_input" size="5"> 일
            </td>
            <th scope="row"><label for="cf_memo_del">쪽지 삭제</label></th>
            <td>
                <?php echo help('설정일이 지난 쪽지 자동 삭제') ?>
                <input type="text" name="cf_memo_del" value="<?php echo $config['cf_memo_del'] ?>" id="cf_memo_del" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_visit_del">접속자로그 삭제</label></th>
            <td>
                <?php echo help('설정일이 지난 접속자 로그 자동 삭제') ?>
                <input type="text" name="cf_visit_del" value="<?php echo $config['cf_visit_del'] ?>" id="cf_visit_del" class="frm_input" size="5"> 일
            </td>
            <th scope="row"><label for="cf_popular_del">인기검색어 삭제</label></th>
            <td>
                <?php echo help('설정일이 지난 인기검색어 자동 삭제') ?>
                <input type="text" name="cf_popular_del" value="<?php echo $config['cf_popular_del'] ?>" id="cf_popular_del" class="frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_login_minutes">현재 접속자</label></th>
            <td>
                <?php echo help('설정값 이내의 접속자를 현재 접속자로 인정') ?>
                <input type="text" name="cf_login_minutes" value="<?php echo $config['cf_login_minutes'] ?>" id="cf_login_minutes" class="frm_input" size="3"> 분
            </td>
            <th scope="row"><label for="cf_new_rows">최근게시물 라인수</label></th>
            <td>
                <?php echo help('목록 한페이지당 라인수') ?>
                <input type="text" name="cf_new_rows" value="<?php echo $config['cf_new_rows'] ?>" id="cf_new_rows" class="frm_input" size="3"> 라인
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_page_rows">한페이지당 라인수</label></th>
            <td>
                <?php echo help('목록(리스트) 한페이지당 라인수') ?>
                <input type="text" name="cf_page_rows" value="<?php echo $config['cf_page_rows'] ?>" id="cf_page_rows" class="frm_input" size="3"> 라인
            </td>
            <th scope="row"><label for="cf_mobile_page_rows">모바일 한페이지당 라인수</label></th>
            <td>
                <?php echo help('모바일 목록 한페이지당 라인수') ?>
                <input type="text" name="cf_mobile_page_rows" value="<?php echo $config['cf_mobile_page_rows'] ?>" id="cf_mobile_page_rows" class="frm_input" size="3"> 라인
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_write_pages">페이지 표시 수<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="cf_write_pages" value="<?php echo $config['cf_write_pages'] ?>" id="cf_write_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
            <th scope="row"><label for="cf_mobile_pages">모바일 페이지 표시 수<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="cf_mobile_pages" value="<?php echo $config['cf_mobile_pages'] ?>" id="cf_mobile_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_new_skin">최근게시물 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('new', 'cf_new_skin', 'cf_new_skin', $config['cf_new_skin'], 'required'); ?>
            </td>
            <th scope="row"><label for="cf_mobile_new_skin">모바일<br>최근게시물 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('new', 'cf_mobile_new_skin', 'cf_mobile_new_skin', $config['cf_mobile_new_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_search_skin">검색 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('search', 'cf_search_skin', 'cf_search_skin', $config['cf_search_skin'], 'required'); ?>
            </td>
            <th scope="row"><label for="cf_mobile_search_skin">모바일 검색 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('search', 'cf_mobile_search_skin', 'cf_mobile_search_skin', $config['cf_mobile_search_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_connect_skin">접속자 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('connect', 'cf_connect_skin', 'cf_connect_skin', $config['cf_connect_skin'], 'required'); ?>
            </td>
            <th scope="row"><label for="cf_mobile_connect_skin">모바일 접속자 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('connect', 'cf_mobile_connect_skin', 'cf_mobile_connect_skin', $config['cf_mobile_connect_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_faq_skin">FAQ 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('faq', 'cf_faq_skin', 'cf_faq_skin', $config['cf_faq_skin'], 'required'); ?>
            </td>
            <th scope="row"><label for="cf_mobile_faq_skin">모바일 FAQ 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('faq', 'cf_mobile_faq_skin', 'cf_mobile_faq_skin', $config['cf_mobile_faq_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_editor">에디터 선택</label></th>
            <td colspan="3">
                <?php echo help(G5_EDITOR_URL.' 밑의 DHTML 에디터 폴더를 선택합니다.') ?>
                <select name="cf_editor" id="cf_editor">
                <?php
                $arr = get_skin_dir('', G5_EDITOR_PATH);
                for ($i=0; $i<count($arr); $i++) {
                    if ($i == 0) echo "<option value=\"\">사용안함</option>";
                    echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_editor'], $arr[$i]).">".$arr[$i]."</option>\n";
                }
                ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_captcha">캡챠 선택<strong class="sound_only">필수</strong></label></th>
            <td colspan="3">
                <?php echo help('사용할 캡챠를 선택합니다.<br>1) Kcaptcha 는 그누보드5의 기본캡챠입니다. ( 문자입력 )<br>2) reCAPTCHA V2 는 구글에서 서비스하는 원클릭 형식의 간편한 캡챠입니다. ( 모바일 친화적 UI )<br>3) Invisible reCAPTCHA 는 구글에서 서비스하는 안보이는 형식의 캡챠입니다. ( 간혹 퀴즈를 풀어야 합니다. )<br>') ?>
                <select name="cf_captcha" id="cf_captcha" required class="required">
                <option value="kcaptcha" <?php echo get_selected($config['cf_captcha'], 'kcaptcha') ; ?>>Kcaptcha</option>
                <option value="recaptcha" <?php echo get_selected($config['cf_captcha'], 'recaptcha') ; ?>>reCAPTCHA V2</option>
                <option value="recaptcha_inv" <?php echo get_selected($config['cf_captcha'], 'recaptcha_inv') ; ?>>Invisible reCAPTCHA</option>
                </select>
            </td>
        </tr>
        <tr class="kcaptcha_mp3">
            <th scope="row"><label for="cf_captcha_mp3">음성캡챠 선택<strong class="sound_only">필수</strong></label></th>
            <td colspan="3">
                <?php echo help('kcaptcha 사용시 '.str_replace(array('recaptcha_inv', 'recaptcha'), 'kcaptcha', G5_CAPTCHA_URL).'/mp3 밑의 음성 폴더를 선택합니다.') ?>
                <select name="cf_captcha_mp3" id="cf_captcha_mp3" required class="required">
                <?php
                $arr = get_skin_dir('mp3', str_replace(array('recaptcha_inv', 'recaptcha'), 'kcaptcha', G5_CAPTCHA_PATH));
                for ($i=0; $i<count($arr); $i++) {
                    if ($i == 0) echo "<option value=\"\">선택</option>";
                    echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_captcha_mp3'], $arr[$i]).">".$arr[$i]."</option>\n";
                }
                ?>
                </select>
            </td>
        </tr>
		<tr>
			<th scope="row"><label for="cf_recaptcha_site_key">구글 reCAPTCHA Site key</label></th>
			<td colspan="3">
            <?php echo help('reCAPTCHA V2와 Invisible reCAPTCHA 캡챠의 sitekey 와 secret 키는 동일하지 않고, 서로 발급받는 키가 다릅니다.') ?>
            <input type="text" name="cf_recaptcha_site_key" value="<?php echo $config['cf_recaptcha_site_key']; ?>" id="cf_recaptcha_site_key" class="frm_input" size="52"> <a href="https://www.google.com/recaptcha/admin" target="_blank" class="btn_frmline">reCAPTCHA 등록하기</a>
            </td>
		</tr>
		<tr>
            <th scope="row"><label for="cf_recaptcha_secret_key">구글 reCAPTCHA Secret key</label></th>
            <td colspan="3">
                <input type="text" name="cf_recaptcha_secret_key" value="<?php echo $config['cf_recaptcha_secret_key']; ?>" id="cf_recaptcha_secret_key" class="frm_input" size="52">
            </td>
		</tr>
        <tr>
            <th scope="row"><label for="cf_use_copy_log">복사, 이동시 로그</label></th>
            <td colspan="3">
                <?php echo help('게시물 아래에 누구로 부터 복사, 이동됨 표시') ?>
                <input type="checkbox" name="cf_use_copy_log" value="1" id="cf_use_copy_log" <?php echo $config['cf_use_copy_log']?'checked':''; ?>> 남김
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_point_term">포인트 유효기간</label></th>
            <td colspan="3">
                <?php echo help('기간을 0으로 설정시 포인트 유효기간이 적용되지 않습니다.') ?>
                <input type="text" name="cf_point_term" value="<?php echo $config['cf_point_term']; ?>" id="cf_point_term" required class="required frm_input" size="5"> 일
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_possible_ip">접근가능 IP</label></th>
            <td>
                <?php echo help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
                <textarea name="cf_possible_ip" id="cf_possible_ip"><?php echo $config['cf_possible_ip'] ?></textarea>
            </td>
            <th scope="row"><label for="cf_intercept_ip">접근차단 IP</label></th>
            <td>
                <?php echo help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
                <textarea name="cf_intercept_ip" id="cf_intercept_ip"><?php echo $config['cf_intercept_ip'] ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_analytics">방문자분석 스크립트</label></th>
            <td colspan="3">
                <?php echo help('방문자분석 스크립트 코드를 입력합니다. 예) 구글 애널리틱스'); ?>
                <textarea name="cf_analytics" id="cf_analytics"><?php echo $config['cf_analytics']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_add_meta">추가 메타태그</label></th>
            <td colspan="3">
                <?php echo help('추가로 사용하실 meta 태그를 입력합니다.'); ?>
                <textarea name="cf_add_meta" id="cf_add_meta"><?php echo $config['cf_add_meta']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_syndi_token">네이버 신디케이션 연동키</label></th>
            <td colspan="3">
                <?php if (!function_exists('curl_init')) echo help('<b>경고) curl이 지원되지 않아 네이버 신디케이션을 사용할수 없습니다.</b>'); ?>
                <?php echo help('네이버 신디케이션 연동키(token)을 입력하면 네이버 신디케이션을 사용할 수 있습니다.<br>연동키는 <a href="http://webmastertool.naver.com/" target="_blank"><u>네이버 웹마스터도구</u></a> -> 네이버 신디케이션에서 발급할 수 있습니다.') ?>
                <input type="text" name="cf_syndi_token" value="<?php echo $config['cf_syndi_token'] ?>" id="cf_syndi_token" class="frm_input" size="70">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_syndi_except">네이버 신디케이션 제외게시판</label></th>
            <td colspan="3">
                <?php echo help('네이버 신디케이션 수집에서 제외할 게시판 아이디를 | 로 구분하여 입력하십시오. 예) notice|adult<br>참고로 그룹접근사용 게시판, 글읽기 권한 2 이상 게시판, 비밀글은 신디케이션 수집에서 제외됩니다.') ?>
                <input type="text" name="cf_syndi_except" value="<?php echo $config['cf_syndi_except'] ?>" id="cf_syndi_except" class="frm_input" size="70">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>
<button type="button" class="get_theme_confc btn_02 btn" data-type="conf_skin" >테마 스킨설정 가져오기</button>

<section id="anc_cf_board">
    <h2 class="h2_frm">게시판 기본 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>각 게시판 관리에서 개별적으로 설정 가능합니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 기본 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_delay_sec">글쓰기 간격<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="cf_delay_sec" value="<?php echo $config['cf_delay_sec'] ?>" id="cf_delay_sec" required class="required numeric frm_input" size="3"> 초 지난후 가능</td>
            <th scope="row"><label for="cf_link_target">새창 링크</label></th>
            <td>
                <?php echo help('글내용중 자동 링크되는 타켓을 지정합니다.') ?>
                <select name="cf_link_target" id="cf_link_target">
                    <option value="_blank"<?php echo get_selected($config['cf_link_target'], '_blank') ?>>_blank</option>
                    <option value="_self"<?php echo get_selected($config['cf_link_target'], '_self') ?>>_self</option>
                    <option value="_top"<?php echo get_selected($config['cf_link_target'], '_top') ?>>_top</option>
                    <option value="_new"<?php echo get_selected($config['cf_link_target'], '_new') ?>>_new</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_read_point">글읽기 포인트<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="cf_read_point" value="<?php echo $config['cf_read_point'] ?>" id="cf_read_point" required class="required frm_input" size="3"> 점</td>
            <th scope="row"><label for="cf_write_point">글쓰기 포인트</label></th>
            <td><input type="text" name="cf_write_point" value="<?php echo $config['cf_write_point'] ?>" id="cf_write_point" required class="required frm_input" size="3"> 점</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_comment_point">댓글쓰기 포인트</label></th>
            <td><input type="text" name="cf_comment_point" value="<?php echo $config['cf_comment_point'] ?>" id="cf_comment_point" required class="required frm_input" size="3"> 점</td>
            <th scope="row"><label for="cf_download_point">다운로드 포인트</label></th>
            <td><input type="text" name="cf_download_point" value="<?php echo $config['cf_download_point'] ?>" id="cf_download_point" required class="required frm_input" size="3"> 점</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_search_part">검색 단위</label></th>
            <td colspan="3"><input type="text" name="cf_search_part" value="<?php echo $config['cf_search_part'] ?>" id="cf_search_part" class="frm_input" size="4"> 건 단위로 검색</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_image_extension">이미지 업로드 확장자</label></th>
            <td colspan="3">
                <?php echo help('게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분') ?>
                <input type="text" name="cf_image_extension" value="<?php echo $config['cf_image_extension'] ?>" id="cf_image_extension" class="frm_input" size="70">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_flash_extension">플래쉬 업로드 확장자</label></th>
            <td colspan="3">
                <?php echo help('게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분') ?>
                <input type="text" name="cf_flash_extension" value="<?php echo $config['cf_flash_extension'] ?>" id="cf_flash_extension" class="frm_input" size="70">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_movie_extension">동영상 업로드 확장자</label></th>
            <td colspan="3">
                <?php echo help('게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분') ?>
                <input type="text" name="cf_movie_extension" value="<?php echo $config['cf_movie_extension'] ?>" id="cf_movie_extension" class="frm_input" size="70">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_filter">단어 필터링</label></th>
            <td colspan="3">
                <?php echo help('입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분합니다.') ?>
                <textarea name="cf_filter" id="cf_filter" rows="7"><?php echo $config['cf_filter'] ?></textarea>
             </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_cf_join">
    <h2 class="h2_frm">회원가입 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>회원가입 시 사용할 스킨과 입력 받을 정보 등을 설정할 수 있습니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>회원가입 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_member_skin">회원 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('member', 'cf_member_skin', 'cf_member_skin', $config['cf_member_skin'], 'required'); ?>
            </td>
            <th scope="row"><label for="cf_mobile_member_skin">모바일<br>회원 스킨<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('member', 'cf_mobile_member_skin', 'cf_mobile_member_skin', $config['cf_mobile_member_skin'], 'required'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">홈페이지 입력</th>
            <td>
                <input type="checkbox" name="cf_use_homepage" value="1" id="cf_use_homepage" <?php echo $config['cf_use_homepage']?'checked':''; ?>> <label for="cf_use_homepage">보이기</label>
                <input type="checkbox" name="cf_req_homepage" value="1" id="cf_req_homepage" <?php echo $config['cf_req_homepage']?'checked':''; ?>> <label for="cf_req_homepage">필수입력</label>
            </td>
            <th scope="row">주소 입력</th>
            <td>
                <input type="checkbox" name="cf_use_addr" value="1" id="cf_use_addr" <?php echo $config['cf_use_addr']?'checked':''; ?>> <label for="cf_use_addr">보이기</label>
                <input type="checkbox" name="cf_req_addr" value="1" id="cf_req_addr" <?php echo $config['cf_req_addr']?'checked':''; ?>> <label for="cf_req_addr">필수입력</label>
            </td>
        </tr>
        <tr>
            <th scope="row">전화번호 입력</th>
            <td>
                <input type="checkbox" name="cf_use_tel" value="1" id="cf_use_tel" <?php echo $config['cf_use_tel']?'checked':''; ?>> <label for="cf_use_tel">보이기</label>
                <input type="checkbox" name="cf_req_tel" value="1" id="cf_req_tel" <?php echo $config['cf_req_tel']?'checked':''; ?>> <label for="cf_req_tel">필수입력</label>
            </td>
            <th scope="row">휴대폰번호 입력</th>
            <td>
                <input type="checkbox" name="cf_use_hp" value="1" id="cf_use_hp" <?php echo $config['cf_use_hp']?'checked':''; ?>> <label for="cf_use_hp">보이기</label>
                <input type="checkbox" name="cf_req_hp" value="1" id="cf_req_hp" <?php echo $config['cf_req_hp']?'checked':''; ?>> <label for="cf_req_hp">필수입력</label>
            </td>
        </tr>
        <tr>
            <th scope="row">서명 입력</th>
            <td>
                <input type="checkbox" name="cf_use_signature" value="1" id="cf_use_signature" <?php echo $config['cf_use_signature']?'checked':''; ?>> <label for="cf_use_signature">보이기</label>
                <input type="checkbox" name="cf_req_signature" value="1" id="cf_req_signature" <?php echo $config['cf_req_signature']?'checked':''; ?>> <label for="cf_req_signature">필수입력</label>
            </td>
            <th scope="row">자기소개 입력</th>
            <td>
                <input type="checkbox" name="cf_use_profile" value="1" id="cf_use_profile" <?php echo $config['cf_use_profile']?'checked':''; ?>> <label for="cf_use_profile">보이기</label>
                <input type="checkbox" name="cf_req_profile" value="1" id="cf_req_profile" <?php echo $config['cf_req_profile']?'checked':''; ?>> <label for="cf_req_profile">필수입력</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_register_level">회원가입시 권한</label></th>
            <td><?php echo get_member_level_select('cf_register_level', 1, 9, $config['cf_register_level']) ?></td>
            <th scope="row"><label for="cf_register_point">회원가입시 포인트</label></th>
            <td><input type="text" name="cf_register_point" value="<?php echo $config['cf_register_point'] ?>" id="cf_register_point" class="frm_input" size="5"> 점</td>
        </tr>
        <tr>
            <th scope="row" id="th310"><label for="cf_leave_day">회원탈퇴후 삭제일</label></th>
            <td colspan="3"><input type="text" name="cf_leave_day" value="<?php echo $config['cf_leave_day'] ?>" id="cf_leave_day" class="frm_input" size="2"> 일 후 자동 삭제</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_use_member_icon">회원아이콘 사용</label></th>
            <td>
                <?php echo help('게시물에 게시자 닉네임 대신 아이콘 사용') ?>
                <select name="cf_use_member_icon" id="cf_use_member_icon">
                    <option value="0"<?php echo get_selected($config['cf_use_member_icon'], '0') ?>>미사용
                    <option value="1"<?php echo get_selected($config['cf_use_member_icon'], '1') ?>>아이콘만 표시
                    <option value="2"<?php echo get_selected($config['cf_use_member_icon'], '2') ?>>아이콘+이름 표시
                </select>
            </td>
            <th scope="row"><label for="cf_icon_level">회원 아이콘, 이미지 업로드 권한</label></th>
            <td><?php echo get_member_level_select('cf_icon_level', 1, 9, $config['cf_icon_level']) ?> 이상</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_member_icon_size">회원아이콘 용량</label></th>
            <td><input type="text" name="cf_member_icon_size" value="<?php echo $config['cf_member_icon_size'] ?>" id="cf_member_icon_size" class="frm_input" size="10"> 바이트 이하</td>
            <th scope="row">회원아이콘 사이즈</th>
            <td>
                <label for="cf_member_icon_width">가로</label>
                <input type="text" name="cf_member_icon_width" value="<?php echo $config['cf_member_icon_width'] ?>" id="cf_member_icon_width" class="frm_input" size="2">
                <label for="cf_member_icon_height">세로</label>
                <input type="text" name="cf_member_icon_height" value="<?php echo $config['cf_member_icon_height'] ?>" id="cf_member_icon_height" class="frm_input" size="2">
                픽셀 이하
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_member_img_size">회원이미지 용량</label></th>
            <td><input type="text" name="cf_member_img_size" value="<?php echo $config['cf_member_img_size'] ?>" id="cf_member_img_size" class="frm_input" size="10"> 바이트 이하</td>
            <th scope="row">회원이미지 사이즈</th>
            <td>
                <label for="cf_member_img_width">가로</label>
                <input type="text" name="cf_member_img_width" value="<?php echo $config['cf_member_img_width'] ?>" id="cf_member_img_width" class="frm_input" size="2">
                <label for="cf_member_img_height">세로</label>
                <input type="text" name="cf_member_img_height" value="<?php echo $config['cf_member_img_height'] ?>" id="cf_member_img_height" class="frm_input" size="2">
                픽셀 이하
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_use_recommend">추천인제도 사용</label></th>
            <td><input type="checkbox" name="cf_use_recommend" value="1" id="cf_use_recommend" <?php echo $config['cf_use_recommend']?'checked':''; ?>> 사용</td>
            <th scope="row"><label for="cf_recommend_point">추천인 포인트</label></th>
            <td><input type="text" name="cf_recommend_point" value="<?php echo $config['cf_recommend_point'] ?>" id="cf_recommend_point" class="frm_input"> 점</td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_prohibit_id">아이디,닉네임 금지단어</label></th>
            <td>
                <?php echo help('회원아이디, 닉네임으로 사용할 수 없는 단어를 정합니다. 쉼표 (,) 로 구분') ?>
                <textarea name="cf_prohibit_id" id="cf_prohibit_id" rows="5"><?php echo $config['cf_prohibit_id'] ?></textarea>
            </td>
            <th scope="row"><label for="cf_prohibit_email">입력 금지 메일</label></th>
            <td>
                <?php echo help('입력 받지 않을 도메인을 지정합니다. 엔터로 구분 ex) hotmail.com') ?>
                <textarea name="cf_prohibit_email" id="cf_prohibit_email" rows="5"><?php echo $config['cf_prohibit_email'] ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_stipulation">회원가입약관</label></th>
            <td colspan="3"><textarea name="cf_stipulation" id="cf_stipulation" rows="10"><?php echo $config['cf_stipulation'] ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_privacy">개인정보처리방침</label></th>
            <td colspan="3"><textarea id="cf_privacy" name="cf_privacy" rows="10"><?php echo $config['cf_privacy'] ?></textarea></td>
        </tr>
        </tbody>
        </table>
    </div>
    <button type="button" class="get_theme_confc btn btn_02" data-type="conf_member">테마 회원스킨설정 가져오기</button>
</section>


<section id="anc_cf_cert">
    <h2 class="h2_frm">본인확인 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>
            회원가입 시 본인확인 수단을 설정합니다.<br>
            실명과 휴대폰 번호 그리고 본인확인 당시에 성인인지의 여부를 저장합니다.<br>
            게시판의 경우 본인확인 또는 성인여부를 따져 게시물 조회 및 쓰기 권한을 줄 수 있습니다.
        </p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>본인확인 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_cert_use">본인확인</label></th>
            <td>
                <select name="cf_cert_use" id="cf_cert_use">
                    <?php echo option_selected("0", $config['cf_cert_use'], "사용안함"); ?>
                    <?php echo option_selected("1", $config['cf_cert_use'], "테스트"); ?>
                    <?php echo option_selected("2", $config['cf_cert_use'], "실서비스"); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_ipin">아이핀 본인확인</label></th>
            <td class="cf_cert_service">
                <select name="cf_cert_ipin" id="cf_cert_ipin">
                    <?php echo option_selected("",    $config['cf_cert_ipin'], "사용안함"); ?>
                    <?php echo option_selected("kcb", $config['cf_cert_ipin'], "코리아크레딧뷰로(KCB) 아이핀"); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_hp">휴대폰 본인확인</label></th>
            <td class="cf_cert_service">
                <select name="cf_cert_hp" id="cf_cert_hp">
                    <?php echo option_selected("",    $config['cf_cert_hp'], "사용안함"); ?>
                    <?php echo option_selected("kcb", $config['cf_cert_hp'], "코리아크레딧뷰로(KCB) 휴대폰 본인확인"); ?>
                    <?php echo option_selected("kcp", $config['cf_cert_hp'], "NHN KCP 휴대폰 본인확인"); ?>
                    <?php echo option_selected("lg",  $config['cf_cert_hp'], "LG유플러스 휴대폰 본인확인"); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_kcb_cd">코리아크레딧뷰로<br>KCB 회원사ID</label></th>
            <td class="cf_cert_service">
                <?php echo help('KCB 회원사ID를 입력해 주십시오.<br>서비스에 가입되어 있지 않다면, KCB와 계약체결 후 회원사ID를 발급 받으실 수 있습니다.<br>이용하시려는 서비스에 대한 계약을 아이핀, 휴대폰 본인확인 각각 체결해주셔야 합니다.<br>아이핀 본인확인 테스트의 경우에는 KCB 회원사ID가 필요 없으나,<br>휴대폰 본인확인 테스트의 경우 KCB 에서 따로 발급 받으셔야 합니다.') ?>
                <input type="text" name="cf_cert_kcb_cd" value="<?php echo $config['cf_cert_kcb_cd'] ?>" id="cf_cert_kcb_cd" class="frm_input" size="20"> <a href="http://sir.kr/main/service/b_ipin.php" target="_blank" class="btn_frmline">KCB 아이핀 서비스 신청페이지</a>
                <a href="http://sir.kr/main/service/b_cert.php" target="_blank" class="btn_frmline">KCB 휴대폰 본인확인 서비스 신청페이지</a>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_kcp_cd">NHN KCP 사이트코드</label></th>
            <td class="cf_cert_service">
                <?php echo help('SM으로 시작하는 5자리 사이트 코드중 뒤의 3자리만 입력해 주십시오.<br>서비스에 가입되어 있지 않다면, 본인확인 서비스 신청페이지에서 서비스 신청 후 사이트코드를 발급 받으실 수 있습니다.') ?>
                <span class="sitecode">SM</span>
                <input type="text" name="cf_cert_kcp_cd" value="<?php echo $config['cf_cert_kcp_cd'] ?>" id="cf_cert_kcp_cd" class="frm_input" size="3"> <a href="http://sir.kr/main/service/p_cert.php" target="_blank" class="btn_frmline">NHN KCP 휴대폰 본인확인 서비스 신청페이지</a>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_lg_mid">LG유플러스 상점아이디</label></th>
            <td class="cf_cert_service">
                <?php echo help('LG유플러스 상점아이디 중 si_를 제외한 나머지 아이디만 입력해 주십시오.<br>서비스에 가입되어 있지 않다면, 본인확인 서비스 신청페이지에서 서비스 신청 후 상점아이디를 발급 받으실 수 있습니다.<br><strong>LG유플러스 휴대폰본인확인은 ActiveX 설치가 필요하므로 Internet Explorer 에서만 사용할 수 있습니다.</strong>') ?>
                <span class="sitecode">si_</span>
                <input type="text" name="cf_lg_mid" value="<?php echo $config['cf_lg_mid'] ?>" id="cf_lg_mid" class="frm_input" size="20"> <a href="http://sir.kr/main/service/lg_cert.php" target="_blank" class="btn_frmline">LG유플러스 본인확인 서비스 신청페이지</a>
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_lg_mert_key">LG유플러스 MERT KEY</label></th>
            <td class="cf_cert_service">
                <?php echo help('LG유플러스 상점MertKey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실 수 있습니다.') ?>
                <input type="text" name="cf_lg_mert_key" value="<?php echo $config['cf_lg_mert_key'] ?>" id="cf_lg_mert_key" class="frm_input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_limit">본인확인 이용제한</label></th>
            <td class="cf_cert_service">
                <?php echo help('하루동안 아이핀과 휴대폰 본인확인 인증 이용회수를 제한할 수 있습니다.<br>회수제한은 실서비스에서 아이핀과 휴대폰 본인확인 인증에 개별 적용됩니다.<br>0 으로 설정하시면 회수제한이 적용되지 않습니다.'); ?>
                <input type="text" name="cf_cert_limit" value="<?php echo $config['cf_cert_limit']; ?>" id="cf_cert_limit" class="frm_input" size="3"> 회
            </td>
        </tr>
        <tr>
            <th scope="row" class="cf_cert_service"><label for="cf_cert_req">본인확인 필수</label></th>
            <td class="cf_cert_service">
                <?php echo help('회원가입 때 본인확인을 필수로 할지 설정합니다. 필수로 설정하시면 본인확인을 하지 않은 경우 회원가입이 안됩니다.'); ?>
                <input type="checkbox" name="cf_cert_req" value="1" id="cf_cert_req"<?php echo get_checked($config['cf_cert_req'], 1); ?>> 예
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_mail">
    <h2 class="h2_frm">기본 메일 환경 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본 메일 환경 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_email_use">메일발송 사용</label></th>
            <td>
                <?php echo help('체크하지 않으면 메일발송을 아예 사용하지 않습니다. 메일 테스트도 불가합니다.') ?>
                <input type="checkbox" name="cf_email_use" value="1" id="cf_email_use" <?php echo $config['cf_email_use']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_use_email_certify">메일인증 사용</label></th>
            <td>
                <?php $tmp = !(defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL) ? '<br>( SNS를 이용한 소셜로그인 한 회원은 회원메일인증을 하지 않습니다. 일반회원에게만 해당됩니다. )' : ''; ?>
                <?php echo help('메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.'.$tmp); ?>
                <input type="checkbox" name="cf_use_email_certify" value="1" id="cf_use_email_certify" <?php echo $config['cf_use_email_certify']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_formmail_is_member">폼메일 사용 여부</label></th>
            <td>
                <?php echo help('체크하지 않으면 비회원도 사용 할 수 있습니다.') ?>
                <input type="checkbox" name="cf_formmail_is_member" value="1" id="cf_formmail_is_member" <?php echo $config['cf_formmail_is_member']?'checked':''; ?>> 회원만 사용
            </td>
        </tr>
        </table>
    </div>
</section>


<section id="anc_cf_article_mail">
    <h2 class="h2_frm">게시판 글 작성 시 메일 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 글 작성 시 메일 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_email_wr_super_admin">최고관리자</label></th>
            <td>
                <?php echo help('최고관리자에게 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_wr_super_admin" value="1" id="cf_email_wr_super_admin" <?php echo $config['cf_email_wr_super_admin']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_email_wr_group_admin">그룹관리자</label></th>
            <td>
                <?php echo help('그룹관리자에게 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_wr_group_admin" value="1" id="cf_email_wr_group_admin" <?php echo $config['cf_email_wr_group_admin']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_email_wr_board_admin">게시판관리자</label></th>
            <td>
                <?php echo help('게시판관리자에게 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_wr_board_admin" value="1" id="cf_email_wr_board_admin" <?php echo $config['cf_email_wr_board_admin']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_email_wr_write">원글작성자</label></th>
            <td>
                <?php echo help('게시자님께 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_wr_write" value="1" id="cf_email_wr_write" <?php echo $config['cf_email_wr_write']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_email_wr_comment_all">댓글작성자</label></th>
            <td>
                <?php echo help('원글에 댓글이 올라오는 경우 댓글 쓴 모든 분들께 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_wr_comment_all" value="1" id="cf_email_wr_comment_all" <?php echo $config['cf_email_wr_comment_all']?'checked':''; ?>> 사용
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_join_mail">
    <h2 class="h2_frm">회원가입 시 메일 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>회원가입 시 메일 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_email_mb_super_admin">최고관리자 메일발송</label></th>
            <td>
                <?php echo help('최고관리자에게 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_mb_super_admin" value="1" id="cf_email_mb_super_admin" <?php echo $config['cf_email_mb_super_admin']?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_email_mb_member">회원님께 메일발송</label></th>
            <td>
                <?php echo help('회원가입한 회원님께 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_mb_member" value="1" id="cf_email_mb_member" <?php echo $config['cf_email_mb_member']?'checked':''; ?>> 사용
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_vote_mail">
    <h2 class="h2_frm">투표 기타의견 작성 시 메일 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>투표 기타의견 작성 시 메일 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_email_po_super_admin">최고관리자 메일발송</label></th>
            <td>
                <?php echo help('최고관리자에게 메일을 발송합니다.') ?>
                <input type="checkbox" name="cf_email_po_super_admin" value="1" id="cf_email_po_super_admin" <?php echo $config['cf_email_po_super_admin']?'checked':''; ?>> 사용
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_sns">
    <h2 class="h2_frm">소셜네트워크서비스(SNS : Social Network Service)</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>소셜네트워크서비스 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_social_login_use">소셜로그인설정</label></th>
            <td colspan="3">
                <?php echo help('소셜로그인을 사용합니다. <a href="https://sir.kr/manual/g5/276" class="btn btn_03" target="_blank" style="margin-left:10px" >설정 관련 메뉴얼 보기</a> ') ?>
                <input type="checkbox" name="cf_social_login_use" value="1" id="cf_social_login_use" <?php echo (!empty($config['cf_social_login_use']))?'checked':''; ?>> 사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_social_servicelist">소셜로그인설정</label></th>
            <td colspan="3" class="social_config_explain">
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_naver" value="naver" <?php echo option_array_checked('naver', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_naver">네이버 로그인을 사용합니다</label>
                    <div>
                    <h3>네이버 CallbackURL</h3>
                    <p><?php echo get_social_callbackurl('naver'); ?></p>
                    </div>
                </div>
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_kakao" value="kakao" <?php echo option_array_checked('kakao', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_kakao">카카오 로그인을 사용합니다</label>
                    <div>
                    <h3>카카오 웹 Redirect Path</h3>
                    <p><?php echo get_social_callbackurl('kakao', true); ?></p>
                    </div>
                </div>
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_facebook" value="facebook" <?php echo option_array_checked('facebook', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_facebook">페이스북 로그인을 사용합니다</label>
                    <div>
                    <h3>페이스북 유효한 OAuth 리디렉션 URI</h3>
                    <p><?php echo get_social_callbackurl('facebook'); ?></p>
                    </div>
                </div>
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_google" value="google" <?php echo option_array_checked('google', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_google">구글 로그인을 사용합니다</label>
                    <div>
                    <h3>구글 승인된 리디렉션 URI</h3>
                    <p><?php echo get_social_callbackurl('google'); ?></p>
                    </div>
                </div>
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_twitter" value="twitter" <?php echo option_array_checked('twitter', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_twitter">트위터 로그인을 사용합니다</label>
                    <div>
                    <h3>트위터 CallbackURL</h3>
                    <p><?php echo get_social_callbackurl('twitter'); ?></p>
                    </div>
                </div>
                <div class="explain_box">
                    <input type="checkbox" name="cf_social_servicelist[]" id="check_social_payco" value="payco" <?php echo option_array_checked('payco', $config['cf_social_servicelist']); ?> >
                    <label for="check_social_payco">페이코 로그인을 사용합니다</label>
                    <div>
                    <h3>페이코 CallbackURL</h3>
                    <p><?php echo get_social_callbackurl('payco'); ?></p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_naver_clientid">네이버 Client ID</label></th>
            <td>
                <input type="text" name="cf_naver_clientid" value="<?php echo $config['cf_naver_clientid'] ?>" id="cf_naver_clientid" class="frm_input" size="40"> <a href="https://developers.naver.com/apps/#/register" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_naver_secret">네이버 Client Secret</label></th>
            <td>
                <input type="text" name="cf_naver_secret" value="<?php echo $config['cf_naver_secret'] ?>" id="cf_naver_secret" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_facebook_appid">페이스북 앱 ID</label></th>
            <td>
                <input type="text" name="cf_facebook_appid" value="<?php echo $config['cf_facebook_appid'] ?>" id="cf_facebook_appid" class="frm_input" size="40"> <a href="https://developers.facebook.com/apps" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_facebook_secret">페이스북 앱 Secret</label></th>
            <td>
                <input type="text" name="cf_facebook_secret" value="<?php echo $config['cf_facebook_secret'] ?>" id="cf_facebook_secret" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_twitter_key">트위터 컨슈머 Key</label></th>
            <td>
                <input type="text" name="cf_twitter_key" value="<?php echo $config['cf_twitter_key'] ?>" id="cf_twitter_key" class="frm_input" size="40"> <a href="https://dev.twitter.com/apps" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_twitter_secret">트위터 컨슈머 Secret</label></th>
            <td>
                <input type="text" name="cf_twitter_secret" value="<?php echo $config['cf_twitter_secret'] ?>" id="cf_twitter_secret" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_google_clientid">구글 Client ID</label></th>
            <td>
                <input type="text" name="cf_google_clientid" value="<?php echo $config['cf_google_clientid'] ?>" id="cf_google_clientid" class="frm_input" size="40"> <a href="https://console.developers.google.com" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_google_secret">구글 Client Secret</label></th>
            <td>
                <input type="text" name="cf_google_secret" value="<?php echo $config['cf_google_secret'] ?>" id="cf_google_secret" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_googl_shorturl_apikey">구글 짧은주소 API Key</label></th>
            <td colspan="3">
                <input type="text" name="cf_googl_shorturl_apikey" value="<?php echo $config['cf_googl_shorturl_apikey'] ?>" id="cf_googl_shorturl_apikey" class="frm_input" size="40"> <a href="http://code.google.com/apis/console/" target="_blank" class="btn_frmline">API Key 등록하기</a>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_kakao_rest_key">카카오 REST API 키</label></th>
            <td>
                <input type="text" name="cf_kakao_rest_key" value="<?php echo $config['cf_kakao_rest_key'] ?>" id="cf_kakao_rest_key" class="frm_input" size="40"> <a href="https://developers.kakao.com/apps/new" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_kakao_client_secret">카카오 Client Secret</label></th>
            <td>
                <input type="text" name="cf_kakao_client_secret" value="<?php echo $config['cf_kakao_client_secret'] ?>" id="cf_kakao_client_secret" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_kakao_js_apikey">카카오 JavaScript 키</label></th>
            <td colspan="3">
                <input type="text" name="cf_kakao_js_apikey" value="<?php echo $config['cf_kakao_js_apikey'] ?>" id="cf_kakao_js_apikey" class="frm_input" size="45">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_payco_clientid">페이코 Client ID</label></th>
            <td>
                <input type="text" name="cf_payco_clientid" value="<?php echo $config['cf_payco_clientid']; ?>" id="cf_payco_clientid" class="frm_input" size="40"> <a href="https://developers.payco.com/guide" target="_blank" class="btn_frmline">앱 등록하기</a>
            </td>
            <th scope="row"><label for="cf_payco_secret">페이코 Secret</label></th>
            <td>
                <input type="text" name="cf_payco_secret" value="<?php echo $config['cf_payco_secret']; ?>" id="cf_payco_secret" class="frm_input" size="45">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_lay">
    <h2 class="h2_frm">레이아웃 추가설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>기본 설정된 파일 경로 및 script, css 를 추가하거나 변경할 수 있습니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>레이아웃 추가설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_add_script">추가 script, css</label></th>
            <td>
                <?php echo help('HTML의 &lt;/HEAD&gt; 태그위로 추가될 JavaScript와 css 코드를 설정합니다.<br>관리자 페이지에서는 이 코드를 사용하지 않습니다.') ?>
                <textarea name="cf_add_script" id="cf_add_script"><?php echo get_text($config['cf_add_script']); ?></textarea>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_sms">
    <h2 class="h2_frm">SMS</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>SMS 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="cf_sms_use">SMS 사용</label></th>
            <td>
                <select id="cf_sms_use" name="cf_sms_use">
                    <option value="" <?php echo get_selected($config['cf_sms_use'], ''); ?>>사용안함</option>
                    <option value="icode" <?php echo get_selected($config['cf_sms_use'], 'icode'); ?>>아이코드</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_sms_type">SMS 전송유형</label></th>
            <td>
                <?php echo help("전송유형을 SMS로 선택하시면 최대 80바이트까지 전송하실 수 있으며<br>LMS로 선택하시면 90바이트 이하는 SMS로, 그 이상은 1500바이트까지 LMS로 전송됩니다.<br>요금은 건당 SMS는 16원, LMS는 48원입니다."); ?>
                <select id="cf_sms_type" name="cf_sms_type">
                    <option value="" <?php echo get_selected($config['cf_sms_type'], ''); ?>>SMS</option>
                    <option value="LMS" <?php echo get_selected($config['cf_sms_type'], 'LMS'); ?>>LMS</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_icode_id">아이코드 회원아이디</label></th>
            <td>
                <?php echo help("아이코드에서 사용하시는 회원아이디를 입력합니다."); ?>
                <input type="text" name="cf_icode_id" value="<?php echo $config['cf_icode_id']; ?>" id="cf_icode_id" class="frm_input" size="20">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cf_icode_pw">아이코드 비밀번호</label></th>
            <td>
                <?php echo help("아이코드에서 사용하시는 비밀번호를 입력합니다."); ?>
                <input type="password" name="cf_icode_pw" value="<?php echo $config['cf_icode_pw']; ?>" id="cf_icode_pw" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row">요금제</th>
            <td>
                <input type="hidden" name="cf_icode_server_ip" value="<?php echo $config['cf_icode_server_ip']; ?>">
                <?php
                    if ($userinfo['payment'] == 'A') {
                       echo '충전제';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                    } else if ($userinfo['payment'] == 'C') {
                        echo '정액제';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7296">';
                    } else {
                        echo '가입해주세요.';
                        echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                    }
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row">아이코드 SMS 신청<br>회원가입</th>
            <td>
                <a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank" class="btn_frmline">아이코드 회원가입</a>
            </td>
        </tr>
         <?php if ($userinfo['payment'] == 'A') { ?>
        <tr>
            <th scope="row">충전 잔액</th>
            <td>
                <?php echo number_format($userinfo['coin']); ?> 원.
                <a href="http://www.icodekorea.com/smsbiz/credit_card_amt.php?icode_id=<?php echo $config['cf_icode_id']; ?>&amp;icode_passwd=<?php echo $config['cf_icode_pw']; ?>" target="_blank" class="btn_frmline">충전하기</a>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_cf_extra">
    <h2 class="h2_frm">여분필드 기본 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>각 게시판 관리에서 개별적으로 설정 가능합니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>여분필드 기본 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php for ($i=1; $i<=10; $i++) { ?>
        <tr>
            <th scope="row">여분필드<?php echo $i ?></th>
            <td class="td_extra">
                <label for="cf_<?php echo $i ?>_subj">여분필드<?php echo $i ?> 제목</label>
                <input type="text" name="cf_<?php echo $i ?>_subj" value="<?php echo get_text($config['cf_'.$i.'_subj']) ?>" id="cf_<?php echo $i ?>_subj" class="frm_input" size="30">
                <label for="cf_<?php echo $i ?>">여분필드<?php echo $i ?> 값</label>
                <input type="text" name="cf_<?php echo $i ?>" value="<?php echo $config['cf_'.$i] ?>" id="cf_<?php echo $i ?>" class="frm_input" size="30">
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){
    <?php
    if(!$config['cf_cert_use'])
        echo '$(".cf_cert_service").addClass("cf_cert_hide");';
    ?>
    $("#cf_cert_use").change(function(){
        switch($(this).val()) {
            case "0":
                $(".cf_cert_service").addClass("cf_cert_hide");
                break;
            default:
                $(".cf_cert_service").removeClass("cf_cert_hide");
                break;
        }
    });
    $("#cf_captcha").on("change", function(){
        if ($(this).val() == 'recaptcha' || $(this).val() == 'recaptcha_inv') {
            $("[class^='kcaptcha_']").hide();
        } else {
            $("[class^='kcaptcha_']").show();
        }
    }).trigger("change");

    $(".get_theme_confc").on("click", function() {
        var type = $(this).data("type");
        var msg = "기본환경 스킨 설정";
        if(type == "conf_member")
            msg = "기본환경 회원스킨 설정";

        if(!confirm("현재 테마의 "+msg+"을 적용하시겠습니까?"))
            return false;

        $.ajax({
            type: "POST",
            url: "./theme_config_load.php",
            cache: false,
            async: false,
            data: { type: type },
            dataType: "json",
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                var field = Array('cf_member_skin', 'cf_mobile_member_skin', 'cf_new_skin', 'cf_mobile_new_skin', 'cf_search_skin', 'cf_mobile_search_skin', 'cf_connect_skin', 'cf_mobile_connect_skin', 'cf_faq_skin', 'cf_mobile_faq_skin');
                var count = field.length;
                var key;

                for(i=0; i<count; i++) {
                    key = field[i];

                    if(data[key] != undefined && data[key] != "")
                        $("select[name="+key+"]").val(data[key]);
                }
            }
        });
    });
});

function fconfigform_submit(f)
{
    f.action = "./config_form_update.php";
    return true;
}
</script>

<?php
// 본인확인 모듈 실행권한 체크
if($config['cf_cert_use']) {
    // kcb일 때
    if($config['cf_cert_ipin'] == 'kcb' || $config['cf_cert_hp'] == 'kcb') {
        // 실행모듈
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $exe = G5_OKNAME_PATH.'/bin/okname';
            else
                $exe = G5_OKNAME_PATH.'/bin/okname_x64';
        } else {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $exe = G5_OKNAME_PATH.'/bin/okname.exe';
            else
                $exe = G5_OKNAME_PATH.'/bin/oknamex64.exe';
        }

        echo module_exec_check($exe, 'okname');
    }

    // kcp일 때
    if($config['cf_cert_hp'] == 'kcp') {
        if(PHP_INT_MAX == 2147483647) // 32-bit
            $exe = G5_KCPCERT_PATH . '/bin/ct_cli';
        else
            $exe = G5_KCPCERT_PATH . '/bin/ct_cli_x64';

        echo module_exec_check($exe, 'ct_cli');
    }

    // LG의 경우 log 디렉토리 체크
    if($config['cf_cert_hp'] == 'lg') {
        $log_path = G5_LGXPAY_PATH.'/lgdacom/log';

        if(!is_dir($log_path)) {
            echo '<script>'.PHP_EOL;
            echo 'alert("'.str_replace(G5_PATH.'/', '', G5_LGXPAY_PATH).'/lgdacom 폴더 안에 log 폴더를 생성하신 후 쓰기권한을 부여해 주십시오.\n> mkdir log\n> chmod 707 log");'.PHP_EOL;
            echo '</script>'.PHP_EOL;
        } else {
            if(!is_writable($log_path)) {
                echo '<script>'.PHP_EOL;
                echo 'alert("'.str_replace(G5_PATH.'/', '',$log_path).' 폴더에 쓰기권한을 부여해 주십시오.\n> chmod 707 log");'.PHP_EOL;
                echo '</script>'.PHP_EOL;
            }
        }
    }
}

include_once ('./admin.tail.php');
?>
