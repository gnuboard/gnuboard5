#!/usr/bin/env php
<?php
if (PHP_SAPI !== 'cli') {
    exit("CLI에서만 실행할 수 있습니다.\n");
}

$root = dirname(dirname(__FILE__));
chdir($root);

define('G5_IS_CLI', true);
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'CLI';

require_once $root . '/common.php';
require_once G5_LIB_PATH . '/migration.lib.php';

$test_suffix = substr(md5(getmypid() . microtime()), 0, 10);
$test_config_table = G5_TABLE_PREFIX . 'migration_test_config_' . $test_suffix;
$test_auth_table = G5_TABLE_PREFIX . 'migration_test_auth_' . $test_suffix;
$test_memo_table = G5_TABLE_PREFIX . 'migration_test_memo_' . $test_suffix;
$test_member_table = G5_TABLE_PREFIX . 'migration_test_member_' . $test_suffix;
$test_board_file_table = G5_TABLE_PREFIX . 'migration_test_board_file_' . $test_suffix;
$test_social_profile_table = G5_TABLE_PREFIX . 'migration_test_social_' . $test_suffix;
$test_content_table = G5_TABLE_PREFIX . 'migration_test_content_' . $test_suffix;
$test_board_table = G5_TABLE_PREFIX . 'migration_test_board_' . $test_suffix;
$test_write_prefix = G5_TABLE_PREFIX . 'migration_test_write_' . $test_suffix . '_';
$test_write_table = $test_write_prefix . 'sample';
$test_cart_table = G5_TABLE_PREFIX . 'migration_test_cart_' . $test_suffix;
$test_item_table = G5_TABLE_PREFIX . 'migration_test_item_' . $test_suffix;
$test_shop_default_table = G5_TABLE_PREFIX . 'migration_test_default_' . $test_suffix;
$test_inicis_pay_table = G5_TABLE_PREFIX . 'migration_test_inicis_' . $test_suffix;
$test_inicis_pay_event_table = G5_TABLE_PREFIX . 'migration_test_inicis_event_' . $test_suffix;
$test_absent_prefix = G5_TABLE_PREFIX . 'migration_test_absent_' . $test_suffix . '_';
$test_tables = array($test_config_table, $test_auth_table, $test_memo_table, $test_member_table, $test_board_file_table, $test_social_profile_table, $test_content_table, $test_board_table, $test_write_table, $test_cart_table, $test_item_table, $test_shop_default_table, $test_inicis_pay_table, $test_inicis_pay_event_table);

