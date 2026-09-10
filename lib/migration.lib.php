<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 버전형 DB 마이그레이션 파일을 읽고 실행한다.
 *
 * 마이그레이션 SQL은 G5_PATH/migrations 아래에 두며 파일명 순서대로 실행한다.
 * 일반 웹 요청에서는 이 라이브러리를 불러와도 DDL을 실행하지 않는다.
 */

function g5_migration_table_name()
{
    return G5_TABLE_PREFIX . 'migrations';
}

function g5_migration_path()
{
    return G5_PATH . '/migrations';
}

function g5_migration_replace_placeholders($sql)
{
    global $g5;

    $replacements = array();
    foreach ($g5 as $key => $table) {
        if (substr($key, -6) === '_table' && is_string($table) && $table !== '') {
            $replacements['{{' . $key . '}}'] = $table;
        }
    }
    $shop_prefix = defined('G5_SHOP_TABLE_PREFIX') ? G5_SHOP_TABLE_PREFIX : G5_TABLE_PREFIX . 'shop_';
    $fallbacks = array(
        'member_auto_login_table' => G5_TABLE_PREFIX . 'member_auto_login',
        'g5_shop_cart_table' => $shop_prefix . 'cart',
        'g5_shop_item_table' => $shop_prefix . 'item',
        'g5_shop_order_table' => $shop_prefix . 'order',
        'g5_shop_personalpay_table' => $shop_prefix . 'personalpay',
        'g5_shop_kcp_noti_table' => $shop_prefix . 'kcp_noti',
        'g5_shop_default_table' => $shop_prefix . 'default',
        'g5_shop_coupon_table' => $shop_prefix . 'coupon',
        'g5_shop_coupon_log_table' => $shop_prefix . 'coupon_log',
        'g5_shop_coupon_zone_table' => $shop_prefix . 'coupon_zone',
        'g5_shop_inicis_log_table' => $shop_prefix . 'inicis_log',
        'g5_shop_order_data_table' => $shop_prefix . 'order_data',
        'g5_shop_post_log_table' => $shop_prefix . 'order_post_log',
        'g5_shop_order_cancel_log_table' => $shop_prefix . 'order_cancel_log',
        'g5_shop_inicis_pay_table' => $shop_prefix . 'inicis_pay',
        'g5_shop_inicis_pay_event_table' => $shop_prefix . 'inicis_pay_event',
        'sms5_config_table' => G5_TABLE_PREFIX . 'sms5_config',
        'sms5_write_table' => G5_TABLE_PREFIX . 'sms5_write',
        'sms5_history_table' => G5_TABLE_PREFIX . 'sms5_history',
        'sms5_book_table' => G5_TABLE_PREFIX . 'sms5_book',
        'sms5_book_group_table' => G5_TABLE_PREFIX . 'sms5_book_group',
        'sms5_form_table' => G5_TABLE_PREFIX . 'sms5_form',
        'sms5_form_group_table' => G5_TABLE_PREFIX . 'sms5_form_group'
    );
    foreach ($fallbacks as $key => $table) {
        if (!isset($replacements['{{' . $key . '}}'])) {
            $replacements['{{' . $key . '}}'] = $table;
        }
    }

    return strtr($sql, $replacements);
}

function g5_migration_parse_condition($directive, $file)
{
    if (!preg_match('/^--\s*@(if|skip-if)-(table|column|index)-(exists|missing)\s+(\S+)(?:\s+(\S+))?\s*$/i', $directive, $matches)) {
        return array('error' => '지원하지 않는 실행 조건입니다: ' . basename($file) . ' - ' . trim($directive));
    }

    $condition = array(
        'object' => strtolower($matches[2]),
        'state' => strtolower($matches[3]),
        'table' => g5_migration_replace_placeholders(trim($matches[4], '`')),
        'name' => isset($matches[5]) ? trim($matches[5], '`') : ''
    );
    if ($condition['object'] !== 'table' && $condition['name'] === '') {
        return array('error' => '컬럼 또는 인덱스 이름이 없습니다: ' . basename($file));
    }
    if (strpos($condition['table'], '{{') !== false || strpos($condition['table'], '}}') !== false) {
        return array('error' => '지원하지 않는 조건 테이블 자리표시자가 있습니다: ' . basename($file));
    }

    return $condition;
}

