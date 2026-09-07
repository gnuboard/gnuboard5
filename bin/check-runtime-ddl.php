#!/usr/bin/env php
<?php
if (PHP_SAPI !== 'cli') {
    exit("CLI에서만 실행할 수 있습니다.\n");
}

$root = dirname(dirname(__FILE__));
$scan_paths = array('');
$excluded = array(
    'lib/migration.lib.php',
    'lib/PHPExcel/',
    'data/',
    'install/',
    'migrations/',
    'bin/check-migration-runner.php',
    'bin/check-shop-install.php'
);
$lifecycle_ddl_files = array(
    'adm/board_delete.inc.php',
    'lib/shop_install.lib.php'
);
$errors = array();

foreach ($scan_paths as $scan_path) {
    $base = $root . '/' . $scan_path;
    if (!is_dir($base)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($iterator as $file) {
        $pathname = $file->getPathname();
        if (!$file->isFile() || strtolower(pathinfo($pathname, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }

        $relative = str_replace($root . '/', '', str_replace('\\', '/', $pathname));
        $skip = false;
        foreach ($excluded as $exclude) {
            if ($relative === $exclude || strpos($relative, $exclude) === 0) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }

        if (in_array($relative, $lifecycle_ddl_files)) {
            continue;
        }

        $contents = file_get_contents($pathname);
        $contents = preg_replace('/^.*\$schema_create.*$/m', '', $contents);
        if (preg_match_all('/\b(?:ALTER\s+TABLE|CREATE\s+TABLE|DROP\s+TABLE|RENAME\s+(?:TABLE|TO)|TRUNCATE\s+TABLE)\b/i', $contents, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $line = substr_count(substr($contents, 0, $match[1]), "\n") + 1;
                $errors[] = $relative . ':' . $line . ': ' . preg_replace('/\s+/', ' ', trim($match[0]));
            }
        }
    }
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[실패] ' . $error . "\n");
    }
    exit(1);
}

echo "일반 실행 경로에 DDL 문이 없습니다.\n";