function g5_migration_test_cleanup()
{
    global $test_tables;
    foreach ($test_tables as $table) {
        sql_query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`", false);
    }
}

function g5_migration_test_fail($message)
{
    fwrite(STDERR, '[실패] ' . $message . "\n");
    exit(1);
}

function g5_migration_test_execute($migration)
{
    if (isset($migration['error'])) {
        g5_migration_test_fail($migration['error']);
    }
    foreach ($migration['statements'] as $statement) {
        if (!g5_migration_should_run_statement($statement['conditions'])) {
            continue;
        }
        $result = g5_migration_execute_statement($statement);
        if ($result['error'] !== '') {
            g5_migration_test_fail($result['error']);
        }
    }
}

register_shutdown_function('g5_migration_test_cleanup');
g5_migration_test_cleanup();

if (!sql_query("CREATE TABLE `{$test_config_table}` (
        `cf_googl_shorturl_apikey` varchar(50) NOT NULL DEFAULT '',
        `cf_twitter_secret` varchar(100) NOT NULL DEFAULT '',
        `cf_kakao_js_apikey` varchar(100) NOT NULL DEFAULT '',
        `cf_member_icon_height` int(11) NOT NULL DEFAULT '0',
        `cf_google_clientid` varchar(100) NOT NULL DEFAULT '',
        `cf_member_img_width` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_auth_table}` (`au_menu` varchar(20) NOT NULL DEFAULT '') ENGINE=MyISAM DEFAULT CHARSET=utf8", false) ||
    !sql_query("CREATE TABLE `{$test_memo_table}` (`me_id` int(11) NOT NULL, PRIMARY KEY (`me_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8", false) ||
    !sql_query("CREATE TABLE `{$test_member_table}` (`mb_marketing_agree` tinyint(1) NOT NULL DEFAULT '0') ENGINE=MyISAM DEFAULT CHARSET=utf8", false) ||
    !sql_query("CREATE TABLE `{$test_board_file_table}` (`bf_fileurl` varchar(255) NOT NULL DEFAULT '') ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_content_table}` (
        `co_id` varchar(20) NOT NULL DEFAULT '',
        `co_subject` varchar(255) NOT NULL DEFAULT '',
        `co_seo_title` varchar(200) NOT NULL DEFAULT '',
        PRIMARY KEY (`co_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_board_table}` (`bo_table` varchar(20) NOT NULL DEFAULT '') ENGINE=MyISAM DEFAULT CHARSET=utf8", false) ||
    !sql_query("CREATE TABLE `{$test_write_table}` (`wr_id` int(11) NOT NULL AUTO_INCREMENT, `wr_content` text NOT NULL, `wr_seo_title` varchar(200) NOT NULL DEFAULT '', PRIMARY KEY (`wr_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8", false) ||
    !sql_query("INSERT INTO `{$test_board_table}` SET bo_table = 'sample'", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_cart_table}` (
        `ct_id` int(11) NOT NULL AUTO_INCREMENT, `it_id` varchar(20) NOT NULL DEFAULT '',
        `it_name` varchar(255) NOT NULL DEFAULT '',
        `it_sc_method` tinyint(4) NOT NULL DEFAULT '0', PRIMARY KEY (`ct_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_item_table}` (
        `it_id` varchar(20) NOT NULL DEFAULT '', `it_sc_type` tinyint(4) NOT NULL DEFAULT '0',
        `it_sc_method` tinyint(4) NOT NULL DEFAULT '0', `it_sc_price` int(11) NOT NULL DEFAULT '0',
        `it_sc_minimum` int(11) NOT NULL DEFAULT '0', `it_sc_qty` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`it_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_shop_default_table}` (`de_inicis_pro_reconcile_use` tinyint(4) NOT NULL DEFAULT '0') ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}
if (!sql_query("CREATE TABLE `{$test_inicis_pay_table}` (
        `ip_id` int(11) NOT NULL AUTO_INCREMENT, `ip_oid` varchar(64) NOT NULL DEFAULT '',
        `ip_environment` varchar(10) NOT NULL DEFAULT '', PRIMARY KEY (`ip_id`), UNIQUE KEY `ip_oid` (`ip_oid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false)) {
    g5_migration_test_fail(sql_error_info());
}

$g5['config_table'] = $test_config_table;
$g5['auth_table'] = $test_auth_table;
$g5['memo_table'] = $test_memo_table;
$g5['member_table'] = $test_member_table;
$g5['board_file_table'] = $test_board_file_table;
$g5['social_profile_table'] = $test_social_profile_table;
$g5['content_table'] = $test_content_table;
$g5['board_table'] = $test_board_table;
$g5['write_prefix'] = $test_write_prefix;
$g5['g5_shop_cart_table'] = $test_cart_table;
$g5['g5_shop_item_table'] = $test_item_table;
$g5['g5_shop_default_table'] = $test_shop_default_table;
$g5['g5_shop_inicis_pay_table'] = $test_inicis_pay_table;
$g5['g5_shop_inicis_pay_event_table'] = $test_inicis_pay_event_table;
$isolated_keys = array(
    'g5_shop_order_data_table', 'g5_shop_inicis_log_table'
);
foreach ($isolated_keys as $index => $key) {
    $g5[$key] = $test_absent_prefix . $index;
}

