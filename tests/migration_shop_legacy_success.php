<?php
// 과거 success의 VARCHAR 확장 복구와 배송비 데이터 자동 재적용 방지. 격리 DB 전용.
if (PHP_SAPI !== 'cli' || !getenv('G5_TEST_DB_PORT')) exit("격리 DB 포트가 필요합니다.\n");
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('127.0.0.1', 'root', getenv('G5_TEST_DB_PASSWORD'), '', (int) getenv('G5_TEST_DB_PORT'));
if ($db->connect_errno) throw new RuntimeException('격리 DB 연결 실패');
$db->set_charset('utf8mb4');
$database = 'g5_legacy_success_' . getmypid();
$dir = sys_get_temp_dir() . '/g5-legacy-success-' . uniqid();
define('_GNUBOARD_', true);
define('G5_PATH', $dir);
define('G5_TABLE_PREFIX', 'custom_');
define('G5_SHOP_TABLE_PREFIX', 'mall_');
define('G5_USE_SHOP', !in_array('disabled', $argv, true));
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
$g5 = array('config_table' => 'custom_config', 'content_table' => 'custom_content');
function sql_query($sql, $error = false) {
    global $db, $deny_columns;
    if (!empty($deny_columns) && strpos($sql, 'SHOW COLUMNS FROM `mall_default`') === 0) return false;
    return $db->query($sql);
}
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_num_rows($result) { return $result->num_rows; }
function sql_fetch($sql, $error = false) { $r = sql_query($sql); return $r ? $r->fetch_assoc() : false; }
function sql_real_escape_string($value) { global $db; return $db->real_escape_string($value); }
function sql_error_info() { global $db; return $db->errno . ' ' . $db->error; }
function get_microtime() { return microtime(true); }
function legacy_check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
require dirname(__DIR__) . '/lib/migration.lib.php';
mkdir($dir . '/migrations', 0700, true);
legacy_check($db->query('CREATE DATABASE `' . $database . '`'), '임시 DB 생성');
$db->select_db($database);
try {
    foreach (array('20260904_003_backfill_legacy_data', '20260909_001_pg_easypay_services') as $id) {
        copy(dirname(__DIR__) . '/migrations/' . $id . '.sql', $dir . '/migrations/' . $id . '.sql');
    }
    legacy_check($db->query("CREATE TABLE mall_default (de_id int, de_easy_pay_services varchar(255) NOT NULL DEFAULT '')"), '설정 테이블');
    $db->query("INSERT INTO mall_default VALUES (1, 'preserve')");
    $fields = 'it_sc_type int DEFAULT 0, it_sc_method int DEFAULT 0, it_sc_price int DEFAULT 0, it_sc_minimum int DEFAULT 0, it_sc_qty int DEFAULT 0';
    $db->query('CREATE TABLE mall_cart (ct_id int PRIMARY KEY, it_id int, ' . $fields . ')');
    $db->query('CREATE TABLE mall_item (it_id int PRIMARY KEY, ' . $fields . ')');
    $db->query('INSERT INTO mall_cart (ct_id,it_id) VALUES (1,1), (2,1)');
    $db->query('INSERT INTO mall_item VALUES (1,2,0,3000,0,0)');
    legacy_check(g5_migration_ensure_table(), '이력 생성');
    foreach (g5_migration_discover() as $m) legacy_check(g5_migration_save_record($m, 'success', '', 0), '과거 성공 이력');
    $status = g5_migration_status();
    legacy_check($status[0]['status'] === 'success', '배송비 이력 자동 재분류 금지');
    legacy_check($status[1]['status'] === (G5_USE_SHOP ? 'shop_pending' : 'success'), '구형 VARCHAR 상태');
    $result = g5_migration_run();
    legacy_check($result['success'] && $result['applied'] === (G5_USE_SHOP ? 1 : 0), json_encode($result));
    $column = sql_fetch("SHOW COLUMNS FROM mall_default LIKE 'de_easy_pay_services'");
    legacy_check($column['Type'] === (G5_USE_SHOP ? 'varchar(1024)' : 'varchar(255)'), '확장 또는 미사용 보존');
    legacy_check(sql_fetch('SELECT de_easy_pay_services FROM mall_default')['de_easy_pay_services'] === 'preserve', '설정값 보존');
    legacy_check((int) sql_fetch('SELECT SUM(it_sc_price) AS price FROM mall_cart')['price'] === 0, '과거 성공 배송비 자동 변경 금지');
    $again = g5_migration_run();
    legacy_check($again['success'] && !$again['applied'] && !$again['skipped'], '반복 실행 없음');
    if (G5_USE_SHOP) {
        $db->query("ALTER TABLE mall_default MODIFY de_easy_pay_services varchar(2048) NOT NULL DEFAULT ''");
        legacy_check(g5_migration_status()[1]['status'] === 'success', '사용자 확장 보존');
        $wide = g5_migration_run();
        legacy_check($wide['success'] && !$wide['applied'], '사용자 확장 재실행 없음');
        legacy_check(sql_fetch("SHOW COLUMNS FROM mall_default LIKE 'de_easy_pay_services'")['Type'] === 'varchar(2048)', '컬럼 축소 금지');
        $deny_columns = true;
        legacy_check(g5_migration_status()[1]['status'] === 'shop_pending', '조회 실패는 성공 아님');
        $denied = g5_migration_run();
        legacy_check(!$denied['success'] && strpos(implode(' ', $denied['errors']), '조회 권한') !== false, '조회 실패 시 ALTER 차단');
        $deny_columns = false;
        legacy_check(sql_fetch("SHOW COLUMNS FROM mall_default LIKE 'de_easy_pay_services'")['Type'] === 'varchar(2048)', '조회 실패 후 넓은 컬럼 보존');
        legacy_check(g5_migration_run()['success'], '권한 복구 후 재시도');
        legacy_check($db->query('ALTER TABLE mall_default DROP de_easy_pay_services'), '누락 컬럼 재현');
        $missing = g5_migration_run();
        legacy_check(!$missing['success'] && strpos(implode(' ', $missing['errors']), '필요한 컬럼') !== false, '컬럼 부재 명시');
        $db->query("ALTER TABLE mall_default ADD de_easy_pay_services varchar(255) NOT NULL DEFAULT ''");
        legacy_check(g5_migration_run()['success'], '컬럼 복구 후 재시도');
        // 운영자가 원자료로 확인한 ct_id만 복구한다. 현재 상품 설정만으로 누락을 단정하지 않는다.
        $repair = 'UPDATE mall_cart c INNER JOIN mall_item i ON i.it_id=c.it_id '
            . 'SET c.it_sc_type=i.it_sc_type, c.it_sc_method=i.it_sc_method, c.it_sc_price=i.it_sc_price, c.it_sc_minimum=i.it_sc_minimum, c.it_sc_qty=i.it_sc_qty '
            . 'WHERE c.ct_id IN (1) AND c.it_sc_type=0 AND c.it_sc_method=0 AND c.it_sc_price=0 AND c.it_sc_minimum=0 AND c.it_sc_qty=0';
        legacy_check($db->query($repair) && $db->affected_rows === 1, '확인된 행만 복구');
        legacy_check((int) sql_fetch('SELECT it_sc_price FROM mall_cart WHERE ct_id=2')['it_sc_price'] === 0, '미확인 행 보존');
        legacy_check($db->query($repair) && $db->affected_rows === 0, '선택 복구 반복 안전성');
    }
    echo (G5_USE_SHOP ? 'enabled' : 'disabled') . " legacy success PASS\n";
} finally {
    $db->query('DROP DATABASE `' . $database . '`');
    foreach (glob($dir . '/migrations/*.sql') as $file) unlink($file);
    rmdir($dir . '/migrations');
    rmdir($dir);
}
