<?php
include_once ('../config.php');
include_once('../lib/json.lib.php');
include_once('../lib/common.lib.php');    // 공통 라이브러리
include_once('./install.function.php');    // 인스톨 과정 함수 모음

include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');    // 데이타 가져오는 함수 모음

$data_path = '../'.G5_DATA_DIR;

// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
    die(install_json_msg('프로그램이 이미 설치되어 있습니다.'));
}

if (isset($_POST['table_prefix']) && preg_match("/[^0-9a-z_]+/i", $_POST['table_prefix']) ) {
    die(install_json_msg('TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.'));
}

$mysql_host  = isset($_POST['mysql_host']) ? safe_install_string_check($_POST['mysql_host'], 'json') : '';
$mysql_user  = isset($_POST['mysql_user']) ? safe_install_string_check($_POST['mysql_user'], 'json') : '';
$mysql_pass  = isset($_POST['mysql_pass']) ? safe_install_string_check($_POST['mysql_pass'], 'json') : '';
$mysql_db    = isset($_POST['mysql_db']) ? safe_install_string_check($_POST['mysql_db'], 'json') : '';
$table_prefix= isset($_POST['table_prefix']) ? safe_install_string_check(preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['table_prefix'])) : '';

$tmp_str = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
$ajax_token = md5($tmp_str.$_SERVER['REMOTE_ADDR'].dirname(dirname(__FILE__).'/'));

$bool_ajax_token = (isset($_POST['ajax_token']) && ($ajax_token == $_POST['ajax_token'])) ? true : false;

if( !($mysql_host && $mysql_user && $mysql_pass && $mysql_db && $table_prefix && $bool_ajax_token) ){
    die(install_json_msg('잘못된 요청입니다.'));
}

try {
    $dblink = sql_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
} catch (Exception $e) {
}

if (!$dblink) {
    die(install_json_msg('MySQL Host, User, Password 를 확인해 주십시오.'));
}

try {
    $select_db = sql_select_db($mysql_db, $dblink);
} catch (Exception $e) {
}

if (!$select_db) {
    die(install_json_msg('MySQL DB 를 확인해 주십시오.'));
}

if(sql_query("DESCRIBE `{$table_prefix}config`", G5_DISPLAY_SQL_ERROR, $dblink)) {
    die(install_json_msg('주의! 이미 테이블이 존재하므로, 기존 DB 자료가 망실됩니다. 계속 진행하겠습니까?', 'exists'));
}

die(install_json_msg('ok', 'success'));