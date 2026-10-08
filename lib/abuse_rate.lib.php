<?php
if (!defined('_GNUBOARD_')) exit;

function g5_abuse_rate_table()
{
    global $g5;
    return isset($g5['abuse_rate_table']) ? $g5['abuse_rate_table'] : G5_TABLE_PREFIX.'abuse_rate';
}

function g5_abuse_rate_error()
{
    error_log('[g5 abuse rate] Invalid policy or unavailable storage; check database upgrade and permissions.');
    return null;
}

// 업무 쿼리의 sql_query_after 훅·트랜잭션과 분리한 비영속 연결을 사용한다.
function g5_abuse_rate_connection()
{
    static $connection = null;
    if ($connection !== null) return $connection;
    $mysqli = function_exists('mysqli_connect') && G5_MYSQLI_USE;
    $host = G5_MYSQL_HOST;
    if (substr($host, 0, 2) === 'p:') $host = substr($host, 2);
    try {
        if ($mysqli) {
            $link = mysqli_init();
            mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            mysqli_options($link, MYSQLI_SET_CHARSET_NAME, 'utf8');
            if (!@mysqli_real_connect($link, $host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB)) $link = false;
        } else {
            $link = @mysql_connect($host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, true);
            if ($link && !@mysql_select_db(G5_MYSQL_DB, $link)) {
                mysql_close($link);
                $link = false;
            }
        }
    } catch (Exception $e) {
        $link = false;
    }
    $connection = $link ? array('link'=>$link, 'mysqli'=>$mysqli) : false;
    if ($connection) {
        // 구버전 MySQL에서 지원하지 않는 세션 변수는 강제하지 않는다.
        if (!g5_abuse_rate_query($connection, 'SET SESSION innodb_lock_wait_timeout = 2, SESSION lock_wait_timeout = 2')) {
            g5_abuse_rate_query($connection, 'SET SESSION innodb_lock_wait_timeout = 2');
        }
        if (!$mysqli) mysql_set_charset('utf8', $link);
    }
    return $connection;
}

function g5_abuse_rate_query($connection, $sql)
{
    try {
        return $connection['mysqli'] ? @mysqli_query($connection['link'], $sql) : @mysql_query($sql, $connection['link']);
    } catch (Exception $e) {
        return false;
    }
}

function g5_abuse_rate_row($connection, $result)
{
    return $connection['mysqli'] ? mysqli_fetch_assoc($result) : mysql_fetch_assoc($result);
}

// 관리자 화면과 실제 처리에서 같은 필수 구조를 검증한다. DDL이나 시험용 쓰기는 하지 않는다.
function g5_abuse_rate_storage_ready()
{
    $connection = g5_abuse_rate_connection();
    if (!$connection) return false;
    $table = g5_abuse_rate_table();
    if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) return false;
    if (!g5_abuse_rate_query($connection, "SELECT ar_key, ar_next FROM `{$table}` LIMIT 0")) return false;
    // 테이블 접근으로 잠금을 확보한 뒤 메타데이터를 한 번에 읽는다.
    // 요청 사이 또는 두 트랜잭션 사이의 구조 변경을 캐시로 놓치지 않는다.
    $result = g5_abuse_rate_query($connection, "SELECT t.ENGINE, k.COLUMN_TYPE AS key_type, k.IS_NULLABLE AS key_null,
        n.COLUMN_TYPE AS next_type, n.IS_NULLABLE AS next_null, p.COLUMN_NAME AS primary_column, p.SUB_PART,
        (SELECT COUNT(*) FROM information_schema.STATISTICS s WHERE s.TABLE_SCHEMA = DATABASE() AND s.TABLE_NAME = '{$table}' AND s.INDEX_NAME = 'PRIMARY') AS primary_parts
        FROM information_schema.TABLES t
        JOIN information_schema.COLUMNS k ON k.TABLE_SCHEMA = t.TABLE_SCHEMA AND k.TABLE_NAME = t.TABLE_NAME AND k.COLUMN_NAME = 'ar_key'
        JOIN information_schema.COLUMNS n ON n.TABLE_SCHEMA = t.TABLE_SCHEMA AND n.TABLE_NAME = t.TABLE_NAME AND n.COLUMN_NAME = 'ar_next'
        JOIN information_schema.STATISTICS p ON p.TABLE_SCHEMA = t.TABLE_SCHEMA AND p.TABLE_NAME = t.TABLE_NAME AND p.INDEX_NAME = 'PRIMARY' AND p.SEQ_IN_INDEX = 1
        WHERE t.TABLE_SCHEMA = DATABASE() AND t.TABLE_NAME = '{$table}'");
    $row = $result ? g5_abuse_rate_row($connection, $result) : false;
    return $row && strtolower($row['ENGINE']) === 'innodb' && strtolower($row['key_type']) === 'char(64)' &&
        preg_match('/\Abigint(?:\([0-9]+\))? unsigned\z/i', $row['next_type']) &&
        $row['key_null'] === 'NO' && $row['next_null'] === 'NO' &&
        $row['primary_column'] === 'ar_key' && $row['SUB_PART'] === null && (int)$row['primary_parts'] === 1;
}