function g5_migration_parse_file($file)
{
    $contents = @file_get_contents($file);
    if ($contents === false) {
        return array('error' => '마이그레이션 파일을 읽을 수 없습니다: ' . basename($file));
    }

    $migration = array(
        'id' => pathinfo($file, PATHINFO_FILENAME),
        'description' => '',
        'skip_table' => '',
        'skip_column' => '',
        'checksum' => hash('sha256', $contents),
        'file' => $file,
        'statements' => array()
    );

    if (!preg_match('/^[0-9]{8}_[0-9]{3}_[a-z0-9_]+$/', $migration['id'])) {
        return array('error' => '마이그레이션 파일명이 규칙에 맞지 않습니다: ' . basename($file));
    }

    if (preg_match('/^--\s*@description\s+(.+)$/mi', $contents, $matches)) {
        $migration['description'] = trim($matches[1]);
    }

    if (preg_match('/^--[\t ]*@skip-if-column[\t ]+(\S+)[\t ]+(\S+)[\t ]*\r?$/mi', $contents, $matches)) {
        $migration['skip_table'] = g5_migration_replace_placeholders(trim($matches[1], '`'));
        $migration['skip_column'] = trim($matches[2], '`');
    }

    $conditions = array();
    $foreach_write_column = '';
    $foreach_write_table = false;
    $foreach_content_seo = false;
    $buffer = '';
    foreach (preg_split('/\r?\n/', $contents) as $line) {
        if (preg_match('/^\s*--\s*@skip-if-column\s+/i', $line)) {
            continue;
        }
        if (preg_match('/^\s*--\s*@foreach-write-table-if-column-missing\s+(\S+)\s*$/i', $line, $matches)) {
            $foreach_write_column = trim($matches[1], '`');
            $foreach_write_table = true;
            continue;
        }
        if (preg_match('/^\s*--\s*@foreach-write-table\s*$/i', $line)) {
            $foreach_write_table = true;
            continue;
        }
        if (preg_match('/^\s*--\s*@foreach-content-missing-seo\s*$/i', $line)) {
            $foreach_content_seo = true;
            continue;
        }
        if (preg_match('/^\s*--\s*@(if|skip-if)-/i', $line)) {
            $parsed_condition = g5_migration_parse_condition(trim($line), $file);
            if (isset($parsed_condition['error'])) {
                return $parsed_condition;
            }
            $conditions[] = $parsed_condition;
            continue;
        }
        if (preg_match('/^\s*--/', $line)) {
            continue;
        }
        $buffer .= $line . "\n";
        while (($position = strpos($buffer, ';')) !== false) {
            $statement = trim(substr($buffer, 0, $position));
            $buffer = substr($buffer, $position + 1);
            if ($statement === '') {
                continue;
            }
            if (!$foreach_write_table && !$foreach_content_seo) {
                $statement = g5_migration_replace_placeholders($statement);
            }
            $allowed_dynamic_placeholder = strpos($statement, '{{write_table}}') !== false || ($foreach_content_seo && strpos($statement, '{{content_table}}') !== false);
            if ((strpos($statement, '{{') !== false || strpos($statement, '}}') !== false) && !$allowed_dynamic_placeholder) {
                return array('error' => '지원하지 않는 테이블 자리표시자가 있습니다: ' . basename($file));
            }
            $migration['statements'][] = array('sql' => $statement, 'conditions' => $conditions, 'foreach_write_column' => $foreach_write_column, 'foreach_write_table' => $foreach_write_table, 'foreach_content_seo' => $foreach_content_seo);
            $conditions = array();
            $foreach_write_column = '';
            $foreach_write_table = false;
            $foreach_content_seo = false;
        }
    }
    if (trim($buffer) !== '') {
        return array('error' => 'SQL 문 끝에 세미콜론이 없습니다: ' . basename($file));
    }

    if (!$migration['statements']) {
        return array('error' => '실행할 SQL이 없습니다: ' . basename($file));
    }

    return $migration;
}

