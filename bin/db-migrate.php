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

$command = isset($argv[1]) ? $argv[1] : 'status';

if ($command === 'status') {
    $statuses = g5_migration_status();
    foreach ($statuses as $status) {
        if (isset($status['error'])) {
            fwrite(STDERR, '[오류] ' . $status['error'] . "\n");
            exit(1);
        }
        $label = g5_migration_status_label($status['status'], $status['checksum_changed']);
        echo '[' . $label . '] ' . $status['id'] . ' ' . $status['description'] . "\n";
    }
    exit(0);
}

if ($command !== 'migrate' && $command !== 'record-existing') {
    fwrite(STDERR, "사용법: php bin/db-migrate.php status | migrate [마이그레이션_ID] | record-existing\n");
    exit(2);
}

$target_id = $command === 'migrate' && isset($argv[2]) && $argv[2] !== '--all' ? $argv[2] : '';
$result = g5_migration_run($target_id, $command === 'record-existing');
if (!$result['success']) {
    foreach ($result['errors'] as $error) {
        fwrite(STDERR, '[실패] ' . $error . "\n");
    }
    exit(1);
}

echo '적용: ' . $result['applied'] . ', 기존 스키마 확인: ' . $result['skipped'] . "\n";
