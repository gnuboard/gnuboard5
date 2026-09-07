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
require_once G5_LIB_PATH . '/shop_install.lib.php';

function g5_shop_install_test_default_values($source)
{
    preg_match_all("/\\b(de_[a-z0-9_]+)\\s*=\\s*'([^']*)'/i", $source, $matches, PREG_SET_ORDER);
    $values = array();
    foreach ($matches as $match) {
        $values[$match[1]] = $match[2];
    }
    return $values;
}

$install_source = @file_get_contents(G5_PATH . '/install/install_db.php');
$shop_install_source = @file_get_contents(G5_LIB_PATH . '/shop_install.lib.php');
if ($install_source === false || $shop_install_source === false ||
    !preg_match('/insert into `\{\$g5_shop_prefix\}default`\s+set(.*?)\n\s+";/s', $install_source, $install_defaults) ||
    !preg_match('/\$sql = "INSERT INTO `\{\$table\}` SET(.*?)";\n\s+return sql_query/s', $shop_install_source, $shop_defaults)) {
    fwrite(STDERR, "[실패] 기본 설치와 후설치의 쇼핑몰 설정을 비교할 수 없습니다.\n");
    exit(1);
}
preg_match_all('/\b(de_[a-z0-9_]+)\s*=/i', $install_defaults[1], $install_fields);
preg_match_all('/\b(de_[a-z0-9_]+)\s*=/i', $shop_defaults[1], $shop_fields);
$install_fields = array_values(array_unique($install_fields[1]));
$shop_fields = array_values(array_unique($shop_fields[1]));
sort($install_fields, SORT_STRING);
sort($shop_fields, SORT_STRING);
if ($install_fields !== $shop_fields) {
    fwrite(STDERR, "[실패] 기본 설치와 후설치의 쇼핑몰 기본 설정 필드가 다릅니다.\n");
    exit(1);
}
$install_values = g5_shop_install_test_default_values($install_defaults[1]);
$shop_values = g5_shop_install_test_default_values($shop_defaults[1]);
$install_variables = array(
    '$ssimg_width' => '160', '$ssimg_height' => '160', '$simg_width' => '215', '$simg_height' => '215',
    '$mimg_width' => '230', '$mimg_height' => '230', '$mmimg_width' => '300', '$mmimg_height' => '300',
    '$msimg_width' => '80', '$msimg_height' => '80', '$list_img_width' => '225', '$list_img_height' => '225'
);
foreach ($install_values as $field => $value) {
    $value = strtr($value, $install_variables);
    if (!isset($shop_values[$field]) || $shop_values[$field] !== $value) {
        fwrite(STDERR, '[실패] 기본 설치와 후설치의 쇼핑몰 기본 설정값이 다릅니다: ' . $field . "\n");
        exit(1);
    }
}

$suffix = substr(md5(getmypid() . microtime()), 0, 10);
$prefix = G5_TABLE_PREFIX . 'shop_install_test_' . $suffix . '_';
$data_path = sys_get_temp_dir() . '/g5-shop-install-' . $suffix;
$config_file = $data_path . '/dbconfig.php';
$tables = g5_shop_install_table_names($prefix);

function g5_shop_install_test_cleanup()
{
    global $tables, $prefix, $data_path;
    foreach ($tables as $name) {
        sql_query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $prefix . $name) . "`", false);
    }
    if (is_dir($data_path)) {
        $dirs = array('banner', 'common', 'event', 'item');
        foreach ($dirs as $dir) {
            $path = $data_path . '/' . $dir;
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        @unlink($path . '/' . $file);
                    }
                }
                @rmdir($path);
            }
        }
        @unlink($data_path . '/dbconfig.php');
        @rmdir($data_path);
    }
}

function g5_shop_install_test_fail($message)
{
    fwrite(STDERR, '[실패] ' . $message . "\n");
    exit(1);
}

// 모든 마이그레이션의 쇼핑몰 SQL만 격리 접두어로 실행한다.
// 공통 스키마가 섞인 파일에서도 쇼핑몰 문장의 조건을 빠짐없이 검사한다.
function g5_shop_install_test_migrations($prefix, $installed)
{
    $checked = 0;
    foreach (g5_migration_discover() as $migration) {
        if (isset($migration['error'])) {
            g5_shop_install_test_fail($migration['error']);
        }
        foreach ($migration['statements'] as $statement) {
            if (strpos($statement['sql'], '`' . $prefix) === false) {
                continue;
            }
            $checked++;
            if (!preg_match('/^(?:CREATE TABLE(?: IF NOT EXISTS)?|ALTER TABLE|UPDATE)\s+`' . preg_quote($prefix, '/') . '/i', $statement['sql'])) {
                g5_shop_install_test_fail('격리된 쇼핑몰 테이블을 대상으로 하지 않는 SQL입니다: ' . $migration['id']);
            }
            $should_run = g5_migration_should_run_statement($statement['conditions']);
            if (!$installed && $should_run) {
                g5_shop_install_test_fail('쇼핑몰 미설치 상태에서 실행되는 SQL이 있습니다: ' . $migration['id']);
            }
            if ($should_run) {
                $result = g5_migration_execute_statement($statement);
                if ($result['error'] !== '') {
                    g5_shop_install_test_fail($migration['id'] . ': ' . $result['error']);
                }
            }
        }
    }
    if (!$checked) {
        g5_shop_install_test_fail('쇼핑몰 마이그레이션 검사 대상이 없습니다.');
    }
    return $checked;
}