// 복합 ALTER의 첫 컬럼만 없는 역방향 부분 적용 상태에서도 과거 마이그레이션이 중단되지 않아야 한다.
$inspection_cache = array();
$social_migration = g5_migration_parse_file(G5_PATH . '/migrations/20180330_001_social_login.sql');
if (g5_migration_needs_no_execution($social_migration, $inspection_cache)) {
    g5_migration_test_fail('부분 적용된 소셜 스키마를 실행 불필요로 판단했습니다.');
}
g5_migration_test_execute(g5_migration_parse_file(G5_PATH . '/migrations/20140331_001_shop_cart_shipping.sql'));
g5_migration_test_execute(g5_migration_parse_file(G5_PATH . '/migrations/20180330_001_social_login.sql'));
g5_migration_test_execute(g5_migration_parse_file(G5_PATH . '/migrations/20260723_001_inicis_pro.sql'));
$inspection_cache = array();
if (!g5_migration_needs_no_execution($social_migration, $inspection_cache)) {
    g5_migration_test_fail('이미 적용된 소셜 스키마를 실행 불필요로 판단하지 못했습니다.');
}
$write_migration = g5_migration_parse_file(G5_PATH . '/migrations/20191202_002_board_write_seo.sql');
if (!g5_migration_needs_no_execution($write_migration, $inspection_cache)) {
    g5_migration_test_fail('기존 게시판 SEO 컬럼을 확인하지 못했습니다.');
}
sql_query("ALTER TABLE `{$test_write_table}` DROP COLUMN wr_seo_title", false);
$inspection_cache = array();
if (g5_migration_needs_no_execution($write_migration, $inspection_cache)) {
    g5_migration_test_fail('게시판 SEO 컬럼 누락을 실행 불필요로 판단했습니다.');
}
g5_migration_test_execute($write_migration);
$inspection_cache = array();
$unconditional_migration = g5_migration_parse_file(G5_PATH . '/migrations/20260904_002_reconcile_partial_schema.sql');
$backfill_migration = g5_migration_parse_file(G5_PATH . '/migrations/20260904_003_backfill_legacy_data.sql');
if (g5_migration_needs_no_execution($unconditional_migration, $inspection_cache) || g5_migration_needs_no_execution($backfill_migration, $inspection_cache)) {
    g5_migration_test_fail('조건 없는 스키마 변경 또는 데이터 보정을 완료로 추정했습니다.');
}
$absent_table = $test_absent_prefix . 'inspection';
if (g5_migration_inspect_object('column', $absent_table, 'missing', $inspection_cache) !== null) {
    g5_migration_test_fail('조회 오류를 객체 없음과 구분하지 못했습니다.');
}
if (!g5_migration_column_exists($test_cart_table, 'it_sc_type') ||
    !g5_migration_column_exists($test_config_table, 'cf_social_login_use') ||
    !g5_migration_column_exists($test_config_table, 'cf_member_img_size') ||
    !g5_migration_column_exists($test_shop_default_table, 'de_inicis_pro_alert_use')) {
    g5_migration_test_fail('역방향 부분 적용 스키마에서 과거 복합 변경의 기준 컬럼을 복구하지 못했습니다.');
}

$schema_migration = g5_migration_parse_file(G5_PATH . '/migrations/20260904_002_reconcile_partial_schema.sql');
g5_migration_test_execute($schema_migration);
if (!g5_migration_column_exists($test_config_table, 'cf_google_secret') ||
    !g5_migration_column_exists($test_config_table, 'cf_member_img_width') ||
    !g5_migration_column_exists($test_config_table, 'cf_member_img_height')) {
    g5_migration_test_fail('부분 적용 설정 테이블의 누락 컬럼을 복구하지 못했습니다.');
}
if (!g5_migration_column_exists($test_cart_table, 'it_sc_method') ||
    !g5_migration_column_exists($test_shop_default_table, 'de_inicis_pro_monitor_message') ||
    !g5_migration_column_exists($test_inicis_pay_table, 'ip_pg_status') ||
    !g5_migration_index_exists($test_inicis_pay_table, 'ip_refund_required')) {
    g5_migration_test_fail('부분 적용 쇼핑몰 테이블의 누락 컬럼 또는 인덱스를 복구하지 못했습니다.');
}
$write_column = sql_fetch("SHOW COLUMNS FROM `{$test_write_table}` LIKE 'wr_seo_title'", false);
if (!isset($write_column['Type']) || strtolower($write_column['Type']) !== 'varchar(255)') {
    g5_migration_test_fail('게시판 글 SEO 컬럼의 최종 자료형을 보정하지 못했습니다.');
}

