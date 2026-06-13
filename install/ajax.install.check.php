<?php
$g5_path['path'] = '..';
include_once('install_common.php');
include_once('../config.php');
include_once('./install.function.php');    // 인스톨 과정 함수 모음
include_once('../lib/common.lib.php');    // 공통 라이브러리
include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');    // 데이터 가져오는 함수 모음

@header('Content-Type: application/json; charset=utf-8');

$data_path = '../'.G5_DATA_DIR;

// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
    die(install_json_msg('프로그램이 이미 설치되어 있습니다.'));
}

if (isset($_POST['table_prefix']) && preg_match("/[^0-9a-z_]+/i", $_POST['table_prefix'])) {
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

if (!($mysql_host && $mysql_user && $mysql_db && $table_prefix && $bool_ajax_token)) {
    die(install_json_msg('잘못된 요청입니다.'));
}

$connect = install_db_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
$dblink = $connect['link'];

if (!$dblink || $connect['message']) {
    if ($connect['error']) {
        install_error_log($connect['message'].' | '.$connect['error']);
    }
    die(install_json_msg($connect['message'] ? $connect['message'] : 'MySQL 정보를 확인해 주십시오.'));
}

$db_check = install_check_db_capability($dblink, $table_prefix);
if (!$db_check[0]) {
    die(install_json_msg($db_check[1]));
}

$table_check_error = '';
$table_exists = install_table_exists($dblink, $table_prefix.'config', $table_check_error);
if ($table_check_error) {
    die(install_json_msg('테이블 존재 여부를 확인하지 못했습니다. DB 계정 권한을 확인해 주십시오.'));
}

if ($table_exists) {
    die(install_json_msg('주의! 이미 테이블이 존재하므로, 기존 DB 자료가 망실됩니다. 계속 진행하겠습니까?', 'exists'));
}

die(install_json_msg('ok', 'success'));
