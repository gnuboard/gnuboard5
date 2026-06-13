<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! function_exists('array_map_deep') ){
    // multi-dimensional array에 사용자지정 함수적용
    function array_map_deep($fn, $array)
    {
        if(is_array($array)) {
            foreach($array as $key => $value) {
                if(is_array($value)) {
                    $array[$key] = array_map_deep($fn, $value);
                } else {
                    $array[$key] = call_user_func($fn, $value);
                }
            }
        } else {
            $array = call_user_func($fn, $array);
        }

        return $array;
    }
}

if( ! function_exists('safe_install_string_check') ){
    function safe_install_string_check( $str, $is_json=false ) {
        $is_check = false;

        if(preg_match('#\);(passthru|eval|pcntl_exec|exec|system|popen|fopen|fsockopen|file|file_get_contents|readfile|unlink|include|include_once|require|require_once)\s?#i', $str)) {
            $is_check = true;
        }

        if(preg_match('#\$_(get|post|request)\s?\[.*?\]\s?\)#i', $str)){
            $is_check = true;
        }

        if($is_check){
            $msg = "입력한 값에 안전하지 않는 문자가 포함되어 있습니다. 설치를 중단합니다.";

            if($is_json){
                die(install_json_msg($msg));
            }

            die($msg);
        }

        return array_map_deep('stripslashes', $str);
    }
}

if( ! function_exists('install_json_msg') ){
    function install_json_msg($msg, $type='error'){

        $error_msg = ($type==='error') ? $msg : '';
        $success_msg = ($type==='success') ? $msg : '';
        $exists_msg = ($type==='exists') ? $msg : '';

        return json_encode(array('error'=>$error_msg, 'success'=>$success_msg, 'exists'=>$exists_msg));
    }
}

if( ! function_exists('install_error_log') ){
    function install_error_log($message) {
        $err_file = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        @error_log('[g5 install] '.$message.' | file: '.$err_file);
    }
}

if( ! function_exists('install_html_escape') ){
    function install_html_escape($str) {
        return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
    }
}

if( ! function_exists('install_db_error') ){
    function install_db_error($link=null) {
        if (function_exists('mysqli_connect') && G5_MYSQLI_USE) {
            if ($link) {
                return mysqli_errno($link).' : '.mysqli_error($link);
            }
            return mysqli_connect_errno().' : '.mysqli_connect_error();
        }

        if (function_exists('mysql_error')) {
            return $link ? mysql_errno($link).' : '.mysql_error($link) : mysql_errno().' : '.mysql_error();
        }

        return '';
    }
}

if( ! function_exists('install_db_query') ){
    function install_db_query($sql, $link) {
        if (function_exists('mysqli_query') && G5_MYSQLI_USE) {
            return @mysqli_query($link, $sql);
        }

        return @mysql_query($sql, $link);
    }
}

if( ! function_exists('install_db_fetch_row') ){
    function install_db_fetch_row($result) {
        if (!$result) {
            return false;
        }

        if (function_exists('mysqli_fetch_row') && G5_MYSQLI_USE) {
            return mysqli_fetch_row($result);
        }

        return mysql_fetch_row($result);
    }
}

if( ! function_exists('install_db_num_rows') ){
    function install_db_num_rows($result) {
        if (!$result) {
            return 0;
        }

        if (function_exists('mysqli_num_rows') && G5_MYSQLI_USE) {
            return mysqli_num_rows($result);
        }

        return mysql_num_rows($result);
    }
}

