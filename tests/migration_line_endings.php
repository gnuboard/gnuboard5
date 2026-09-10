<?php
// 실행: php tests/migration_line_endings.php (DB 연결 불필요)
define('_GNUBOARD_', true);
define('G5_TABLE_PREFIX', 'g5_');
$g5 = array('config_table' => 'g5_config');
require dirname(__DIR__) . '/lib/migration.lib.php';

$directory = sys_get_temp_dir() . '/g5-migration-' . uniqid('', true);
mkdir($directory);
$file = $directory . '/20260904_001_add_email_certify_minutes.sql';
$count = 0;
try {
    foreach (array("\n", "\r\n") as $newline) {
        foreach (array('', ' ', "\t") as $trailing) {
            foreach (array(false, true) as $atEnd) {
                $directive = '-- @skip-if-column {{config_table}} cf_email_certify_minutes' . $trailing;
                $sql = 'ALTER TABLE `{{config_table}}` ADD `cf_email_certify_minutes` int;';
                $contents = $atEnd ? $sql . $newline . $directive : $directive . $newline . $sql . $newline;
                file_put_contents($file, $contents);
                $parsed = g5_migration_parse_file($file);
                if ($parsed['skip_table'] !== 'g5_config' || $parsed['skip_column'] !== 'cf_email_certify_minutes'
                    || $parsed['checksum'] !== hash('sha256', $contents) || count($parsed['statements']) !== 1) {
                    throw new RuntimeException('줄바꿈·공백·EOF 조건 또는 원본 체크섬 보존 실패');
                }
                $count++;
            }
        }
    }
    // 불완전한 지시문의 컬럼을 다음 줄에서 가져오면 안 된다.
    file_put_contents($file, "-- @skip-if-column {{config_table}}\r\nSELECT 1;\r\n");
    $parsed = g5_migration_parse_file($file);
    if ($parsed['skip_table'] !== '' || $parsed['skip_column'] !== '') {
        throw new RuntimeException('지시문이 다음 줄을 소비함');
    }
    $count++;
    echo $count . "개 마이그레이션 줄바꿈 검사 통과\n";
} finally {
    unlink($file);
    rmdir($directory);
}
