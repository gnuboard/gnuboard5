<?php
// 격리 DB 전용: G5_TEST_DB_PORT, G5_TEST_DB_PASSWORD 환경변수를 지정한다.
// 실행: php tests/migration_shop_conditions.php undefined|disabled|enabled
if (PHP_SAPI !== 'cli' || !getenv('G5_TEST_DB_PORT')) exit("격리 DB 포트가 필요합니다.\n");
$mode = isset($argv[1]) ? $argv[1] : 'undefined';
if (!in_array($mode, array('undefined', 'disabled', 'enabled'), true)) exit(1);
define('_GNUBOARD_', true);
define('G5_TABLE_PREFIX', 'custom_');
define('G5_SHOP_TABLE_PREFIX', 'mall_');
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
if ($mode !== 'undefined') define('G5_USE_SHOP', $mode === 'enabled');
$root = dirname(__DIR__);
$fixture = sys_get_temp_dir() . '/g5-shop-migration-' . uniqid();
mkdir($fixture . '/migrations', 0700, true);
define('G5_PATH', $fixture);
require $root . '/lib/migration.lib.php';
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('127.0.0.1', 'root', getenv('G5_TEST_DB_PASSWORD'), '', (int) getenv('G5_TEST_DB_PORT'));
if ($db->connect_errno) throw new RuntimeException('격리 DB 연결 실패');
$database = 'g5_shop_migration_test_' . getmypid();
$db->query('CREATE DATABASE `' . $database . '`');
$db->select_db($database);
$db->set_charset('utf8mb4');
$db->query("SET sql_mode = ''");
$g5 = array('config_table' => 'custom_config');
$queries = array();
$forced_failure = '';
function sql_query($sql, $error = false) {
    global $db, $queries, $forced_failure;
    $queries[] = $sql;
    return $forced_failure !== '' && strpos($sql, $forced_failure) !== false ? false : $db->query($sql);
}
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_num_rows($result) { return $result->num_rows; }
function sql_fetch($sql, $error = false) { $result = sql_query($sql); return $result ? sql_fetch_array($result) : false; }
function sql_real_escape_string($value) { global $db; return $db->real_escape_string($value); }
function sql_error_info() { global $db; return $db->errno . ' ' . $db->error; }
function get_microtime() { return microtime(true); }
function check_shop($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function reset_shop_history() { global $db; $db->query('DROP TABLE IF EXISTS custom_migrations'); }
try {
    foreach (array('20160422_001_shop_test_order', '20261008_001_abuse_rate', '20261008_002_abuse_rate_transaction') as $id) {
        copy($root . '/migrations/' . $id . '.sql', $fixture . '/migrations/' . $id . '.sql');
    }
    // 하나의 파일에 공통/쇼핑몰 SQL이 함께 있어도 공통 SQL은 실행해야 한다.
    file_put_contents($fixture . '/migrations/20261102_001_mixed.sql',
        file_get_contents($root . '/migrations/20160422_001_shop_test_order.sql')
        . "\n-- @if-column-missing {{config_table}} marker\nALTER TABLE `{{config_table}}` ADD `marker` int NOT NULL DEFAULT 0;\n");
    $db->query('CREATE TABLE custom_config (id int)');
    $db->query('CREATE TABLE mall_order (od_id bigint PRIMARY KEY)');
    $db->query('INSERT INTO mall_order VALUES (123)');
    $order = g5_migration_parse_file($fixture . '/migrations/20160422_001_shop_test_order.sql');
    check_shop($order['checksum'] === hash_file('sha256', $root . '/migrations/20160422_001_shop_test_order.sql'), '기존 체크섬 보존');
    check_shop(!$db->query($order['statements'][0]['sql']) && $db->errno === 1054, '원본 SQL 1054 재현');

    if ($mode === 'enabled') {
        $statuses = g5_migration_status();
        check_shop(strpos($statuses[0]['error_message'], 'od_settle_case') !== false, '조회에서 누락 컬럼 표시');
        $result = g5_migration_run('', true);
        check_shop(!$result['success'] && !$result['skipped'] && strpos($result['errors'][0], 'od_settle_case') !== false, '불완전 구조의 기존 상태 이력 등록 차단');
        $result = g5_migration_run();
        check_shop(!$result['success'] && strpos($result['errors'][0], 'od_settle_case') !== false, '불완전 구조 실행 차단');
        check_shop(!g5_migration_table_exists('custom_abuse_rate'), '실패 후 후속 SQL 미실행');
        $records = g5_migration_get_records($error);
        check_shop($records[$order['id']]['status'] === 'failed', '불완전 구조를 성공으로 기록하지 않음');
        $db->query('ALTER TABLE mall_order ADD od_settle_case varchar(255) NOT NULL DEFAULT \'\'');
        $result = g5_migration_run();
        check_shop($result['success'] && g5_migration_column_exists('mall_order', 'od_test'), '필수 구조 복원 후 실패 이력 재시도');
        check_shop(g5_migration_column_exists('custom_config', 'marker'), '혼합 파일 공통 SQL 실행');
        check_shop(!g5_migration_run()['applied'], '성공 후 재실행 없음');

        reset_shop_history();
        $db->query('DROP TABLE mall_order');
        $result = g5_migration_run();
        check_shop(!$result['success'] && strpos($result['errors'][0], '필요한 테이블') !== false, '쇼핑몰 설정이 있는데 주문 테이블 없음');
        $db->query('CREATE TABLE mall_order (od_id bigint, od_settle_case varchar(255))');
        reset_shop_history();
        $forced_failure = 'SHOW COLUMNS FROM `mall_order`';
        $result = g5_migration_run();
        check_shop(!$result['success'] && strpos($result['errors'][0], '조회 권한') !== false, '조회 실패를 미설치로 취급하지 않음');
        $forced_failure = '';
        // 앞선 ADD로 생성한 컬럼을 다음 AFTER가 참조하는 경우 캐시를 재사용하면 안 된다.
        $db->query('CREATE TABLE mall_cart (it_name varchar(255))');
        copy($root . '/migrations/20140331_001_shop_cart_shipping.sql', $fixture . '/migrations/20140331_001_shop_cart_shipping.sql');
        $result = g5_migration_run();
        check_shop($result['success'] && g5_migration_column_exists('mall_cart', 'it_sc_qty'), '연속 ADD AFTER에서 직전 변경 재검사');
    } else {
        $statuses = g5_migration_status();
        check_shop($statuses[0]['status'] === 'unrecorded', '미설치 잔여 테이블은 실행 불필요');
        $result = g5_migration_run('', true);
        check_shop($result['success'] && $result['skipped'] === 1, '미설치 기존 상태 이력 등록');
        reset_shop_history();
        // 이전 버전에서 실패한 이력도 전체 실행으로 복구할 수 있어야 한다.
        g5_migration_ensure_table();
        g5_migration_save_record($order, 'failed', '1054', 0);
        $queries = array();
        $result = g5_migration_run();
        check_shop($result['success'], '미설치 전체 실행 성공');
        foreach ($queries as $query) check_shop(!preg_match('/^(ALTER|UPDATE|DELETE|INSERT|CREATE).*`mall_order`/i', $query), '잔여 주문 테이블 쓰기 없음');
        check_shop(!g5_migration_column_exists('mall_order', 'od_test') && (int) sql_fetch('SELECT od_id FROM mall_order')['od_id'] === 123, '잔여 구조와 데이터 보존');
        check_shop(g5_migration_column_exists('custom_config', 'marker'), '혼합 파일 공통 SQL 실행');
        $row = sql_fetch("SHOW TABLE STATUS LIKE 'custom_abuse_rate'");
        check_shop(strtolower($row['Engine']) === 'innodb', '후속 비밀번호 찾기 저장소 준비');
        $records = g5_migration_get_records($error);
        check_shop($records[$order['id']]['status'] === 'success', '미설치 실패 이력 복구');
        check_shop(!g5_migration_run()['applied'], '성공 후 재실행 없음');
        reset_shop_history();
        $db->query('DROP TABLE mall_order');
        check_shop(g5_migration_run()['success'], '테이블이 전혀 없는 미설치도 성공');
    }
    echo $mode . ": 쇼핑몰 실행 조건 회귀 검사 통과\n";
} finally {
    $db->query('DROP DATABASE `' . $database . '`');
    $db->close();
    foreach (glob($fixture . '/migrations/*.sql') as $file) unlink($file);
    rmdir($fixture . '/migrations');
    rmdir($fixture);
}
