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

// 별도 연결 실패 원인(동시 접속 수 제한 등)을 진단 화면에 알리기 위해 오류 번호만 보관한다.
function g5_abuse_rate_connect_errno($errno = null)
{
    static $last = 0;
    if ($errno !== null) $last = (int)$errno;
    return $last;
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
            if (!@mysqli_real_connect($link, $host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB)) {
                g5_abuse_rate_connect_errno(mysqli_connect_errno());
                $link = false;
            }
        } else {
            $link = @mysql_connect($host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, true);
            if (!$link) g5_abuse_rate_connect_errno(mysql_errno());
            if ($link && !@mysql_select_db(G5_MYSQL_DB, $link)) {
                g5_abuse_rate_connect_errno(mysql_errno($link));
                mysql_close($link);
                $link = false;
            }
        }
    } catch (Exception $e) {
        g5_abuse_rate_connect_errno($e->getCode());
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

function g5_abuse_rate_errno($connection)
{
    return $connection['mysqli'] ? mysqli_errno($connection['link']) : mysql_errno($connection['link']);
}

// 관리자 화면과 실제 처리에서 같은 필수 구조를 검증한다. DDL이나 시험용 쓰기는 하지 않는다.
// mode는 transaction(InnoDB 트랜잭션) 또는 lock(비트랜잭션 엔진을 이름 잠금으로 직렬화)이다.
// InnoDB를 쓸 수 없는 DB에서는 InnoDB 지정이 기본 엔진(MyISAM·Aria)으로 대체되므로 두 엔진도 허용한다.
// reason: connection, table_name, table, metadata, structure, engine
function g5_abuse_rate_storage_state()
{
    $state = array('ready'=>false, 'mode'=>'', 'engine'=>'', 'reason'=>'', 'errno'=>0, 'row'=>array());
    $connection = g5_abuse_rate_connection();
    if (!$connection) {
        $state['reason'] = 'connection';
        $state['errno'] = g5_abuse_rate_connect_errno();
        return $state;
    }
    $table = g5_abuse_rate_table();
    if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) {
        $state['reason'] = 'table_name';
        return $state;
    }
    if (!g5_abuse_rate_query($connection, "SELECT ar_key, ar_next FROM `{$table}` LIMIT 0")) {
        $state['reason'] = 'table';
        $state['errno'] = g5_abuse_rate_errno($connection);
        return $state;
    }
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
    if (!$result) {
        $state['reason'] = 'metadata';
        $state['errno'] = g5_abuse_rate_errno($connection);
        return $state;
    }
    $row = g5_abuse_rate_row($connection, $result);
    if (!$row) {
        // 컬럼이나 기본키가 없으면 결합 결과가 비어 있다.
        // 진단 화면에 표시할 수 있도록 저장 엔진만 따로 읽는다(실패 경로에서만 조회한다).
        $state['reason'] = 'structure';
        $result = g5_abuse_rate_query($connection, "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'");
        $row = $result ? g5_abuse_rate_row($connection, $result) : false;
        if ($row && $row['ENGINE'] !== null) $state['engine'] = (string)$row['ENGINE'];
        return $state;
    }
    $state['row'] = $row;
    $state['engine'] = (string)$row['ENGINE'];
    if (!(strtolower($row['key_type']) === 'char(64)' &&
        preg_match('/\Abigint(?:\([0-9]+\))? unsigned\z/i', $row['next_type']) &&
        $row['key_null'] === 'NO' && $row['next_null'] === 'NO' &&
        $row['primary_column'] === 'ar_key' && $row['SUB_PART'] === null && (int)$row['primary_parts'] === 1)) {
        $state['reason'] = 'structure';
        return $state;
    }
    $engine = strtolower($state['engine']);
    if ($engine === 'innodb') {
        $state['mode'] = 'transaction';
    } elseif ($engine === 'myisam' || $engine === 'aria') {
        $state['mode'] = 'lock';
    } else {
        $state['reason'] = 'engine';
        return $state;
    }
    $state['ready'] = true;
    return $state;
}

function g5_abuse_rate_storage_ready()
{
    $state = g5_abuse_rate_storage_state();
    return $state['ready'];
}

