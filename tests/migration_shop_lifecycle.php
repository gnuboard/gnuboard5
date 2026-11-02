<?php
// 격리 DB 전용. G5_TEST_DB_PORT, G5_TEST_DB_PASSWORD를 지정한다.
if (PHP_SAPI !== 'cli' || !getenv('G5_TEST_DB_PORT')) exit("격리 DB 포트가 필요합니다.\n");
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('127.0.0.1', 'root', getenv('G5_TEST_DB_PASSWORD'), '', (int) getenv('G5_TEST_DB_PORT'));
if ($db->connect_errno) throw new RuntimeException('격리 DB 연결 실패');
$db->set_charset('utf8mb4');
$db->query("SET sql_mode = ''");
function check_lifecycle($ok, $message) { if (!$ok) throw new RuntimeException($message); }
if (count($argv) === 1) {
    $database = 'g5_shop_lifecycle_' . getmypid();
    $dir = sys_get_temp_dir() . '/g5-shop-lifecycle-' . uniqid();
    mkdir($dir . '/migrations', 0700, true);
    check_lifecycle($db->query('CREATE DATABASE `' . $database . '`'), '임시 DB 생성');
    $db->select_db($database);
    try {
        $db->query('CREATE TABLE custom_config (counter int NOT NULL)');
        $db->query('INSERT INTO custom_config VALUES (0)');
        $db->query('CREATE TABLE mall_order (od_id bigint PRIMARY KEY)');
        $db->query('INSERT INTO mall_order VALUES (123)');
        $db->query('CREATE TABLE mall_cart (id int)');
        foreach (array('20150908_001_inicis_auth_number', '20160422_001_shop_test_order') as $id) {
            copy(dirname(__DIR__) . '/migrations/' . $id . '.sql', $dir . '/migrations/' . $id . '.sql');
        }
        file_put_contents($dir . '/migrations/20261102_001_mixed.sql', "-- @if-table-exists {{g5_shop_cart_table}}\n-- @if-column-missing {{g5_shop_cart_table}} marker\nALTER TABLE `{{g5_shop_cart_table}}` ADD `marker` int AFTER `it_name`;\nUPDATE `{{config_table}}` SET counter=counter+1;\n");
        foreach (array('off', 'off_again', 'broken_order', 'broken_cart', 'repaired', 'enabled_again', 'disabled_again', 'legacy_success', 'optional_created', 'checksum') as $phase) {
            if ($phase === 'broken_cart') $db->query("ALTER TABLE mall_order ADD od_settle_case varchar(255) DEFAULT ''");
            if ($phase === 'repaired') $db->query("ALTER TABLE mall_cart ADD it_name varchar(255) DEFAULT ''");
            if ($phase === 'legacy_success') {
                $db->query('ALTER TABLE mall_order DROP od_test');
                $db->query("UPDATE custom_migrations SET status='success' WHERE migration_id='20160422_001_shop_test_order'");
            }
            if ($phase === 'optional_created') $db->query('CREATE TABLE mall_inicis_log (P_FN_NM varchar(255))');
            if ($phase === 'checksum') $db->query("UPDATE custom_migrations SET status='shop_pending', checksum=REPEAT('0',64) WHERE migration_id='20160422_001_shop_test_order'");
            $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . escapeshellarg($phase) . ' ' . escapeshellarg($database) . ' ' . escapeshellarg($dir);
            passthru($cmd, $exit_code);
            check_lifecycle($exit_code === 0, $phase . ' 실패');
            $counter = $db->query('SELECT counter FROM custom_config')->fetch_row();
            check_lifecycle((int) $counter[0] === 1, $phase . ': 공통 UPDATE는 정확히 한 번');
        }
        echo "쇼핑몰 비활성→활성·실패·복구·재실행·과거 성공·선택 테이블·체크섬 검사 통과\n";
    } finally {
        $db->query('DROP DATABASE `' . $database . '`');
        foreach (glob($dir . '/migrations/*.sql') as $file) unlink($file);
        rmdir($dir . '/migrations'); rmdir($dir);
    }
    exit;
}
$phase = $argv[1];
$db->select_db($argv[2]);
define('_GNUBOARD_', true);
define('G5_PATH', $argv[3]);
define('G5_TABLE_PREFIX', 'custom_');
define('G5_SHOP_TABLE_PREFIX', 'mall_');
define('G5_USE_SHOP', !in_array($phase, array('off', 'off_again', 'disabled_again', 'checksum'), true));
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
$g5 = array('config_table' => 'custom_config');
function sql_query($sql, $error = false) { global $db; return $db->query($sql); }
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_num_rows($result) { return $result->num_rows; }
function sql_fetch($sql, $error = false) { $r = sql_query($sql); return $r ? $r->fetch_assoc() : false; }
function sql_real_escape_string($value) { global $db; return $db->real_escape_string($value); }
function sql_error_info() { global $db; return $db->errno . ' ' . $db->error; }
function get_microtime() { return microtime(true); }
require dirname(__DIR__) . '/lib/migration.lib.php';
if ($phase === 'off') {
    $existing = g5_migration_run('', true);
    check_lifecycle($existing['success'] && $existing['skipped'] === 2, '기존 구조 확인에서 보류 기록');
}
$before = g5_migration_status();
if (in_array($phase, array('off_again', 'disabled_again'), true)) {
    $migrations = g5_migration_discover(); $records = g5_migration_get_records($error);
    check_lifecycle(g5_migration_validate_target_dependencies($migrations, $records, '20261102_001_mixed') === '', '미사용 보류는 후속 작업을 막지 않음');
}
$result = g5_migration_run();
if (in_array($phase, array('broken_order', 'broken_cart', 'checksum'), true)) {
    check_lifecycle(!$result['success'], '불완전 구조 또는 체크섬 차단');
    $expected = $phase === 'broken_order' ? 'od_settle_case' : ($phase === 'broken_cart' ? 'it_name' : '파일이 적용 후 변경');
    check_lifecycle(strpos(implode(' ', $result['errors']), $expected) !== false, '구체적 오류 안내');
} else {
    check_lifecycle($result['success'], json_encode($result, JSON_UNESCAPED_UNICODE));
}
if (in_array($phase, array('off_again', 'enabled_again', 'disabled_again'), true)) check_lifecycle(!$result['applied'] && !$result['skipped'], '재실행 쓰기 없음');
$records = g5_migration_get_records($error);
if ($phase === 'off') check_lifecycle($records['20261102_001_mixed']['status'] === 'shop_pending', '공통 완료/쇼핑몰 보류 분리');
if ($phase === 'broken_cart') check_lifecycle($records['20261102_001_mixed']['status'] === 'shop_failed', '공통 완료 상태를 보존하는 실패 기록');
if ($phase === 'legacy_success') check_lifecycle(g5_migration_column_exists('mall_order', 'od_test'), '과거 success의 누락 스키마 재평가');
if ($phase === 'optional_created') check_lifecycle(g5_migration_column_exists('mall_inicis_log', 'P_AUTH_NO'), '나중에 생성된 선택 테이블 재평가');
echo $phase . " PASS\n";
