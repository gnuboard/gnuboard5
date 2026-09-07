#!/usr/bin/env php
<?php
// 실제 DB 대신 SQL 응답을 제공해 이력 등록의 순서와 쓰기 범위를 검증한다.
if (PHP_SAPI !== 'cli') exit("CLI에서만 실행할 수 있습니다.\n");
define('_GNUBOARD_', true);
define('G5_TABLE_PREFIX', 'inspection_test_');
define('G5_TIME_YMDHIS', '2026-09-07 00:00:00');
define('G5_PATH', sys_get_temp_dir() . '/g5-existing-' . uniqid());
mkdir(G5_PATH);
mkdir(G5_PATH . '/migrations');
$g5 = array('config_table' => 'inspection_test_config');
$records = array();
$queries = array();
$columns = array('present');
$lock_available = true;
$history_readable = true;

class MigrationInspectionRows
{
    public $rows;
    public function __construct($rows) { $this->rows = $rows; }
}
function sql_real_escape_string($value) { return addslashes($value); }
function sql_error_info() { return '검사용 오류'; }
function get_microtime() { return microtime(true); }
function sql_fetch_array($result) { return array_shift($result->rows); }
function sql_num_rows($result) { return count($result->rows); }
function sql_fetch($sql, $error = true) {
    $result = sql_query($sql, $error);
    return $result ? sql_fetch_array($result) : false;
}
function sql_query($sql, $error = true) {
    global $queries, $records, $columns, $lock_available, $history_readable;
    $queries[] = $sql;
    if (strpos($sql, 'SHOW TABLES') === 0) return new MigrationInspectionRows(array(array('table' => 'inspection_test_migrations')));
    if (strpos($sql, 'SHOW COLUMNS') === 0) {
        $rows = array();
        foreach ($columns as $column) $rows[] = array('Field' => $column);
        return new MigrationInspectionRows($rows);
    }
    if (strpos($sql, 'SELECT migration_id') === 0) return $history_readable ? new MigrationInspectionRows(array_values($records)) : false;
    if (strpos($sql, 'SELECT COUNT(*)') === 0) return new MigrationInspectionRows(array(array('total' => count($records))));
    if (strpos($sql, 'SELECT GET_LOCK') === 0) return new MigrationInspectionRows(array(array('acquired' => $lock_available ? 1 : 0)));
    if (strpos($sql, 'SELECT RELEASE_LOCK') === 0 || strpos($sql, 'CREATE TABLE IF NOT EXISTS `inspection_test_migrations`') === 0) return true;
    if (strpos($sql, 'INSERT INTO `inspection_test_migrations`') === 0) {
        preg_match("/migration_id = '([^']+)'/", $sql, $id);
        preg_match("/checksum = '([^']+)'/", $sql, $checksum);
        $records[$id[1]] = array('migration_id' => $id[1], 'checksum' => $checksum[1], 'status' => 'success', 'error_message' => '', 'applied_at' => G5_TIME_YMDHIS);
        return true;
    }
    fwrite(STDERR, '[실패] 허용하지 않은 SQL: ' . $sql . "\n");
    exit(1);
}
function inspection_check($condition, $message) {
    if (!$condition) { fwrite(STDERR, '[실패] ' . $message . "\n"); exit(1); }
}
function inspection_cleanup() {
    foreach (glob(G5_PATH . '/migrations/*') as $file) unlink($file);
    rmdir(G5_PATH . '/migrations');
    rmdir(G5_PATH);
}
register_shutdown_function('inspection_cleanup');
require dirname(dirname(__FILE__)) . '/lib/migration.lib.php';
$ids = array('20200101_001_existing', '20200102_001_missing', '20200103_001_later');
foreach ($ids as $index => $id) {
    $column = $index === 1 ? 'missing' : 'present';
    file_put_contents(G5_PATH . '/migrations/' . $id . '.sql', "-- @if-column-missing {{config_table}} $column\nALTER TABLE `{{config_table}}` ADD `$column` int;\n");
}
$statuses = g5_migration_status();
inspection_check($statuses[0]['status'] === 'unrecorded' && $statuses[1]['status'] === 'pending' && $statuses[2]['status'] === 'unrecorded', '기존·미적용 상태 구분');
foreach ($queries as $query) inspection_check(preg_match('/^(SHOW|SELECT) /', $query), '상태 조회에서 DB 쓰기 발생');
$result = g5_migration_run('', true);
inspection_check($result['success'] && $result['skipped'] === 1 && count($records) === 1 && isset($records[$ids[0]]), '미적용 선행 항목을 넘어 등록함');
$result = g5_migration_run('', true);
inspection_check($result['skipped'] === 0 && count($records) === 1, '반복 등록');
$statuses = g5_migration_status();
inspection_check($statuses[0]['status'] === 'success' && $statuses[2]['status'] === 'unrecorded', '부분 이력 이후 검사 누락');
$records[$ids[0]]['checksum'] = 'changed';
$result = g5_migration_run('', true);
inspection_check(!$result['success'] && count($records) === 1, '체크섬 변경 무시');
$records[$ids[0]]['status'] = 'failed';
$columns[] = 'missing';
$result = g5_migration_run('', true);
inspection_check($result['skipped'] === 0 && $records[$ids[0]]['status'] === 'failed', '실패 이력을 덮어씀');
$records = array();
$lock_available = false;
$result = g5_migration_run('', true);
inspection_check(!$result['success'] && !$records, '잠금 실패 후 등록');
$lock_available = true;
$result = g5_migration_run('', true);
inspection_check($result['success'] && $result['skipped'] === 3 && count($records) === 3, '재검사 후 기존 항목 등록 실패');
$history_readable = false;
$result = g5_migration_run('', true);
inspection_check(!$result['success'] && count($records) === 3, '이력 조회 실패 후 덮어씀');
echo "기존 상태 조회·순차 이력 등록·재검사·실패/체크섬/잠금 보호 검사가 통과했습니다.\n";
