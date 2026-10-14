<?php
// 실행: php tests/migration_install_history.php /path/to/isolated/mysql.sock
// root/빈 비밀번호인 격리 MySQL에 임시 DB를 만들고 종료 시 삭제한다.
if (PHP_SAPI !== 'cli' || empty($argv[1])) exit("격리 MySQL 소켓 경로가 필요합니다.\n");
define('_GNUBOARD_', true);
define('G5_PATH', dirname(__DIR__));
define('G5_TABLE_PREFIX', 'custom_');
define('G5_SHOP_TABLE_PREFIX', 'mall_');
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
require G5_PATH . '/lib/migration.lib.php';
require G5_PATH . '/install/install.function.php';
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('localhost', 'root', '', '', 0, $argv[1]);
if ($db->connect_errno) exit("격리 DB 연결 실패\n");
$database = 'g5_migration_test_' . getmypid();
if (!$db->query("CREATE DATABASE `{$database}`")) exit("임시 DB 생성 실패\n");
$db->select_db($database);
$db->set_charset('utf8mb4');
$db->query("SET sql_mode = ''");
$queries = array();
$forced_failure = '';
function sql_query($sql, $error = false) {
    global $db, $queries, $forced_failure;
    $queries[] = $sql;
    if ($forced_failure !== '' && strpos($sql, $forced_failure) !== false) return false;
    return $db->query($sql);
}
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_fetch($sql, $error = false) { $result = sql_query($sql); return $result ? sql_fetch_array($result) : false; }
function sql_real_escape_string($value) { global $db; return $db->real_escape_string($value); }
function sql_error_info() { global $db, $forced_failure; return $forced_failure ? '테스트용 DB 실패' : $db->errno . ' ' . $db->error; }
function get_microtime() { return microtime(true); }
function expect_history($ok, $message) { if (!$ok) throw new Exception($message); }
$g5 = array('write_prefix' => G5_TABLE_PREFIX . 'write_');
foreach (array('auth', 'board', 'board_file', 'config', 'content', 'login', 'member', 'memo', 'qa_config', 'visit') as $name) {
    $g5[$name . '_table'] = G5_TABLE_PREFIX . $name;
}
$g5['social_profile_table'] = G5_TABLE_PREFIX . 'member_social_profiles';
$table = g5_migration_table_name();
try {
    // 보고된 빈 테이블의 1054를 재현한 뒤 조회와 두 실행 경로에서 사전 차단한다.
    $db->query("CREATE TABLE `{$table}` (id int PRIMARY KEY)");
    expect_history(!g5_migration_save_record(array('id'=>'test', 'description'=>'', 'checksum'=>''), 'success', '', 0) && $db->errno === 1054, '1054 재현');
    foreach (array(false, true) as $has_data) {
        if ($has_data) $db->query("INSERT INTO `{$table}` VALUES (1)");
        $queries = array();
        $statuses = g5_migration_status();
        expect_history(isset($statuses[0]['error']), '잘못된 구조 조회 오류 표시');
        foreach ($queries as $query) expect_history(!preg_match('/^(CREATE|ALTER|INSERT|UPDATE|DELETE|DROP)/i', $query), '조회 중 쓰기 금지');
        foreach (array(false, true) as $existing) {
            $queries = array();
            $result = g5_migration_run('', $existing);
            expect_history(!$result['success'] && strpos($result['errors'][0], '구조') !== false, '실행 전 구조 검사');
            foreach ($queries as $query) expect_history(!preg_match('/^(ALTER|INSERT|UPDATE|DELETE|DROP)/i', $query), '잘못된 테이블 및 업무 DB 보존');
            expect_history((int)sql_fetch("SELECT IS_FREE_LOCK('g5_migration_" . substr(md5(G5_TABLE_PREFIX), 0, 16) . "') AS available")['available'] === 1, '실패 후 잠금 해제');
        }
    }
    // 사용자 지정 접두어와 쇼핑몰 선택 여부에 관계없이 배포 파일 전부를 기록한다.
    foreach (array(false, true) as $shop) {
        foreach (install_load_sql_file(G5_PATH . '/install/gnuboard5.sql', 'g5_', G5_TABLE_PREFIX) as $query) {
            expect_history((bool)$db->query($query), '기본 스키마: ' . $db->error);
        }
        if ($shop) foreach (install_load_sql_file(G5_PATH . '/install/gnuboard5shop.sql', 'g5_shop_', G5_SHOP_TABLE_PREFIX) as $query) {
            expect_history((bool)$db->query($query), '쇼핑몰 스키마: ' . $db->error);
        }
        $queries = array();
        expect_history(install_record_migrations(G5_TABLE_PREFIX) === '', '신규 설치 이력 등록');
        foreach ($queries as $query) expect_history(!preg_match('/^(CREATE|ALTER|UPDATE|DELETE|DROP)/i', $query), '과거 마이그레이션 SQL 미실행');
        $records = g5_migration_get_records($error);
        expect_history($error === '' && count($records) === count(glob(G5_PATH . '/migrations/*.sql')), '배포 이력 수');
        foreach (g5_migration_discover() as $migration) {
            $record = $records[$migration['id']];
            expect_history($record['status'] === 'success' && $record['checksum'] === $migration['checksum'] && $record['applied_at'] === G5_TIME_YMDHIS, '체크섬 및 설치 시각');
        }
        foreach (g5_migration_status() as $status) expect_history($status['status'] === 'success' && !$status['checksum_changed'], '설치 직후 모두 완료');
        $result = g5_migration_run();
        expect_history($result['success'] && !$result['applied'] && !$result['skipped'], '설치 직후 재실행 없음');
    }
    $forced_failure = 'SELECT migration_id, checksum';
    expect_history(isset(g5_migration_status()[0]['error']), '이력 읽기 실패 표시');
    expect_history(!g5_migration_run()['success'], '이력 읽기 실패 시 실행 차단');
    $forced_failure = 'INSERT INTO';
    expect_history(strpos(install_record_migrations(G5_TABLE_PREFIX), '설치 이력을 저장하지 못했습니다') !== false, '설치 이력 저장 실패 전파');
    $forced_failure = '';
    $db->query("ALTER TABLE `{$table}` DROP PRIMARY KEY");
    expect_history(strpos(g5_migration_validate_table(), '기본키') !== false, '기본키 누락 감지');
    echo "이력 테이블 1054 재현·쓰기 차단, 신규 설치 이력·체크섬·재실행·실패 처리 검사 통과\n";
} finally {
    $db->query("DROP DATABASE `{$database}`");
    $db->close();
}
