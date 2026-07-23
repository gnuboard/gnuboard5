<?php
$sub_menu = '100410';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = 'DB 업그레이드';
include_once('./admin.head.php');

$is_check = false;

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

    $is_check = true;
}

//소셜 로그인 관련 필드 카카오 클라이언트 시크릿 추가
if(!isset($config['cf_kakao_client_secret'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}`
                ADD `cf_kakao_client_secret` varchar(100) NOT NULL DEFAULT '' AFTER `cf_kakao_rest_key`
    ", true);

    $is_check = true;
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

    $is_check = true;
}

// 소셜 로그인 관리 테이블 없을 경우 생성
if( isset($g5['social_profile_table']) && !sql_query(" DESC {$g5['social_profile_table']} ", false)) {
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

    $is_check = true;
}

// 게시판 짧은 주소
$sql = " select bo_table from {$g5['board_table']} ";
$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
    $write_table = $g5['write_prefix'] . $row['bo_table']; // 게시판 테이블 전체이름

    $sql = " SHOW COLUMNS FROM {$write_table} LIKE 'wr_seo_title' ";
    $row = sql_fetch($sql);
    
    if( !$row ){
        sql_query("ALTER TABLE `{$write_table}`
                    ADD `wr_seo_title` varchar(200) NOT NULL DEFAULT '' AFTER `wr_content`,
                    ADD INDEX `wr_seo_title` (`wr_seo_title`);
        ", false);

        $is_check = true;
    }
}

// 내용 관리 짧은 주소
$sql = " SHOW COLUMNS FROM `{$g5['content_table']}` LIKE 'co_seo_title' ";
$row = sql_fetch($sql);

if( !$row ){
    sql_query("ALTER TABLE `{$g5['content_table']}`
                ADD `co_seo_title` varchar(200) NOT NULL DEFAULT '' AFTER `co_content`,
                ADD INDEX `co_seo_title` (`co_seo_title`);
    ", false);

    $is_check = true;
}

$sql = "select * from {$g5['content_table']} limit 100 ";
$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {

    if( ! $row['co_seo_title']){
        
        $co_seo_title = exist_seo_title_recursive('content', generate_seo_title($row['co_subject']), $g5['content_table'], $row['co_id']);
        
        $sql = " update {$g5['content_table']}
                    set co_seo_title = '$co_seo_title'
                  where co_id = '{$row['co_id']}' ";
        sql_query($sql);

    }
}

// 메모 테이블
$sql = " SHOW COLUMNS FROM `{$g5['memo_table']}` LIKE 'me_send_id' ";
$row = sql_fetch($sql);

if( !$row ){
    sql_query("ALTER TABLE `{$g5['memo_table']}`
                ADD `me_send_id` INT(11) NOT NULL DEFAULT '0',
                ADD `me_type` ENUM('send','recv') NOT NULL DEFAULT 'recv',
                ADD `me_send_ip` VARCHAR(100) NOT NULL DEFAULT '',
                CHANGE COLUMN `me_id` `me_id` INT(11) NOT NULL AUTO_INCREMENT;
    ", false);

    $is_check = true;
}

// 읽지 않은 메모 수 칼럼
if(!isset($member['mb_memo_cnt'])) {
    sql_query(" ALTER TABLE `{$g5['member_table']}`
                ADD `mb_memo_cnt` int(11) NOT NULL DEFAULT '0' AFTER `mb_memo_call`", true);

    $is_check = true;
}

// 스크랩 읽은 수 추가
if(!isset($member['mb_scrap_cnt'])) {
    sql_query(" ALTER TABLE `{$g5['member_table']}`
                ADD `mb_scrap_cnt` int(11) NOT NULL DEFAULT '0' AFTER `mb_memo_cnt`", true);

	$is_check = true;
}

// 짧은 URL 주소를 사용 여부 필드 추가
if (!isset($config['cf_bbs_rewrite'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_bbs_rewrite` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_link_target` ", true);

	$is_check = true;
}

// 파일테이블에 추가 칼럼

$sql = " SHOW COLUMNS FROM `{$g5['board_file_table']}` LIKE 'bf_fileurl' ";
$row = sql_fetch($sql);

if( !$row ) {
    sql_query(" ALTER TABLE `{$g5['board_file_table']}` 
                ADD COLUMN `bf_fileurl` VARCHAR(255) NOT NULL DEFAULT '' AFTER `bf_content`,
                ADD COLUMN `bf_thumburl` VARCHAR(255) NOT NULL DEFAULT '' AFTER `bf_fileurl`,
                ADD COLUMN `bf_storage` VARCHAR(50) NOT NULL DEFAULT '' AFTER `bf_thumburl`", true);

    $is_check = true;
}

if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    // 임시저장 테이블이 없을 경우 생성
    if(!sql_query(" DESC {$g5['g5_shop_post_log_table']} ", false)) {
        sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_post_log_table']}` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `oid` bigint(20) unsigned NOT NULL,
                    `mb_id` varchar(255) NOT NULL DEFAULT '',
                    `post_data` text NOT NULL,
                    `ol_code` varchar(255) NOT NULL DEFAULT '',
                    `ol_msg` text NOT NULL,
                    `ol_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `ol_ip` varchar(25) NOT NULL DEFAULT '',
                    PRIMARY KEY (`log_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ", true);

        $is_check = true;
    }

    $result = sql_query("describe `{$g5['g5_shop_post_log_table']}`");
    while ($row = sql_fetch_array($result)){
        if( isset($row['Field']) && $row['Field'] === 'ol_msg' && $row['Type'] === 'varchar(255)' ){
            sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` MODIFY ol_msg TEXT NOT NULL;", false);
            sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` DROP PRIMARY KEY;", false);
            sql_query("ALTER TABLE `{$g5['g5_shop_post_log_table']}` ADD `log_id` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`log_id`);", false);
            $is_check = true;
            break;
        }
    }

    if (!isset($default['de_id'])) {
        sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                        ADD COLUMN `de_id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
                        ADD PRIMARY KEY (`de_id`); ", true);

        $is_check = true;
    }
}

// auth.au_menu 컬럼 크기 조정
$sql = " SHOW COLUMNS FROM `{$g5['auth_table']}` LIKE 'au_menu' ";
$row = sql_fetch($sql);
if (
    stripos($row['Type'], 'varchar') !== false
    && (int) preg_replace('/[^0-9]/', '', $row['Type']) < 50
) {
    sql_query(" ALTER TABLE `{$g5['auth_table']}` CHANGE `au_menu` `au_menu` VARCHAR(50) NOT NULL; ", true);

    $is_check = true;
}

// qa config 테이블 auto id key 추가
$row = sql_fetch("select * from `{$g5['qa_config_table']}` limit 1");
if (!isset($row['qa_id'])) {
    sql_query(" ALTER TABLE `{$g5['qa_config_table']}` ADD COLUMN `qa_id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
                ADD PRIMARY KEY (`qa_id`); ", true);

    $is_check = true;
}

// config 기본 테이블 auto id key 추가
if (!isset($config['cf_id'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD COLUMN `cf_id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
                    ADD PRIMARY KEY (`cf_id`); ", true);

	$is_check = true;
}

// login 테이블 auto id key 추가
$row = sql_fetch("select * from `{$g5['login_table']}` limit 1");
if (!isset($row['lo_id'])) {
    sql_query(" ALTER TABLE `{$g5['login_table']}`
                    ADD COLUMN `lo_id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`lo_id`),
                    ADD UNIQUE KEY `lo_ip_unique` (`lo_ip`) ", true);

	$is_check = true;
}

// visit 테이블 auto id key 로 변경
$result = sql_query("describe `{$g5['visit_table']}`");
while ($row = sql_fetch_array($result)){
    if (isset($row['Field']) && $row['Field'] === 'vi_id' && (isset($row['Default']) && $row['Default'] == 0)){
        sql_query("ALTER TABLE `{$g5['visit_table']}`
                    CHANGE COLUMN `vi_id` `vi_id` INT(11) NOT NULL AUTO_INCREMENT;
        ", false);

        $is_check = true;
    }
}

// SMS5 테이블 G5_TABLE_PREFIX 적용
if($g5['sms5_prefix'] != 'sms5_' && sql_num_rows(sql_query("show tables like 'sms5_config'")))
{
    $tables = array('config','write','history','book','book_group','form','form_group');

    foreach($tables as $name){
        $old_table = 'sms5_' . $name;
        $new_table = $g5['sms5_prefix'] . $name;

        // 기존 테이블이 있고, G5_TABLE_PREFIX 적용 테이블이 없을 경우 → 테이블명 변경
        if(sql_num_rows(sql_query("SHOW TABLES LIKE '{$old_table}' "))){
            if(!sql_num_rows(sql_query("SHOW TABLES LIKE '{$new_table}' "))){
                sql_query("RENAME TABLE {$old_table} TO {$new_table}", false);
            }
        }
    }

    $is_check = true;
}

// 광고성 정보 수신 동의 사용 필드 추가
if (!isset($config['cf_use_promotion'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
            ADD `cf_use_promotion` tinyint(1) NOT NULL DEFAULT '0' AFTER `cf_privacy` ",
        true
    );

    $is_check = true;
}

// 광고성 정보 수신 동의 여부 필드 추가 + 메일 / SMS 수신 일자 추가
if (!isset($member['mb_marketing_agree'])) {
    sql_query(
        " ALTER TABLE `{$g5['member_table']}`
                ADD `mb_marketing_agree` tinyint(1) NOT NULL DEFAULT '0' AFTER  `mb_scrap_cnt`,
                ADD `mb_marketing_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `mb_marketing_agree`,
                ADD `mb_thirdparty_agree` tinyint(1) NOT NULL DEFAULT '0' AFTER  `mb_marketing_date`,
                ADD `mb_thirdparty_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `mb_thirdparty_agree`,
                ADD `mb_agree_log` TEXT NOT NULL AFTER `mb_thirdparty_date`,
                ADD `mb_mailling_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `mb_mailling`,
                ADD `mb_sms_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `mb_sms` ",
        true
    );

    $is_check = true;
}

// 쿠폰 로그 테이블에 UNIQUE 인덱스 추가 (쿠폰 이중사용 방지)
if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    $result = sql_query("SHOW INDEX FROM `{$g5['g5_shop_coupon_log_table']}` WHERE Key_name = 'idx_coupon_use'", false);
    if (!$result || !sql_num_rows($result)) {
        // 기존에 동일 쿠폰이 중복 사용된 데이터가 있으면 UNIQUE 인덱스 생성 실패하므로 중복 데이터 정리
        $dup_sql = " SELECT cp_id, mb_id, MIN(cl_id) as keep_id
                       FROM `{$g5['g5_shop_coupon_log_table']}`
                      GROUP BY cp_id, mb_id
                     HAVING COUNT(*) > 1 ";

        $dup_result = sql_query($dup_sql, false);
        if ($dup_result && sql_num_rows($dup_result)) {
            while ($dup_row = sql_fetch_array($dup_result)) {
                
                echo $dup_row['cp_id']." 의 동일 쿠폰이 중복 사용된 데이터가 있으므로 인덱스 생성이 불가합니다. <br>";
                
                $sql = " DELETE FROM `{$g5['g5_shop_coupon_log_table']}`
                             WHERE cp_id = '{$dup_row['cp_id']}'
                               AND mb_id = '{$dup_row['mb_id']}'
                               AND cl_id != '{$dup_row['keep_id']}' ";
                if ($is_admin === 'super') {
                    echo "데이터베이스에서 검토후에 이 쿼리문을 실행해 주세요.<br>$sql<br>";
                }
                // sql_query($sql);
            }
        }

        // MyISAM + utf8mb4 환경에서 키 길이 초과 방지: cp_id varchar(100), mb_id varchar(100)으로 조정
        sql_query("ALTER TABLE `{$g5['g5_shop_coupon_log_table']}` MODIFY `cp_id` varchar(100) NOT NULL DEFAULT '', MODIFY `mb_id` varchar(100) NOT NULL DEFAULT ''", false);
        sql_query("ALTER TABLE `{$g5['g5_shop_coupon_log_table']}` ADD UNIQUE KEY `idx_coupon_use` (`cp_id`, `mb_id`)", false);
        $is_check = true;
    }
}

// 자동 로그인 토큰 테이블 생성 (KVE-2026-0610: 추측 가능한 자동 로그인 쿠키 위조 방지)
// 다중 디바이스 지원을 위해 회원당 여러 토큰을 별도 테이블로 관리
if (!isset($g5['member_auto_login_table'])) {
    $g5['member_auto_login_table'] = G5_TABLE_PREFIX.'member_auto_login';
}
if (!sql_query(" DESC `{$g5['member_auto_login_table']}` ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['member_auto_login_table']}` (
                  `al_id` int(11) NOT NULL auto_increment,
                  `mb_id` varchar(20) NOT NULL default '',
                  `al_token` varchar(64) NOT NULL default '',
                  `al_user_agent` varchar(255) NOT NULL default '',
                  `al_ip` varchar(45) NOT NULL default '',
                  `al_created` datetime DEFAULT NULL,
                  `al_last_used` datetime DEFAULT NULL,
                  `al_expire` datetime DEFAULT NULL,
                  PRIMARY KEY  (`al_id`),
                  UNIQUE KEY `al_token` (`al_token`),
                  KEY `mb_id` (`mb_id`),
                  KEY `al_expire` (`al_expire`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    $is_check = true;
}

// KG이니시스 결제 처리 현황 및 단계별 이력 테이블
if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    $inicis_pro_config_columns = array(
        'de_inicis_pro_alert_use' => "ADD COLUMN `de_inicis_pro_alert_use` tinyint(4) NOT NULL DEFAULT '1'",
        'de_inicis_pro_reconcile_use' => "ADD COLUMN `de_inicis_pro_reconcile_use` tinyint(4) NOT NULL DEFAULT '0'",
        'de_inicis_pro_log_days' => "ADD COLUMN `de_inicis_pro_log_days` int(11) NOT NULL DEFAULT '365'",
        'de_inicis_pro_summary_days' => "ADD COLUMN `de_inicis_pro_summary_days` int(11) NOT NULL DEFAULT '1825'",
        'de_inicis_pro_monitor_at' => "ADD COLUMN `de_inicis_pro_monitor_at` datetime DEFAULT NULL",
        'de_inicis_pro_monitor_message' => "ADD COLUMN `de_inicis_pro_monitor_message` varchar(255) NOT NULL DEFAULT ''"
    );
    $inicis_pro_config_alter = array();
    foreach ($inicis_pro_config_columns as $column => $alter) {
        $column_result = sql_query(" SHOW COLUMNS FROM `{$g5['g5_shop_default_table']}` LIKE '$column' ", false);
        if (!$column_result || sql_num_rows($column_result) === 0)
            $inicis_pro_config_alter[] = $alter;
    }
    if (count($inicis_pro_config_alter)) {
        sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}` ".implode(', ', $inicis_pro_config_alter), true);
        $is_check = true;
    }

    if (!isset($g5['g5_shop_inicis_pay_table']))
        $g5['g5_shop_inicis_pay_table'] = G5_SHOP_TABLE_PREFIX.'inicis_pay';
    if (!isset($g5['g5_shop_inicis_pay_event_table']))
        $g5['g5_shop_inicis_pay_event_table'] = G5_SHOP_TABLE_PREFIX.'inicis_pay_event';

    if (!sql_query(" DESC `{$g5['g5_shop_inicis_pay_table']}` ", false)) {
        sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_inicis_pay_table']}` (
                      `ip_id` int(11) NOT NULL AUTO_INCREMENT,
                      `ip_oid` varchar(64) NOT NULL DEFAULT '',
                      `ip_tid` varchar(80) NOT NULL DEFAULT '',
                      `ip_auth_tid` varchar(80) NOT NULL DEFAULT '',
                      `ip_mid` varchar(80) NOT NULL DEFAULT '',
                      `ip_environment` varchar(10) NOT NULL DEFAULT '',
                      `mb_id` varchar(20) NOT NULL DEFAULT '',
                      `ip_amount` int(11) NOT NULL DEFAULT '0',
                      `ip_pay_type` varchar(20) NOT NULL DEFAULT '',
                      `ip_easy_pay` varchar(20) NOT NULL DEFAULT '',
                      `ip_device` varchar(10) NOT NULL DEFAULT '',
                      `ip_order_type` varchar(10) NOT NULL DEFAULT '',
                      `ip_status` varchar(30) NOT NULL DEFAULT '',
                      `ip_result_code` varchar(30) NOT NULL DEFAULT '',
                      `ip_result_message` varchar(255) NOT NULL DEFAULT '',
                      `ip_noti_status` varchar(30) NOT NULL DEFAULT '',
                      `ip_noti_code` varchar(30) NOT NULL DEFAULT '',
                      `ip_noti_message` varchar(255) NOT NULL DEFAULT '',
                      `ip_noti_failed_count` int(11) NOT NULL DEFAULT '0',
                      `ip_noti_at` datetime DEFAULT NULL,
                      `ip_cancel_status` varchar(30) NOT NULL DEFAULT '',
                      `ip_cancel_code` varchar(30) NOT NULL DEFAULT '',
                      `ip_cancel_message` varchar(255) NOT NULL DEFAULT '',
                      `ip_cancel_checked_at` datetime DEFAULT NULL,
                      `ip_refund_required` tinyint(4) NOT NULL DEFAULT '0',
                      `ip_vbank_due_at` datetime DEFAULT NULL,
                      `ip_expired_at` datetime DEFAULT NULL,
                      `ip_order_exists` tinyint(4) NOT NULL DEFAULT '0',
                      `ip_approved_at` datetime DEFAULT NULL,
                      `ip_ordered_at` datetime DEFAULT NULL,
                      `ip_notified_at` datetime DEFAULT NULL,
                      `ip_canceled_at` datetime DEFAULT NULL,
                      `ip_created_at` datetime DEFAULT NULL,
                      `ip_updated_at` datetime DEFAULT NULL,
                      `ip_ip` varchar(45) NOT NULL DEFAULT '',
                      `ip_event_count` int(11) NOT NULL DEFAULT '0',
                      `ip_audit_error` tinyint(4) NOT NULL DEFAULT '0',
                      `ip_alerted_at` datetime DEFAULT NULL,
                      `ip_alert_key` varchar(64) NOT NULL DEFAULT '',
                      `ip_pg_status` varchar(30) NOT NULL DEFAULT '',
                      `ip_pg_amount` int(11) NOT NULL DEFAULT '0',
                      `ip_pg_tid` varchar(80) NOT NULL DEFAULT '',
                      `ip_pg_result_code` varchar(30) NOT NULL DEFAULT '',
                      `ip_pg_message` varchar(255) NOT NULL DEFAULT '',
                      `ip_pg_checked_at` datetime DEFAULT NULL,
                      PRIMARY KEY (`ip_id`),
                      UNIQUE KEY `ip_oid` (`ip_oid`),
                      KEY `ip_tid` (`ip_tid`),
                      KEY `ip_auth_tid` (`ip_auth_tid`),
                      KEY `ip_status` (`ip_status`),
                      KEY `ip_noti_status` (`ip_noti_status`),
                      KEY `ip_cancel_status` (`ip_cancel_status`),
                      KEY `ip_refund_required` (`ip_refund_required`),
                      KEY `ip_updated_at` (`ip_updated_at`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
        $is_check = true;
    }

    if (sql_query(" DESC `{$g5['g5_shop_inicis_pay_table']}` ", false)) {
        $inicis_pay_columns = array(
            'ip_environment' => "ADD COLUMN `ip_environment` varchar(10) NOT NULL DEFAULT '' AFTER `ip_mid`",
            'ip_easy_pay' => "ADD COLUMN `ip_easy_pay` varchar(20) NOT NULL DEFAULT '' AFTER `ip_pay_type`",
            'ip_noti_status' => "ADD COLUMN `ip_noti_status` varchar(30) NOT NULL DEFAULT '' AFTER `ip_result_message`",
            'ip_noti_code' => "ADD COLUMN `ip_noti_code` varchar(30) NOT NULL DEFAULT '' AFTER `ip_noti_status`",
            'ip_noti_message' => "ADD COLUMN `ip_noti_message` varchar(255) NOT NULL DEFAULT '' AFTER `ip_noti_code`",
            'ip_noti_failed_count' => "ADD COLUMN `ip_noti_failed_count` int(11) NOT NULL DEFAULT '0' AFTER `ip_noti_message`",
            'ip_noti_at' => "ADD COLUMN `ip_noti_at` datetime DEFAULT NULL AFTER `ip_noti_failed_count`",
            'ip_cancel_status' => "ADD COLUMN `ip_cancel_status` varchar(30) NOT NULL DEFAULT '' AFTER `ip_noti_at`",
            'ip_cancel_code' => "ADD COLUMN `ip_cancel_code` varchar(30) NOT NULL DEFAULT '' AFTER `ip_cancel_status`",
            'ip_cancel_message' => "ADD COLUMN `ip_cancel_message` varchar(255) NOT NULL DEFAULT '' AFTER `ip_cancel_code`",
            'ip_cancel_checked_at' => "ADD COLUMN `ip_cancel_checked_at` datetime DEFAULT NULL AFTER `ip_cancel_message`",
            'ip_refund_required' => "ADD COLUMN `ip_refund_required` tinyint(4) NOT NULL DEFAULT '0' AFTER `ip_cancel_checked_at`",
            'ip_vbank_due_at' => "ADD COLUMN `ip_vbank_due_at` datetime DEFAULT NULL AFTER `ip_refund_required`",
            'ip_expired_at' => "ADD COLUMN `ip_expired_at` datetime DEFAULT NULL AFTER `ip_vbank_due_at`",
            'ip_audit_error' => "ADD COLUMN `ip_audit_error` tinyint(4) NOT NULL DEFAULT '0'",
            'ip_alerted_at' => "ADD COLUMN `ip_alerted_at` datetime DEFAULT NULL",
            'ip_alert_key' => "ADD COLUMN `ip_alert_key` varchar(64) NOT NULL DEFAULT ''",
            'ip_pg_status' => "ADD COLUMN `ip_pg_status` varchar(30) NOT NULL DEFAULT ''",
            'ip_pg_amount' => "ADD COLUMN `ip_pg_amount` int(11) NOT NULL DEFAULT '0'",
            'ip_pg_tid' => "ADD COLUMN `ip_pg_tid` varchar(80) NOT NULL DEFAULT ''",
            'ip_pg_result_code' => "ADD COLUMN `ip_pg_result_code` varchar(30) NOT NULL DEFAULT ''",
            'ip_pg_message' => "ADD COLUMN `ip_pg_message` varchar(255) NOT NULL DEFAULT ''",
            'ip_pg_checked_at' => "ADD COLUMN `ip_pg_checked_at` datetime DEFAULT NULL"
        );
        $inicis_pay_alter = array();
        foreach ($inicis_pay_columns as $column => $alter) {
            $column_result = sql_query(" SHOW COLUMNS FROM `{$g5['g5_shop_inicis_pay_table']}` LIKE '$column' ", false);
            if (!$column_result || sql_num_rows($column_result) === 0)
                $inicis_pay_alter[] = $alter;
        }
        if (count($inicis_pay_alter)) {
            sql_query(" ALTER TABLE `{$g5['g5_shop_inicis_pay_table']}` ".implode(', ', $inicis_pay_alter), true);
            $is_check = true;
        }

        if (count($inicis_pay_alter)) {
            $environment = !empty($default['de_card_test']) ? 'test' : 'live';
            sql_query(" insert ignore into `{$g5['g5_shop_inicis_pay_table']}`
                            (ip_oid, ip_environment, ip_status, ip_noti_status, ip_noti_code, ip_noti_message,
                             ip_noti_failed_count, ip_noti_at, ip_created_at, ip_updated_at, ip_event_count)
                        select e.ip_oid, '$environment', e.pe_status, e.pe_status, e.pe_code, e.pe_message,
                               sum(if(e.pe_status = 'notification_failed', 1, 0)), max(e.pe_created_at),
                               min(e.pe_created_at), max(e.pe_created_at), count(*)
                          from `{$g5['g5_shop_inicis_pay_event_table']}` e
                          left join `{$g5['g5_shop_inicis_pay_table']}` p on p.ip_oid = e.ip_oid
                         where p.ip_id is null
                           and e.pe_stage = 'notification'
                         group by e.ip_oid ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}`
                           set ip_environment = case
                                   when lower(ip_mid) in ('inipaytest','iniescrow0') then 'test'
                                   when ip_mid <> '' then 'live'
                                   else '$environment'
                               end
                         where ip_environment = '' ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}` p
                           inner join (
                               select ip_oid, max(pe_id) as pe_id,
                                      sum(if(pe_status = 'notification_failed', 1, 0)) as fail_count
                                 from `{$g5['g5_shop_inicis_pay_event_table']}`
                                where pe_stage = 'notification'
                                group by ip_oid
                           ) x on x.ip_oid = p.ip_oid
                           inner join `{$g5['g5_shop_inicis_pay_event_table']}` e on e.pe_id = x.pe_id
                           set p.ip_noti_status = e.pe_status,
                               p.ip_noti_code = e.pe_code,
                               p.ip_noti_message = e.pe_message,
                               p.ip_noti_failed_count = x.fail_count,
                               p.ip_noti_at = e.pe_created_at ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}` p
                           inner join (
                               select ip_oid, max(pe_id) as pe_id
                                 from `{$g5['g5_shop_inicis_pay_event_table']}`
                                where pe_stage = 'cancel'
                                group by ip_oid
                           ) x on x.ip_oid = p.ip_oid
                           inner join `{$g5['g5_shop_inicis_pay_event_table']}` e on e.pe_id = x.pe_id
                           set p.ip_cancel_status = e.pe_status,
                               p.ip_cancel_code = e.pe_code,
                               p.ip_cancel_message = e.pe_message,
                               p.ip_cancel_checked_at = e.pe_created_at ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}` p
                           inner join `{$g5['g5_shop_inicis_pay_event_table']}` e on e.ip_oid = p.ip_oid and e.pe_stage = 'request'
                           set p.ip_easy_pay = case
                               when e.pe_message like '삼성페이%' then 'SAMSUNGPAY'
                               when e.pe_message like 'lpay%' then 'LPAY'
                               when e.pe_message like 'inicis_kakaopay%' then 'KAKAOPAY'
                               when e.pe_message like '간편결제%' then 'EASYPAY'
                               else p.ip_easy_pay
                           end
                         where p.ip_easy_pay = '' ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}`
                           set ip_noti_status = case
                                   when ip_status = 'paid' then 'paid'
                                   when ip_status = 'vbank_issued' then 'vbank_issued'
                                   when ip_status = 'notification_received' then 'notification_received'
                                   else ip_noti_status
                               end,
                               ip_cancel_status = case
                                   when ip_status in ('canceled','cancel_failed','partial_canceled','partial_cancel_failed') then ip_status
                                   else ip_cancel_status
                               end ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}` p
                           inner join {$g5['g5_shop_order_table']} o on o.od_id = p.ip_oid
                           set p.ip_status = 'paid_after_cancel',
                               p.ip_refund_required = '1',
                               p.ip_noti_status = 'paid_after_cancel',
                               p.ip_noti_message = '취소 주문에 가상계좌 입금 확인'
                         where p.ip_pay_type = 'VBANK'
                           and p.ip_status = 'paid'
                           and o.od_status = '취소'
                           and o.od_receipt_price > 0 ", false);
            $inicis_pro_migrated_at = G5_TIME_YMDHIS;
            $inicis_pro_migrated_message = '기존 결제·주문 이력 대조에서 취소 후 입금 확인';
            sql_query(" insert into `{$g5['g5_shop_inicis_pay_event_table']}`
                            (ip_oid, ip_tid, pe_stage, pe_status, pe_code, pe_message, pe_source, pe_ip, pe_created_at)
                        select p.ip_oid, p.ip_tid, 'reconcile', 'paid_after_cancel', '',
                               '".sql_escape_string($inicis_pro_migrated_message)."', 'system', '', '$inicis_pro_migrated_at'
                          from `{$g5['g5_shop_inicis_pay_table']}` p
                          left join `{$g5['g5_shop_inicis_pay_event_table']}` e
                            on e.ip_oid = p.ip_oid and e.pe_status = 'paid_after_cancel'
                         where p.ip_status = 'paid_after_cancel'
                           and p.ip_refund_required = '1'
                           and e.pe_id is null ", false);
            sql_query(" update `{$g5['g5_shop_inicis_pay_table']}` p
                           inner join `{$g5['g5_shop_inicis_pay_event_table']}` e
                             on e.ip_oid = p.ip_oid
                            and e.pe_status = 'paid_after_cancel'
                            and e.pe_message = '".sql_escape_string($inicis_pro_migrated_message)."'
                            and e.pe_created_at = '$inicis_pro_migrated_at'
                           set p.ip_event_count = p.ip_event_count + 1 ", false);
        }

        $inicis_pay_indexes = array(
            'ip_noti_status' => "ADD KEY `ip_noti_status` (`ip_noti_status`)",
            'ip_cancel_status' => "ADD KEY `ip_cancel_status` (`ip_cancel_status`)",
            'ip_refund_required' => "ADD KEY `ip_refund_required` (`ip_refund_required`)"
        );
        foreach ($inicis_pay_indexes as $index_name => $index_sql) {
            $index_result = sql_query(" SHOW INDEX FROM `{$g5['g5_shop_inicis_pay_table']}` WHERE Key_name = '$index_name' ", false);
            if (!$index_result || sql_num_rows($index_result) === 0) {
                sql_query(" ALTER TABLE `{$g5['g5_shop_inicis_pay_table']}` $index_sql ", true);
                $is_check = true;
            }
        }
    }

    if (!sql_query(" DESC `{$g5['g5_shop_inicis_pay_event_table']}` ", false)) {
        sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_inicis_pay_event_table']}` (
                      `pe_id` int(11) NOT NULL AUTO_INCREMENT,
                      `ip_oid` varchar(64) NOT NULL DEFAULT '',
                      `ip_tid` varchar(80) NOT NULL DEFAULT '',
                      `pe_stage` varchar(30) NOT NULL DEFAULT '',
                      `pe_status` varchar(30) NOT NULL DEFAULT '',
                      `pe_code` varchar(30) NOT NULL DEFAULT '',
                      `pe_message` varchar(255) NOT NULL DEFAULT '',
                      `pe_source` varchar(10) NOT NULL DEFAULT '',
                      `pe_ip` varchar(45) NOT NULL DEFAULT '',
                      `pe_created_at` datetime DEFAULT NULL,
                      PRIMARY KEY (`pe_id`),
                      KEY `ip_oid` (`ip_oid`),
                      KEY `ip_tid` (`ip_tid`),
                      KEY `pe_status` (`pe_status`),
                      KEY `pe_created_at` (`pe_created_at`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
        $is_check = true;
    }
}

$is_check = run_replace('admin_dbupgrade', $is_check);

$db_upgrade_msg = $is_check ? 'DB 업그레이드가 완료되었습니다.' : '더 이상 업그레이드 할 내용이 없습니다.<br>현재 DB 업그레이드가 완료된 상태입니다.';
?>

<div class="local_desc01 local_desc">
    <p>
        <?php echo $db_upgrade_msg; ?>
    </p>
</div>

<?php
include_once ('./admin.tail.php');