function g5_abuse_rate_affected_rows($connection)
{
    return $connection['mysqli'] ? mysqli_affected_rows($connection['link']) : mysql_affected_rows($connection['link']);
}

// true: 전체 한도 확보, false: 한도 초과, null: 저장소·설정 오류.
// 여러 한도는 같은 트랜잭션에서 확보하고 하나라도 실패하면 모두 되돌린다.
function g5_abuse_rate_allow_many($limits, &$reservation = null)
{
    $reservation = array();
    $connection = g5_abuse_rate_connection();
    if (!$connection || !is_array($limits) || !$limits) return g5_abuse_rate_error();
    $secret = defined('G5_TOKEN_ENCRYPTION_KEY') && G5_TOKEN_ENCRYPTION_KEY
        ? G5_TOKEN_ENCRYPTION_KEY : G5_MYSQL_PASSWORD;
    $buckets = array();
    foreach ($limits as $limit) {
        if (!isset($limit['scope'], $limit['identity'], $limit['interval'], $limit['burst']) ||
            !is_scalar($limit['interval']) || !is_scalar($limit['burst'])) return g5_abuse_rate_error();
        $interval = (int)$limit['interval'];
        $burst = (int)$limit['burst'];
        if ($interval < 1 || $interval > 86400 || $burst < 1 || $burst > 10000) return g5_abuse_rate_error();
        $key = hash_hmac('sha256', $limit['scope']."\0".$limit['identity'], $secret);
        if (isset($buckets[$key])) return g5_abuse_rate_error();
        $buckets[$key] = array('scope'=>$limit['scope'], 'interval'=>$interval, 'tolerance'=>$interval * ($burst - 1));
    }
    // 항상 같은 순서로 행을 잠가 동시 요청의 잠금 순서 역전을 방지한다.
    ksort($buckets);
    if (!g5_abuse_rate_query($connection, 'START TRANSACTION')) return g5_abuse_rate_error();
    $allowed = null;
    do {
        // 트랜잭션 안에서 구조를 확인하고 준비되지 않은 테이블에서는 한도를 소비하지 않는다.
        if (!g5_abuse_rate_storage_ready()) break;
        $table = g5_abuse_rate_table();
        $result = g5_abuse_rate_query($connection, 'SELECT UNIX_TIMESTAMP() AS now');
        $row = $result ? g5_abuse_rate_row($connection, $result) : false;
        if (!$row) break;
        $now = (int)$row['now'];
        foreach ($buckets as $key=>$bucket) {
            // 중복 행에도 처음부터 배타 잠금을 잡아 INSERT IGNORE의 공유 잠금 승격 교착을 피한다.
            if (!g5_abuse_rate_query($connection, "INSERT INTO `{$table}` (ar_key, ar_next) VALUES ('{$key}', 0) ON DUPLICATE KEY UPDATE ar_key = ar_key")) break 2;
            $ceiling = $now + $bucket['tolerance'];
            $interval = $bucket['interval'];
            // 판정과 갱신을 한 SQL로 처리한다. 전용 연결이므로 감사 훅이 결과를 바꾸지 못한다.
            if (!g5_abuse_rate_query($connection, "UPDATE `{$table}` SET ar_next = GREATEST(ar_next, {$now}) + {$interval} WHERE ar_key = '{$key}' AND ar_next <= {$ceiling}")) break 2;
            if (g5_abuse_rate_affected_rows($connection) !== 1) {
                $allowed = false;
                break 2;
            }
            // 수신자 burst=1이면 이번 예약의 시각이 유일하게 정해진다.
            if ($bucket['scope'] === 'password_lost_recipient' && $bucket['tolerance'] === 0) {
                $reservation = array('key'=>$key, 'next'=>$now + $interval);
            }
        }
        if (!g5_abuse_rate_query($connection, 'COMMIT')) break;
        $allowed = true;
    } while (false);
    if ($allowed !== true) {
        g5_abuse_rate_query($connection, 'ROLLBACK');
        $reservation = array();
    }
    if ($allowed === null) error_log('[g5 abuse rate] Storage unavailable or invalid; check database upgrade and permissions.');
    if ($allowed === true && mt_rand(1, 100) === 1) {
        g5_abuse_rate_query($connection, "DELETE FROM `{$table}` WHERE ar_next < UNIX_TIMESTAMP() - 86400 LIMIT 1000");
    }
    return $allowed;
}