// DB 업그레이드 화면용 상세 진단. 조회와 잠금 시험만 하며 DDL이나 테이블 쓰기는 하지 않는다.
function g5_abuse_rate_storage_diagnose()
{
    $state = g5_abuse_rate_storage_state();
    // InnoDB 지원: YES, DEFAULT, NO, DISABLED, 확인할 수 없으면 빈 문자열
    $state['innodb'] = '';
    // 이름 잠금 사용 가능 여부: true, false, 확인하지 않았으면 null
    $state['lock'] = null;
    $connection = g5_abuse_rate_connection();
    if (!$connection) return $state;
    $result = g5_abuse_rate_query($connection, "SELECT SUPPORT FROM information_schema.ENGINES WHERE ENGINE = 'InnoDB'");
    if ($result) {
        $row = g5_abuse_rate_row($connection, $result);
        $state['innodb'] = $row ? strtoupper($row['SUPPORT']) : 'NO';
    } else {
        // information_schema.ENGINES가 없는 구버전은 SHOW ENGINES로 확인한다.
        $result = g5_abuse_rate_query($connection, 'SHOW ENGINES');
        if ($result) {
            $state['innodb'] = 'NO';
            while ($row = g5_abuse_rate_row($connection, $result)) {
                if (isset($row['Engine'], $row['Support']) && strtolower($row['Engine']) === 'innodb') $state['innodb'] = strtoupper($row['Support']);
            }
        }
    }
    if ($state['mode'] === 'lock') {
        // 실제 처리와 다른 이름으로 시험해 처리 중인 요청을 막지 않는다.
        $probe = g5_abuse_rate_lock_name(g5_abuse_rate_table().'_probe');
        $result = g5_abuse_rate_query($connection, "SELECT GET_LOCK('{$probe}', 0) AS acquired");
        $row = $result ? g5_abuse_rate_row($connection, $result) : false;
        $state['lock'] = $row && (string)$row['acquired'] === '1';
        if ($state['lock']) g5_abuse_rate_query($connection, "DO RELEASE_LOCK('{$probe}')");
    }
    return $state;
}

// 진단 결과를 관리자 화면 문구로 바꾼다. DB 오류 원문(계정·호스트 포함 가능)은 쓰지 않고 오류 번호만 쓴다.
function g5_abuse_rate_storage_messages($state)
{
    $table = g5_abuse_rate_table();
    $errno = isset($state['errno']) ? (int)$state['errno'] : 0;
    $code = $errno ? ' (오류 번호 '.$errno.')' : '';
    $reason = '';
    $action = '';
    if (!empty($state['ready'])) {
        if ($state['mode'] === 'transaction') {
            $reason = 'InnoDB 트랜잭션 방식으로 동작합니다.';
        } else {
            $reason = $state['engine'].' 엔진에서 이름 잠금(GET_LOCK) 방식으로 동작합니다. 비밀번호 찾기는 정상적으로 이용할 수 있습니다.';
            if (isset($state['innodb']) && ($state['innodb'] === 'NO' || $state['innodb'] === 'DISABLED')) {
                $action = 'DB 서버가 InnoDB를 지원하지 않아 테이블이 '.$state['engine'].'(으)로 만들어졌습니다. 따로 조치할 필요는 없습니다.';
            } elseif (isset($state['innodb']) && ($state['innodb'] === 'YES' || $state['innodb'] === 'DEFAULT')) {
                $action = 'InnoDB를 사용할 수 있는 서버입니다. 원하면 ALTER TABLE `'.$table.'` ENGINE=InnoDB 로 전환할 수 있습니다.';
            }
            if (isset($state['lock']) && $state['lock'] === false) {
                $reason = $state['engine'].' 엔진은 이름 잠금(GET_LOCK)이 필요하지만 지금 잠금을 얻지 못했습니다. 비밀번호 찾기 요청이 거부될 수 있습니다.';
                $action = '잠시 후 다시 확인해 주세요. 계속되면 DB 서버나 DB 프록시에서 GET_LOCK 사용이 제한되는지 호스팅사에 확인해 주세요.';
            }
        }
        return array('reason'=>$reason, 'action'=>$action);
    }
    switch (isset($state['reason']) ? $state['reason'] : '') {
        case 'connection':
            if ($errno === 1040) $reason = 'DB 서버의 전체 동시 접속 수가 한도에 도달해 저장소용 연결을 열지 못했습니다.';
            elseif ($errno === 1203) $reason = 'DB 계정의 동시 접속 수 제한(max_user_connections) 때문에 저장소용 연결을 열지 못했습니다.';
            else $reason = '저장소용 DB 연결을 열지 못했습니다'.$code.'.';
            $action = '이 기능은 DB 연결을 하나 더 사용합니다. 호스팅사에 계정당 DB 동시 접속 수 제한을 확인해 주세요.';
            break;
        case 'table_name':
            $reason = '저장소 테이블 이름이 올바르지 않습니다.';
            $action = '테이블 접두사 설정을 확인해 주세요.';
            break;
        case 'table':
            if ($errno === 1146) {
                $reason = '저장소 테이블('.$table.')이 없습니다.';
                $action = 'DB 업그레이드에서 대기 중인 변경을 적용해 주세요.';
            } elseif ($errno === 1142) {
                $reason = 'DB 계정에 저장소 테이블('.$table.') 조회 권한이 없습니다.';
                $action = 'DB 계정 권한을 확인해 주세요.';
            } else {
                $reason = '저장소 테이블('.$table.')을 조회하지 못했습니다'.$code.'.';
                $action = 'DB 업그레이드에서 대기 중인 변경을 적용했는지와 DB 계정 권한을 확인해 주세요.';
            }
            break;
        case 'metadata':
            $reason = 'information_schema에서 저장소 테이블 구조를 읽지 못했습니다'.$code.'.';
            $action = 'DB 계정 권한을 확인해 주세요.';
            break;
        case 'structure':
            $reason = '저장소 테이블('.$table.')의 구조가 필요한 형식과 다릅니다.';
            $action = '필요한 구조: ar_key char(64) NOT NULL 기본키, ar_next bigint unsigned NOT NULL.';
            break;
        case 'engine':
            $reason = '저장소 테이블('.$table.')의 저장 엔진('.$state['engine'].')은 지원하지 않습니다.';
            $action = 'InnoDB 또는 MyISAM으로 변경해 주세요.';
            break;
        default:
            $reason = '저장소 상태를 확인하지 못했습니다.';
            $action = '서버의 PHP 오류 로그에서 [g5 abuse rate] 기록을 확인해 주세요.';
    }
    return array('reason'=>$reason, 'action'=>$action);
}