if( ! function_exists('install_db_connect') ){
    function install_db_connect($host, $user, $pass, $db) {
        $result = array('link'=>false, 'error'=>'', 'message'=>'');

        if (function_exists('mysqli_connect') && G5_MYSQLI_USE) {
            if (function_exists('mysqli_report')) {
                mysqli_report(MYSQLI_REPORT_OFF);
            }

            $link = @mysqli_connect($host, $user, $pass);
            if (!$link) {
                $result['error'] = install_db_error();
                $result['message'] = 'MySQL Host, User, Password 정보를 확인해 주십시오.';
                return $result;
            }

            if (!@mysqli_select_db($link, $db)) {
                $result['link'] = $link;
                $result['error'] = install_db_error($link);
                $result['message'] = 'MySQL DB 정보를 확인해 주십시오.';
                return $result;
            }

            $result['link'] = $link;
            return $result;
        }

        if (!function_exists('mysql_connect')) {
            $result['message'] = 'MySQL 확장(mysqli/mysql)이 설치되어 있지 않습니다.';
            return $result;
        }

        $link = @mysql_connect($host, $user, $pass);
        if (!$link) {
            $result['error'] = install_db_error();
            $result['message'] = 'MySQL Host, User, Password 정보를 확인해 주십시오.';
            return $result;
        }

        if (!@mysql_select_db($db, $link)) {
            $result['link'] = $link;
            $result['error'] = install_db_error($link);
            $result['message'] = 'MySQL DB 정보를 확인해 주십시오.';
            return $result;
        }

        $result['link'] = $link;
        return $result;
    }
}

if( ! function_exists('install_sql_like_escape') ){
    function install_sql_like_escape($str) {
        return str_replace(array('\\', '%', '_'), array('\\\\', '\%', '\_'), $str);
    }
}

if( ! function_exists('install_sql_escape') ){
    function install_sql_escape($str, $link) {
        if (function_exists('mysqli_real_escape_string') && G5_MYSQLI_USE) {
            return mysqli_real_escape_string($link, $str);
        }

        return mysql_real_escape_string($str, $link);
    }
}

if( ! function_exists('install_table_exists') ){
    function install_table_exists($link, $table, &$error='') {
        $error = '';
        $table_like = install_sql_escape(install_sql_like_escape($table), $link);
        $result = install_db_query("SHOW TABLES LIKE '{$table_like}'", $link);

        if (!$result) {
            $error = install_db_error($link);
            install_error_log('table exists check failed: '.$error.' | table: '.$table);
            return false;
        }

        return install_db_num_rows($result) > 0;
    }
}

if( ! function_exists('install_get_existing_tables') ){
    function install_get_existing_tables($link, $prefixes) {
        $tables = array();

        foreach ($prefixes as $prefix) {
            if (!$prefix) {
                continue;
            }

            $table_like = install_sql_escape(install_sql_like_escape($prefix).'%', $link);
            $result = install_db_query("SHOW TABLES LIKE '{$table_like}'", $link);

            while ($row = install_db_fetch_row($result)) {
                if (isset($row[0])) {
                    $tables[$row[0]] = $row[0];
                }
            }
        }

        ksort($tables);

        return array_values($tables);
    }
}

if( ! function_exists('install_fail_page') ){
    function install_fail_page($message, $link=null, $prefixes=array()) {
        global $install_progress_started;

        install_error_log($message);

        if (!empty($install_progress_started)) {
            $install_progress_started = false;
?>
    </ol>
</div>
<?php
        }
?>
<div class="ins_inner">
    <h2>설치를 계속할 수 없습니다.</h2>
    <p><?php echo nl2br(install_html_escape($message)); ?></p>
<?php
        if ($link && $prefixes) {
            $tables = install_get_existing_tables($link, $prefixes);
            if ($tables) {
?>
    <p>설치가 중간에 멈춘 경우 아래 테이블이 일부 생성되었을 수 있습니다. 다시 설치하려면 DB에서 해당 테이블을 정리하거나, 재설치 옵션을 선택한 뒤 진행해 주십시오.</p>
    <ul>
<?php
                for ($i=0; $i<count($tables); $i++) {
?>
        <li><?php echo install_html_escape($tables[$i]); ?></li>
<?php
                }
?>
    </ul>
<?php
            }
        }
?>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>
<?php
        include_once('./install.inc2.php');
        exit;
    }
}