function g5_migration_discover()
{
    $files = glob(g5_migration_path() . '/*.sql');
    $migrations = array();

    if (!$files) {
        return $migrations;
    }

    sort($files, SORT_STRING);
    foreach ($files as $file) {
        $migration = g5_migration_parse_file($file);
        if (isset($migration['error'])) {
            return array($migration);
        }
        $migrations[] = $migration;
    }

    return $migrations;
}

function g5_migration_table_exists($table)
{
    $result = sql_query("SHOW TABLES LIKE '" . sql_real_escape_string($table) . "'", false);
    if (!$result) {
        return false;
    }

    while ($row = sql_fetch_array($result)) {
        if (reset($row) === $table) {
            return true;
        }
    }

    return false;
}

function g5_migration_column_exists($table, $column)
{
    $result = sql_query("SHOW COLUMNS FROM `" . str_replace('`', '``', $table) . "` LIKE '" . sql_real_escape_string($column) . "'", false);
    return $result && sql_num_rows($result) > 0;
}

function g5_migration_index_exists($table, $index)
{
    if (!g5_migration_table_exists($table)) {
        return false;
    }
    $result = sql_query("SHOW INDEX FROM `" . str_replace('`', '``', $table) . "` WHERE Key_name = '" . sql_real_escape_string($index) . "'", false);
    return $result && sql_num_rows($result) > 0;
}

function g5_migration_should_run_statement($conditions)
{
    if (!$conditions) {
        return true;
    }
    foreach ($conditions as $condition) {
        if ($condition['object'] === 'table') {
            $exists = g5_migration_table_exists($condition['table']);
        } elseif ($condition['object'] === 'column') {
            $exists = g5_migration_column_exists($condition['table'], $condition['name']);
        } else {
            $exists = g5_migration_index_exists($condition['table'], $condition['name']);
        }
        if (($condition['state'] === 'exists') !== $exists) {
            return false;
        }
    }
    return true;
}

function g5_migration_execute_statement($statement)
{
    global $g5;

    if ($statement['foreach_content_seo']) {
        if (!function_exists('exist_seo_title_recursive') || !function_exists('generate_seo_title')) {
            return array('error' => 'SEO 제목 생성 함수를 사용할 수 없습니다.', 'executed' => false);
        }
        $result = sql_query("SELECT co_id, co_subject FROM `{$g5['content_table']}` WHERE co_seo_title = ''", false);
        if (!$result) {
            return array('error' => sql_error_info(), 'executed' => false);
        }
        $executed = false;
        while ($content = sql_fetch_array($result)) {
            $seo_title = exist_seo_title_recursive('content', generate_seo_title($content['co_subject']), $g5['content_table'], $content['co_id']);
            $sql = str_replace(
                array('{{content_table}}', '{{content_id}}', '{{seo_title}}'),
                array($g5['content_table'], sql_real_escape_string($content['co_id']), sql_real_escape_string($seo_title)),
                $statement['sql']
            );
            if (!sql_query($sql, false)) {
                return array('error' => $content['co_id'] . ': ' . sql_error_info(), 'executed' => $executed);
            }
            $executed = true;
        }
        return array('error' => '', 'executed' => $executed);
    }

    if (!$statement['foreach_write_table']) {
        return array('error' => sql_query($statement['sql'], false) ? '' : sql_error_info(), 'executed' => true);
    }

    $result = sql_query("SELECT bo_table FROM `{$g5['board_table']}`", false);
    if (!$result) {
        return array('error' => sql_error_info(), 'executed' => false);
    }
    $executed = false;
    while ($board = sql_fetch_array($result)) {
        $write_table = $g5['write_prefix'] . $board['bo_table'];
        if (!g5_migration_table_exists($write_table) || ($statement['foreach_write_column'] !== '' && g5_migration_column_exists($write_table, $statement['foreach_write_column']))) {
            continue;
        }
        $sql = str_replace('{{write_table}}', $write_table, $statement['sql']);
        if (!sql_query($sql, false)) {
            return array('error' => $write_table . ': ' . sql_error_info(), 'executed' => $executed);
        }
        $executed = true;
    }
    return array('error' => '', 'executed' => $executed);
}

