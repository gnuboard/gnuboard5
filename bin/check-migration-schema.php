#!/usr/bin/env php
<?php
if (PHP_SAPI !== 'cli') exit("CLI에서만 실행할 수 있습니다.\n");

$root = dirname(dirname(__FILE__));
$install_files = array($root.'/install/gnuboard5.sql', $root.'/install/gnuboard5shop.sql', $root.'/adm/sql_write.sql');
$migration_files = glob($root.'/migrations/*.sql');
$install_sql = '';
foreach ($install_files as $install_file) {
    $contents = @file_get_contents($install_file);
    if ($contents === false) {
        fwrite(STDERR, '신규 설치 SQL을 읽을 수 없습니다: '.basename($install_file)."\n");
        exit(1);
    }
    $install_sql .= "\n".$contents;
}

$table_map = array(
    '{{auth_table}}'=>'g5_auth', '{{board_file_table}}'=>'g5_board_file', '{{config_table}}'=>'g5_config',
    '{{content_table}}'=>'g5_content', '{{login_table}}'=>'g5_login', '{{member_table}}'=>'g5_member',
    '{{member_auto_login_table}}'=>'g5_member_auto_login', '{{memo_table}}'=>'g5_memo',
    '{{qa_config_table}}'=>'g5_qa_config', '{{social_profile_table}}'=>'g5_member_social_profiles',
    '{{visit_table}}'=>'g5_visit', '{{write_table}}'=>'__TABLE_NAME__',
    '{{g5_shop_cart_table}}'=>'g5_shop_cart', '{{g5_shop_coupon_log_table}}'=>'g5_shop_coupon_log',
    '{{g5_shop_coupon_table}}'=>'g5_shop_coupon', '{{g5_shop_coupon_zone_table}}'=>'g5_shop_coupon_zone',
    '{{g5_shop_default_table}}'=>'g5_shop_default', '{{g5_shop_inicis_log_table}}'=>'g5_shop_inicis_log',
    '{{g5_shop_inicis_pay_event_table}}'=>'g5_shop_inicis_pay_event',
    '{{g5_shop_inicis_pay_table}}'=>'g5_shop_inicis_pay',
    '{{g5_shop_order_cancel_log_table}}'=>'g5_shop_order_cancel_log',
    '{{g5_shop_order_data_table}}'=>'g5_shop_order_data', '{{g5_shop_order_table}}'=>'g5_shop_order',
    '{{g5_shop_post_log_table}}'=>'g5_shop_order_post_log'
);

$install_tables = array();
if (preg_match_all('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/is', $install_sql, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) $install_tables[$match[1]] = $match[2];
}

function g5_schema_normalize_definition($definition)
{
    $definition = preg_replace('/\s+AFTER\s+`?[a-zA-Z0-9_]+`?\s*$/i', '', trim($definition));
    $definition = preg_replace('/\s+FIRST\s*$/i', '', $definition);
    $definition = str_replace('`', '', strtolower($definition));
    return preg_replace('/\s+/', ' ', $definition);
}

function g5_schema_check_objects($file, $table, $sql, $install_tables, &$errors, &$latest_definitions)
{
    if (!isset($install_tables[$table])) {
        $errors[] = $file.': 신규 설치 SQL에 `'.$table.'` 테이블이 없습니다.';
        return;
    }
    $body = $install_tables[$table];
    if (preg_match_all('/\bADD(?:\s+COLUMN)?\s+`([a-zA-Z0-9_]+)`/i', $sql, $columns)) {
        foreach ($columns[1] as $column) {
            if (!preg_match('/`'.preg_quote($column, '/').'`\s+[a-z]/i', $body))
                $errors[] = $file.': `'.$table.'`.`'.$column.'` 컬럼이 신규 설치 SQL에 없습니다.';
        }
    }
    if (preg_match_all('/(?:ADD(?:\s+COLUMN)?|MODIFY(?:\s+COLUMN)?|CHANGE(?:\s+COLUMN)?\s+`[a-zA-Z0-9_]+`)\s+`([a-zA-Z0-9_]+)`\s+(.*?)(?=,\s*(?:ADD|MODIFY|CHANGE|DROP|PRIMARY|UNIQUE|KEY|INDEX)\b|$)/is', $sql, $definitions, PREG_SET_ORDER)) {
        foreach ($definitions as $definition) {
            $latest_definitions[$table][$definition[1]] = g5_schema_normalize_definition($definition[2]);
        }
    }
    if (preg_match_all('/\bADD\s+(?:UNIQUE\s+)?(?:KEY|INDEX)\s+`([^`]+)`/i', $sql, $indexes)) {
        foreach ($indexes[1] as $index) {
            if (!preg_match('/(?:UNIQUE\s+)?KEY\s+`'.preg_quote($index, '/').'`/i', $body))
                $errors[] = $file.': `'.$table.'`.`'.$index.'` 인덱스가 신규 설치 SQL에 없습니다.';
        }
    }
}

$errors = array();
$latest_definitions = array();
foreach ((array) $migration_files as $migration_file) {
    $sql = @file_get_contents($migration_file);
    if ($sql === false) {
        $errors[] = basename($migration_file).': 파일을 읽을 수 없습니다.';
        continue;
    }
    if (preg_match_all('/ALTER\s+TABLE\s+`(\{\{[a-zA-Z0-9_]+\}\})`\s+(.*?);/is', $sql, $alters, PREG_SET_ORDER)) {
        foreach ($alters as $alter) {
            if (!isset($table_map[$alter[1]])) {
                $errors[] = basename($migration_file).': 검사할 수 없는 자리표시자입니다: '.$alter[1];
                continue;
            }
            g5_schema_check_objects(basename($migration_file), $table_map[$alter[1]], $alter[2], $install_tables, $errors, $latest_definitions);
        }
    }
    if (preg_match_all('/CREATE\s+TABLE\s+`(\{\{[a-zA-Z0-9_]+\}\})`\s*\((.*?)\)\s*ENGINE/is', $sql, $creates, PREG_SET_ORDER)) {
        foreach ($creates as $create) {
            if (!isset($table_map[$create[1]]) || !isset($install_tables[$table_map[$create[1]]])) {
                $errors[] = basename($migration_file).': 신규 설치 SQL에서 생성 대상 테이블을 찾을 수 없습니다: '.$create[1];
                continue;
            }
            $table = $table_map[$create[1]];
            if (preg_match_all('/`([a-zA-Z0-9_]+)`\s+[a-z]/i', $create[2], $columns)) {
                foreach ($columns[1] as $column) {
                    if (!preg_match('/`'.preg_quote($column, '/').'`\s+[a-z]/i', $install_tables[$table]))
                        $errors[] = basename($migration_file).': `'.$table.'`.`'.$column.'` 컬럼이 신규 설치 SQL에 없습니다.';
                }
            }
        }
    }
}

foreach ($latest_definitions as $table => $columns) {
    foreach ($columns as $column => $definition) {
        if (!preg_match('/^\s*`'.preg_quote($column, '/').'`\s+(.+?),?\s*$/mi', $install_tables[$table], $match)) {
            continue;
        }
        $install_definition = g5_schema_normalize_definition($match[1]);
        if ($definition !== $install_definition) {
            $errors[] = '`'.$table.'`.`'.$column.'` 최종 정의가 신규 설치 SQL과 다릅니다: '.$definition.' != '.$install_definition;
        }
    }
}

if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, '[실패] '.$error."\n");
    exit(1);
}
echo "마이그레이션의 테이블·컬럼·인덱스와 최종 컬럼 정의가 신규 설치 SQL과 일치합니다.\n";