sql_query("INSERT INTO `{$test_config_table}` SET cf_member_img_size = 0, cf_member_img_width = 0, cf_member_img_height = 0", false);
sql_query("INSERT INTO `{$test_content_table}` SET co_id = 'company', co_subject = '회사 소개', co_seo_title = ''", false);
sql_query("INSERT INTO `{$test_item_table}` SET it_id = 'item-1', it_sc_type = 2, it_sc_method = 1, it_sc_price = 3000, it_sc_minimum = 50000, it_sc_qty = 2", false);
sql_query("INSERT INTO `{$test_cart_table}` SET it_id = 'item-1'", false);

$data_migration = g5_migration_parse_file(G5_PATH . '/migrations/20260904_003_backfill_legacy_data.sql');
g5_migration_test_execute($data_migration);

$config_row = sql_fetch("SELECT cf_member_img_size, cf_member_img_width, cf_member_img_height FROM `{$test_config_table}` LIMIT 1", false);
if ((int) $config_row['cf_member_img_size'] !== 50000 || (int) $config_row['cf_member_img_width'] !== 60 || (int) $config_row['cf_member_img_height'] !== 60) {
    g5_migration_test_fail('회원 이미지 설정 기본값을 보정하지 못했습니다.');
}
$content_row = sql_fetch("SELECT co_seo_title FROM `{$test_content_table}` WHERE co_id = 'company'", false);
if (!isset($content_row['co_seo_title']) || $content_row['co_seo_title'] === '') {
    g5_migration_test_fail('콘텐츠 SEO 제목을 보정하지 못했습니다.');
}
$cart_row = sql_fetch("SELECT it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty FROM `{$test_cart_table}` WHERE it_id = 'item-1'", false);
if ((int) $cart_row['it_sc_type'] !== 2 || (int) $cart_row['it_sc_method'] !== 1 || (int) $cart_row['it_sc_price'] !== 3000 || (int) $cart_row['it_sc_minimum'] !== 50000 || (int) $cart_row['it_sc_qty'] !== 2) {
    g5_migration_test_fail('장바구니 배송비 데이터를 보정하지 못했습니다.');
}

$migrations = g5_migration_discover();
$migration_ids = array();
$dependency_records = array();
foreach ($migrations as $migration) {
    if (isset($migration['error'])) {
        g5_migration_test_fail($migration['error']);
    }
    $migration_ids[] = $migration['id'];
    $dependency_records[$migration['id']] = array('status' => 'success', 'checksum' => $migration['checksum']);
}
$sorted_ids = $migration_ids;
sort($sorted_ids, SORT_STRING);
if ($migration_ids !== $sorted_ids) {
    g5_migration_test_fail('전체 마이그레이션이 오래된 순서로 정렬되지 않았습니다.');
}
$last_migration = $migrations[count($migrations) - 1];
unset($dependency_records[$migrations[0]['id']]);
if (g5_migration_validate_target_dependencies($migrations, $dependency_records, $last_migration['id']) === '') {
    g5_migration_test_fail('누락된 선행 마이그레이션을 차단하지 못했습니다.');
}

echo "기존 상태 읽기 전용 검사·실행 순서·선행 조건·부분 적용 스키마·기존 데이터 보정 검사가 통과했습니다.\n";