function g5_abuse_rate_affected_rows($connection)
{
    return $connection['mysqli'] ? mysqli_affected_rows($connection['link']) : mysql_affected_rows($connection['link']);
}

// DB와 테이블마다 다른 이름 잠금을 쓴다. 이름은 64자를 넘지 않는다.
function g5_abuse_rate_lock_name($table)
{
    return 'g5_abuse_rate_'.substr(md5(G5_MYSQL_DB."\0".$table), 0, 32);
}

function g5_abuse_rate_next($connection, $table, $key)
{
    $result = g5_abuse_rate_query($connection, "SELECT ar_next FROM `{$table}` WHERE ar_key = '{$key}'");
    $row = $result ? g5_abuse_rate_row($connection, $result) : false;
    return $row && preg_match('/\A[0-9]+\z/', (string)$row['ar_next']) ? (string)$row['ar_next'] : false;
}

// true: 전체 한도 확보, false: 한도 초과, null: 저장소·설정 오류.
// 여러 한도는 함께 확보하고 하나라도 실패하면 모두 되돌린다.
// InnoDB는 한 트랜잭션으로, MyISAM·Aria는 이름 잠금과 확보 직전 값 복원으로 처리한다.
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
    $table = g5_abuse_rate_table();
    if (!g5_abuse_rate_query($connection, 'START TRANSACTION')) return g5_abuse_rate_error();
    // 트랜잭션 안에서 구조를 확인하고 준비되지 않은 테이블에서는 한도를 소비하지 않는다.
    $state = g5_abuse_rate_storage_state();
    if ($state['ready'] && $state['mode'] === 'lock') {
        g5_abuse_rate_query($connection, 'ROLLBACK');
        $allowed = g5_abuse_rate_allow_locked($connection, $table, $buckets, $reservation);
    } else {
        $allowed = $state['ready'] ? g5_abuse_rate_allow_transaction($connection, $table, $buckets, $reservation) : null;
        if ($allowed !== true) {
            g5_abuse_rate_query($connection, 'ROLLBACK');
            $reservation = array();
        }
        if ($allowed === true && mt_rand(1, 100) === 1) {
            g5_abuse_rate_query($connection, "DELETE FROM `{$table}` WHERE ar_next < UNIX_TIMESTAMP() - 86400 LIMIT 1000");
        }
    }
    if ($allowed === null) error_log('[g5 abuse rate] Storage unavailable or invalid; check database upgrade and permissions.');
    return $allowed;
}

function g5_abuse_rate_now($connection)
{
    $result = g5_abuse_rate_query($connection, 'SELECT UNIX_TIMESTAMP() AS now');
    $row = $result ? g5_abuse_rate_row($connection, $result) : false;
    return $row ? (int)$row['now'] : false;
}