function g5_migration_get_records()
{
    $table = g5_migration_table_name();
    $records = array();

    if (!g5_migration_table_exists($table)) {
        return $records;
    }

    $result = sql_query("SELECT migration_id, checksum, status, error_message, applied_at FROM `{$table}` ORDER BY migration_id", false);
    if (!$result) {
        return $records;
    }

    while ($row = sql_fetch_array($result)) {
        $records[$row['migration_id']] = $row;
    }

    return $records;
}

// 검사 실패는 객체 없음과 구분한다. 잘못된 건너뛰기 이력을 만들지 않는다.
function g5_migration_inspect_object($object, $table, $name, &$cache)
{
    $key = $object . ':' . $table;
    if (!array_key_exists($key, $cache)) {
        $quoted = '`' . str_replace('`', '``', $table) . '`';
        if ($object === 'table') {
            $sql = "SHOW TABLES LIKE '" . sql_real_escape_string($table) . "'";
        } elseif ($object === 'column') {
            $sql = 'SHOW COLUMNS FROM ' . $quoted;
        } else {
            $sql = 'SHOW INDEX FROM ' . $quoted;
        }
        $result = sql_query($sql, false);
        $cache[$key] = $result ? array() : null;
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $value = $object === 'table' ? reset($row) : $row[$object === 'column' ? 'Field' : 'Key_name'];
                $cache[$key][$value] = true;
            }
        }
    }
    return $cache[$key] === null ? null : isset($cache[$key][$object === 'table' ? $table : $name]);
}

// SQL을 실행하지 않고, 실행기의 모든 문이 건너뛰어질 때만 true를 반환한다.
function g5_migration_needs_no_execution($migration, &$cache)
{
    global $g5;

    if (isset($migration['error'])) {
        return false;
    }
    if ($migration['skip_table'] && $migration['skip_column'] &&
        g5_migration_inspect_object('column', $migration['skip_table'], $migration['skip_column'], $cache) === true) {
        return true;
    }
    foreach ($migration['statements'] as $statement) {
        $skip = false;
        foreach ($statement['conditions'] as $condition) {
            $exists = g5_migration_inspect_object($condition['object'], $condition['table'], $condition['name'], $cache);
            if ($exists === null) {
                return false;
            }
            if (($condition['state'] === 'exists') !== $exists) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }
        if (!$statement['foreach_write_table']) {
            // 데이터 보정 및 조건 없는 DDL은 완료로 추정하지 않는다.
            return false;
        }
        $result = sql_query("SELECT bo_table FROM `" . str_replace('`', '``', $g5['board_table']) . "`", false);
        if (!$result) {
            return false;
        }
        while ($board = sql_fetch_array($result)) {
            $table = $g5['write_prefix'] . $board['bo_table'];
            if (g5_migration_inspect_object('table', $table, '', $cache) !== true ||
                $statement['foreach_write_column'] === '' ||
                g5_migration_inspect_object('column', $table, $statement['foreach_write_column'], $cache) !== true) {
                return false;
            }
        }
    }
    return true;
}