register_shutdown_function('g5_shop_install_test_cleanup');
g5_shop_install_test_cleanup();

// SQL과 조건에서 사용되는 쇼핑몰 자리표시자를 모두 임시 테이블에 연결한다.
$shop_keys = array();
foreach (glob(G5_PATH . '/migrations/*.sql') as $migration_file) {
    preg_match_all('/\{\{(g5_shop_[a-z0-9_]+_table)\}\}/', file_get_contents($migration_file), $matches);
    foreach ($matches[1] as $key) {
        $shop_keys[$key] = true;
    }
}
$source_shop_prefix = defined('G5_SHOP_TABLE_PREFIX') ? G5_SHOP_TABLE_PREFIX : G5_TABLE_PREFIX . 'shop_';
foreach ($shop_keys as $key => $unused) {
    $original = g5_migration_replace_placeholders('{{' . $key . '}}');
    if (strpos($original, $source_shop_prefix) !== 0) {
        g5_shop_install_test_fail('쇼핑몰 테이블 접두어를 격리하지 못했습니다: ' . $key);
    }
    $g5[$key] = $prefix . substr($original, strlen($source_shop_prefix));
}
$checked_statements = g5_shop_install_test_migrations($prefix, false);
if (g5_shop_install_existing_tables($prefix) !== array()) {
    g5_shop_install_test_fail('쇼핑몰 미설치 검사 후 테이블이 생성되었습니다.');
}
if (!@mkdir($data_path, G5_DIR_PERMISSION) || @file_put_contents($config_file, "<?php\n// 기존 설정 보존 표식\n?>") === false) {
    g5_shop_install_test_fail('격리 테스트 설정 파일을 준비하지 못했습니다.');
}

$result = g5_shop_install_run(array(
    'allow_enabled' => true,
    'prefix' => $prefix,
    'data_path' => $data_path,
    'config_file' => $config_file
));
if (!$result['success']) {
    g5_shop_install_test_fail($result['error']);
}

foreach ($tables as $name) {
    if (!g5_migration_table_exists($prefix . $name)) {
        g5_shop_install_test_fail('쇼핑몰 테이블이 누락되었습니다: ' . $prefix . $name);
    }
}
// 미설치 업그레이드 뒤 후설치는 과거 이력과 관계없이 최신 설치 SQL을 사용한다.
// 쇼핑몰을 먼저 설치한 경우의 업그레이드 SQL도 최신 스키마에서 정상 처리돼야 한다.
g5_shop_install_test_migrations($prefix, true);
$default = sql_fetch("SELECT de_shop_skin, de_shop_mobile_skin, de_sms_cont1, de_sms_cont2, de_sms_cont3, de_sms_cont4, de_sms_cont5 FROM `{$prefix}default` LIMIT 1", false);
if (!isset($default['de_shop_skin']) || $default['de_shop_skin'] !== 'basic' || $default['de_shop_mobile_skin'] !== 'basic') {
    g5_shop_install_test_fail('쇼핑몰 기본 설정이 올바르게 생성되지 않았습니다.');
}
for ($index = 1; $index <= 5; $index++) {
    preg_match_all('/./us', $default['de_sms_cont' . $index], $characters);
    $bytes = 0;
    foreach ($characters[0] as $character) {
        $bytes += strlen($character) > 1 ? 2 : 1;
    }
    if ($bytes > 80) {
        g5_shop_install_test_fail('기본 SMS 문구가 80바이트를 초과합니다: de_sms_cont' . $index);
    }
}
$config = @file_get_contents($config_file);
if ($config === false || strpos($config, '기존 설정 보존 표식') === false || strpos($config, "define('G5_USE_SHOP', true)") === false || strpos($config, $prefix) === false) {
    g5_shop_install_test_fail('기존 DB 설정을 보존하면서 쇼핑몰 설정을 추가하지 못했습니다.');
}
$retry = g5_shop_install_run(array(
    'allow_enabled' => true,
    'prefix' => $prefix,
    'data_path' => $data_path,
    'config_file' => $config_file
));
if ($retry['success']) {
    g5_shop_install_test_fail('이미 설치된 쇼핑몰의 중복 설치를 차단하지 못했습니다.');
}

echo '쇼핑몰 SQL ' . $checked_statements . "개 미설치 건너뛰기·후설치 후 업그레이드·설정 보존·중복 실행 차단 검사가 통과했습니다.\n";