// InnoDB: 호출한 쪽이 연 트랜잭션 안에서 행 잠금으로 모든 한도를 확보하고 COMMIT 한다.
function g5_abuse_rate_allow_transaction($connection, $table, $buckets, &$reservation)
{
    $now = g5_abuse_rate_now($connection);
    if ($now === false) return null;
    foreach ($buckets as $key=>$bucket) {
        // 중복 행에도 처음부터 배타 잠금을 잡아 INSERT IGNORE의 공유 잠금 승격 교착을 피한다.
        if (!g5_abuse_rate_query($connection, "INSERT INTO `{$table}` (ar_key, ar_next) VALUES ('{$key}', 0) ON DUPLICATE KEY UPDATE ar_key = ar_key")) return null;
        $ceiling = $now + $bucket['tolerance'];
        $interval = $bucket['interval'];
        // 판정과 갱신을 한 SQL로 처리한다. 전용 연결이므로 감사 훅이 결과를 바꾸지 못한다.
        if (!g5_abuse_rate_query($connection, "UPDATE `{$table}` SET ar_next = GREATEST(ar_next, {$now}) + {$interval} WHERE ar_key = '{$key}' AND ar_next <= {$ceiling}")) return null;
        if (g5_abuse_rate_affected_rows($connection) !== 1) return false;
        // 수신자 burst=1이면 이번 예약의 시각이 유일하게 정해진다.
        if ($bucket['scope'] === 'password_lost_recipient' && $bucket['tolerance'] === 0) {
            $reservation = array('key'=>$key, 'next'=>$now + $interval);
        }
    }
    return g5_abuse_rate_query($connection, 'COMMIT') ? true : null;
}

// MyISAM·Aria: 트랜잭션 대신 이름 잠금으로 한도 확보를 직렬화한다.
// 하나라도 확보하지 못하면 앞서 확보한 한도를 확보 직전 값으로 되돌린다.
// 되돌리기 전에 요청이 끊기면 한도가 더 엄격하게 남을 뿐 느슨해지지 않는다.
function g5_abuse_rate_allow_locked($connection, $table, $buckets, &$reservation)
{
    $reservation = array();
    $lock = g5_abuse_rate_lock_name($table);
    $result = g5_abuse_rate_query($connection, "SELECT GET_LOCK('{$lock}', 2) AS acquired");
    $row = $result ? g5_abuse_rate_row($connection, $result) : false;
    if (!$row || (string)$row['acquired'] !== '1') return null;
    $allowed = null;
    $taken = array();
    do {
        // 잠금을 얻은 뒤 구조를 다시 확인한다. 그 사이 엔진이 바뀌었으면 이번 요청은 오류로 처리한다.
        $state = g5_abuse_rate_storage_state();
        if (!$state['ready'] || $state['mode'] !== 'lock') break;
        $now = g5_abuse_rate_now($connection);
        if ($now === false) break;
        foreach ($buckets as $key=>$bucket) {
            if (!g5_abuse_rate_query($connection, "INSERT INTO `{$table}` (ar_key, ar_next) VALUES ('{$key}', 0) ON DUPLICATE KEY UPDATE ar_key = ar_key")) break 2;
            $before = g5_abuse_rate_next($connection, $table, $key);
            if ($before === false) break 2;
            $ceiling = $now + $bucket['tolerance'];
            $interval = $bucket['interval'];
            if (!g5_abuse_rate_query($connection, "UPDATE `{$table}` SET ar_next = GREATEST(ar_next, {$now}) + {$interval} WHERE ar_key = '{$key}' AND ar_next <= {$ceiling}")) break 2;
            if (g5_abuse_rate_affected_rows($connection) !== 1) {
                $allowed = false;
                break 2;
            }
            $after = g5_abuse_rate_next($connection, $table, $key);
            if ($after === false) break 2;
            $taken[] = array('key'=>$key, 'before'=>$before, 'after'=>$after);
            if ($bucket['scope'] === 'password_lost_recipient' && $bucket['tolerance'] === 0) {
                $reservation = array('key'=>$key, 'next'=>$now + $interval);
            }
        }
        $allowed = true;
    } while (false);
    if ($allowed !== true) {
        // 그 사이 다른 처리(메일 실패 환급 등)가 값을 바꿨으면 덮어쓰지 않는다.
        foreach (array_reverse($taken) as $item) {
            if (!g5_abuse_rate_query($connection, "UPDATE `{$table}` SET ar_next = {$item['before']} WHERE ar_key = '{$item['key']}' AND ar_next = {$item['after']}")) {
                error_log('[g5 abuse rate] Failed to restore a reserved limit; the limit stays stricter until it expires.');
            }
        }
        $reservation = array();
    } elseif (mt_rand(1, 100) === 1) {
        // 잠금 안에서 정리해 다른 요청이 확보 중인 행을 지우지 않는다.
        g5_abuse_rate_query($connection, "DELETE FROM `{$table}` WHERE ar_next < UNIX_TIMESTAMP() - 86400 LIMIT 1000");
    }
    g5_abuse_rate_query($connection, "DO RELEASE_LOCK('{$lock}')");
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
