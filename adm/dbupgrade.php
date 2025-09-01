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