if( ! function_exists('install_query_or_fail') ){
    function install_query_or_fail($sql, $link, $message, $prefixes=array()) {
        $result = install_db_query($sql, $link);

        if (!$result) {
            install_error_log($message.' | '.install_db_error($link).' | SQL: '.$sql);
            install_fail_page($message."\n서버 오류 로그를 확인해 주십시오.", $link, $prefixes);
        }

        return $result;
    }
}

if( ! function_exists('install_check_db_capability') ){
    function install_check_db_capability($link, $table_prefix) {
        $suffix = substr(md5(uniqid('', true)), 0, 12);
        $table = $table_prefix.'install_check_'.$suffix;
        $charset = preg_replace('/[^0-9a-z_]/i', '', G5_DB_CHARSET);
        $engine = '';

        if (!$charset) {
            $charset = 'utf8';
        }

        if (in_array(strtolower(G5_DB_ENGINE), array('innodb', 'myisam'))) {
            $engine = ' ENGINE='.G5_DB_ENGINE;
        }

        $checks = array(
            "CREATE TABLE `{$table}` (`id` int(11) NOT NULL){$engine} DEFAULT CHARSET={$charset}",
            "INSERT INTO `{$table}` (`id`) VALUES (1)",
            "ALTER TABLE `{$table}` ADD `memo` varchar(20) NOT NULL DEFAULT ''",
            "DROP TABLE `{$table}`"
        );

        for ($i=0; $i<count($checks); $i++) {
            if (!install_db_query($checks[$i], $link)) {
                $error = install_db_error($link);
                install_db_query("DROP TABLE IF EXISTS `{$table}`", $link);
                install_error_log('DB capability check failed: '.$error.' | SQL: '.$checks[$i]);

                return array(false, 'DB 계정에 테이블 생성/입력/수정/삭제 권한이 있는지, DB 문자셋('.G5_DB_CHARSET.')을 사용할 수 있는지 확인해 주십시오.');
            }
        }

        return array(true, '');
    }
}

if( ! function_exists('install_split_sql') ){
    function install_split_sql($sql) {
        $queries = array();
        $query = '';
        $quote = '';
        $len = strlen($sql);

        for ($i=0; $i<$len; $i++) {
            $char = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

            if ($quote) {
                $query .= $char;

                if ($quote !== '`' && $char === '\\' && $next !== '') {
                    $query .= $next;
                    $i++;
                    continue;
                }

                if ($char === $quote) {
                    $quote = '';
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $query .= $char;
                continue;
            }

            if ($char === '-' && $next === '-' && ($i === 0 || preg_match('/\s/', $sql[$i - 1]))) {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            if ($char === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            if ($char === '/' && $next === '*') {
                $i += 2;
                while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                    $i++;
                }
                $i++;
                continue;
            }

            if ($char === ';') {
                $trimmed = trim($query);
                if ($trimmed !== '') {
                    $queries[] = $trimmed;
                }
                $query = '';
                continue;
            }

            $query .= $char;
        }

        $trimmed = trim($query);
        if ($trimmed !== '') {
            $queries[] = $trimmed;
        }

        return $queries;
    }
}

if( ! function_exists('install_load_sql_file') ){
    function install_load_sql_file($file, $from_prefix, $to_prefix) {
        if (!is_readable($file)) {
            return false;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            return false;
        }

        $sql = preg_replace('/`'.preg_quote($from_prefix, '/').'([^`]+`)/', '`'.$to_prefix.'$1', $sql);

        return install_split_sql($sql);
    }
}

if( ! function_exists('install_ensure_dir') ){
    function install_ensure_dir($dir) {
        if (!is_dir($dir) && !@mkdir($dir, G5_DIR_PERMISSION)) {
            return false;
        }

        @chmod($dir, G5_DIR_PERMISSION);

        return is_dir($dir) && is_writable($dir);
    }
}

if( ! function_exists('install_file_write') ){
    function install_file_write($handle, $content) {
        global $install_file_write_error;

        if ($install_file_write_error) {
            return false;
        }

        $result = @fwrite($handle, $content);

        if ($result === false || $result < strlen($content)) {
            $install_file_write_error = true;
            return false;
        }

        return true;
    }
}