function g5_abuse_rate_allow($scope, $identity, $interval, $burst)
{
    return g5_abuse_rate_allow_many(array(array('scope'=>$scope, 'identity'=>$identity, 'interval'=>$interval, 'burst'=>$burst)));
}

function g5_password_lost_rate_defaults()
{
    return array(
        'ip' => array('interval' => 6, 'burst' => 10),
        'recipient' => array('interval' => 300, 'burst' => 1),
        'global' => array('interval' => 1, 'burst' => 30)
    );
}

function g5_password_lost_policy_valid($policies)
{
    if (!is_array($policies)) return false;
    foreach (g5_password_lost_rate_defaults() as $kind=>$default) {
        foreach (array('interval'=>86400, 'burst'=>10000) as $field=>$max) {
            if (!isset($policies[$kind][$field]) || !is_scalar($policies[$kind][$field]) ||
                !preg_match('/\A[0-9]+\z/', (string)$policies[$kind][$field]) ||
                $policies[$kind][$field] < 1 || $policies[$kind][$field] > $max) return false;
        }
    }
    return true;
}

function g5_password_lost_rate_policy()
{
    global $config;
    $policies = g5_password_lost_rate_defaults();
    if (isset($config['cf_password_lost_policy']) && $config['cf_password_lost_policy'] !== '') {
        $policies = json_decode($config['cf_password_lost_policy'], true);
    }
    return g5_password_lost_policy_valid($policies) ? $policies : false;
}

function g5_password_lost_rate_limits($identities, &$reservation = null)
{
    $policies = run_replace('password_lost_rate_policy', g5_password_lost_rate_policy());
    if (!g5_password_lost_policy_valid($policies)) return g5_abuse_rate_error();
    $limits = array();
    foreach ($identities as $kind=>$identity) {
        if (!is_array($policies) || !isset($policies[$kind]['interval'], $policies[$kind]['burst'])) return g5_abuse_rate_error();
        $limits[] = array('scope'=>'password_lost_'.$kind, 'identity'=>$identity,
            'interval'=>$policies[$kind]['interval'], 'burst'=>$policies[$kind]['burst']);
    }
    return g5_abuse_rate_allow_many($limits, $reservation);
}

function g5_password_lost_rate_allow($kind, $identity)
{
    return g5_password_lost_rate_limits(array($kind=>$identity));
}

function g5_password_lost_mail_allow($email, &$reservation = null)
{
    return g5_password_lost_rate_limits(array('recipient'=>strtolower($email), 'global'=>'all'), $reservation);
}

// 실패한 요청 자신이 확보한 수신자 예약만 줄인다. IP·전체 시도 한도는 환급하지 않는다.
function g5_password_lost_mail_failed($reservation)
{
    if (empty($reservation['key']) || !preg_match('/\A[a-f0-9]{64}\z/', $reservation['key']) || empty($reservation['next'])) return;
    $connection = g5_abuse_rate_connection();
    $table = g5_abuse_rate_table();
    if (!$connection || !preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) return;
    $next = (int)$reservation['next'];
    if (!g5_abuse_rate_query($connection, "UPDATE `{$table}` SET ar_next = LEAST(ar_next, UNIX_TIMESTAMP() + 60) WHERE ar_key = '{$reservation['key']}' AND ar_next = {$next}")) g5_abuse_rate_error();
}

// 잘못된 링크·동시 요청·메일 실패 복원이 다른 요청의 인증값을 덮어쓰지 않게 한다.
function g5_password_lost_compare_update($member_no, $expected, $replacement, $password = null)
{
    global $g5;
    $connection = g5_abuse_rate_connection();
    $table = $g5['member_table'];
    if (!$connection || !preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) return false;
    $values = array($expected, $replacement);
    if ($password !== null) $values[] = $password;
    foreach ($values as $i=>$value) {
        $values[$i] = $connection['mysqli'] ? mysqli_real_escape_string($connection['link'], $value) : mysql_real_escape_string($value, $connection['link']);
    }
    $member_no = (int)$member_no;
    $set_password = $password === null ? '' : ", mb_password = '{$values[2]}'";
    return g5_abuse_rate_query($connection, "UPDATE `{$table}` SET mb_lost_certify = '{$values[1]}'{$set_password} WHERE mb_no = {$member_no} AND BINARY mb_lost_certify = '{$values[0]}'") && g5_abuse_rate_affected_rows($connection) === 1;
}