function g5_migration_status()
{
    $migrations = g5_migration_discover();
    $records = g5_migration_get_records();
    $status = array();
    $inspection_cache = array();

    foreach ($migrations as $migration) {
        if (isset($migration['error'])) {
            $status[] = $migration;
            continue;
        }

        $record = isset($records[$migration['id']]) ? $records[$migration['id']] : null;
        $migration['status'] = $record ? $record['status'] : 'pending';
        if (!$record && g5_migration_needs_no_execution($migration, $inspection_cache)) {
            $migration['status'] = 'unrecorded';
        }
        $migration['checksum_changed'] = $record && $record['status'] === 'success' && $record['checksum'] !== $migration['checksum'];
        $migration['error_message'] = $record ? $record['error_message'] : '';
        $migration['applied_at'] = $record ? $record['applied_at'] : '';
        $status[] = $migration;
    }

    return $status;
}

function g5_migration_status_label($status, $checksum_changed = false)
{
    if ($checksum_changed) {
        return '적용 후 파일 변경됨';
    }

    $labels = array(
        'pending' => '대기',
        'unrecorded' => '실행 불필요 (미기록)',
        'success' => '성공',
        'failed' => '실패'
    );

    return isset($labels[$status]) ? $labels[$status] : '알 수 없음';
}

function g5_migration_validate_target_dependencies($migrations, $records, $target_id)
{
    $target_index = -1;
    foreach ($migrations as $index => $migration) {
        if (isset($migration['error'])) {
            return $migration['error'];
        }
        if ($migration['id'] === $target_id) {
            $target_index = $index;
            break;
        }
    }

    if ($target_index < 0) {
        return '요청한 마이그레이션을 찾을 수 없습니다: ' . $target_id;
    }

    for ($index = 0; $index < $target_index; $index++) {
        $migration = $migrations[$index];
        $record = isset($records[$migration['id']]) ? $records[$migration['id']] : null;
        if (!$record || $record['status'] !== 'success') {
            return $target_id . ' 실행 전에 선행 마이그레이션을 먼저 실행해야 합니다: ' . $migration['id'];
        }
        if ($record['checksum'] !== $migration['checksum']) {
            return '선행 마이그레이션 파일이 적용 후 변경되었습니다: ' . $migration['id'];
        }
    }

    return '';
}

function g5_migration_ensure_table()
{
    $table = g5_migration_table_name();
    $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
                `migration_id` varchar(100) NOT NULL,
                `description` varchar(255) NOT NULL DEFAULT '',
                `checksum` char(64) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `error_message` text NOT NULL,
                `execution_ms` int(11) NOT NULL DEFAULT '0',
                `applied_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`migration_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    return (bool) sql_query($sql, false);
}

function g5_migration_save_record($migration, $status, $error_message, $execution_ms)
{
    $table = g5_migration_table_name();
    $id = sql_real_escape_string($migration['id']);
    $description = sql_real_escape_string($migration['description']);
    $checksum = sql_real_escape_string($migration['checksum']);
    $status = sql_real_escape_string($status);
    $error_message = sql_real_escape_string($error_message);
    $execution_ms = (int) $execution_ms;

    $sql = "INSERT INTO `{$table}`
                SET migration_id = '{$id}', description = '{$description}', checksum = '{$checksum}',
                    status = '{$status}', error_message = '{$error_message}', execution_ms = '{$execution_ms}',
                    applied_at = '" . G5_TIME_YMDHIS . "'
            ON DUPLICATE KEY UPDATE
                description = VALUES(description), checksum = VALUES(checksum), status = VALUES(status),
                error_message = VALUES(error_message), execution_ms = VALUES(execution_ms), applied_at = VALUES(applied_at)";

    return (bool) sql_query($sql, false);
}

function g5_migration_run($target_id = '', $record_existing = false)
{
    $result = array('success' => false, 'applied' => 0, 'skipped' => 0, 'errors' => array());

    if (!g5_migration_ensure_table()) {
        $result['errors'][] = '마이그레이션 이력 테이블을 생성하지 못했습니다: ' . sql_error_info();
        return $result;
    }

    $lock_name = 'g5_migration_' . substr(md5(G5_TABLE_PREFIX), 0, 16);
    $lock = sql_fetch("SELECT GET_LOCK('{$lock_name}', 10) AS acquired", false);
    if (!$lock || (int) $lock['acquired'] !== 1) {
        $result['errors'][] = '다른 DB 업그레이드가 실행 중이거나 실행 잠금을 얻지 못했습니다.';
        return $result;
    }

    $migrations = g5_migration_discover();
    $records = g5_migration_get_records();
    $target_found = $target_id === '';

    if ($record_existing) {
        $history = sql_fetch('SELECT COUNT(*) AS total FROM `' . g5_migration_table_name() . '`', false);
        if (!$history || !isset($history['total']) || (int) $history['total'] !== count($records)) {
            $result['errors'][] = '기존 마이그레이션 이력을 확인하지 못했습니다.';
            sql_query("SELECT RELEASE_LOCK('{$lock_name}')", false);
            return $result;
        }
    }

    if ($target_id !== '') {
        $dependency_error = g5_migration_validate_target_dependencies($migrations, $records, $target_id);
        if ($dependency_error !== '') {
            $result['errors'][] = $dependency_error;
            sql_query("SELECT RELEASE_LOCK('{$lock_name}')", false);
            return $result;
        }
    }

    foreach ($migrations as $migration) {
        if (isset($migration['error'])) {
            $result['errors'][] = $migration['error'];
            break;
        }

        if ($target_id !== '' && $migration['id'] !== $target_id) {
            continue;
        }
        $target_found = true;

        $record = isset($records[$migration['id']]) ? $records[$migration['id']] : null;
        if ($record && $record['status'] === 'success') {
            if ($record['checksum'] !== $migration['checksum']) {
                $result['errors'][] = $migration['id'] . ' 파일이 적용 후 변경되었습니다.';
                break;
            }
            continue;
        }

        $started_at = get_microtime();
        if ($record_existing) {
            $inspection_cache = array();
            // 앞선 변경이 후속 실행 조건을 바꿀 수 있으므로 연속된 항목만 등록한다.
            if ($record || !g5_migration_needs_no_execution($migration, $inspection_cache)) {
                break;
            }
            if (!g5_migration_save_record($migration, 'success', '', 0)) {
                $result['errors'][] = $migration['id'] . ' 기존 상태 확인 이력을 저장하지 못했습니다: ' . sql_error_info();
                break;
            }
            $result['skipped']++;
            continue;
        }
        $skip = $migration['skip_table'] && $migration['skip_column'] && g5_migration_column_exists($migration['skip_table'], $migration['skip_column']);
        $migration_error = '';
        $executed = false;

        if (!$skip) {
            foreach ($migration['statements'] as $statement) {
                if (!g5_migration_should_run_statement($statement['conditions'])) {
                    continue;
                }
                $statement_result = g5_migration_execute_statement($statement);
                $executed = $executed || $statement_result['executed'];
                if ($statement_result['error'] !== '') {
                    $migration_error = $statement_result['error'];
                    break;
                }
            }
        }

        $execution_ms = (int) round((get_microtime() - $started_at) * 1000);
        if ($migration_error !== '') {
            g5_migration_save_record($migration, 'failed', $migration_error, $execution_ms);
            $result['errors'][] = $migration['id'] . ': ' . $migration_error;
            break;
        }

        if (!g5_migration_save_record($migration, 'success', '', $execution_ms)) {
            $result['errors'][] = $migration['id'] . ' 실행 이력을 저장하지 못했습니다: ' . sql_error_info();
            break;
        }

        if ($skip || !$executed) {
            $result['skipped']++;
        } else {
            $result['applied']++;
        }
    }


    if (!$target_found) {
        $result['errors'][] = '요청한 마이그레이션을 찾을 수 없습니다: ' . $target_id;
    }

    sql_query("SELECT RELEASE_LOCK('{$lock_name}')", false);
    $result['success'] = !$result['errors'];

    return $result;
}
