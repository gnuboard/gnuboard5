<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//------------------------------------------------------------------------------
// SMS 상수 모음 시작
//------------------------------------------------------------------------------

define('G5_SMS5_DIR',             'sms5');
define('G5_SMS5_PATH',            G5_PLUGIN_PATH.'/'.G5_SMS5_DIR);
define('G5_SMS5_URL',             G5_PLUGIN_URL.'/'.G5_SMS5_DIR);

define('G5_SMS5_ADMIN_DIR',        'sms_admin');
define('G5_SMS5_ADMIN_PATH',       G5_ADMIN_PATH.'/'.G5_SMS5_ADMIN_DIR);
define('G5_SMS5_ADMIN_URL',        G5_ADMIN_URL.'/'.G5_SMS5_ADMIN_DIR);

define('ICODE_JSON_SOCKET_HOST', '211.172.232.124');
define('ICODE_JSON_SOCKET_PORT', '9201');

define('G5_ICODE_LMS_MAX_LENGTH', 1500);    // 구버전 LMS 최대길이
define('G5_ICODE_JSON_MAX_LENGTH', 2000);   // JSON 버전 LMS 최대길이

// SMS 테이블명
$g5['sms5_prefix']                = 'sms5_';
$g5['sms5_config_table']          = $g5['sms5_prefix'] . 'config';
$g5['sms5_write_table']           = $g5['sms5_prefix'] . 'write';
$g5['sms5_history_table']         = $g5['sms5_prefix'] . 'history';
$g5['sms5_book_table']            = $g5['sms5_prefix'] . 'book';
$g5['sms5_book_group_table']      = $g5['sms5_prefix'] . 'book_group';
$g5['sms5_form_table']            = $g5['sms5_prefix'] . 'form';
$g5['sms5_form_group_table']      = $g5['sms5_prefix'] . 'form_group';

$sms5 = array('cf_skin'=>'', 'cf_datetime'=>'', 'cf_phone'=>'');

if (!empty($config['cf_sms_use'])) {

    $sms5 = sql_fetch("select * from {$g5['sms5_config_table']} ", false);

    // Demo 설정
    if (file_exists(G5_PATH.'/DEMO'))
    {
        // 받는 번호를 010-000-0000 으로 만듭니다.
        $g5['sms5_demo'] = true;

        // 아이코드에 실제로 보내지 않고 가상(Random)으로 전송결과를 저장합니다.
        $g5['sms5_demo_send'] = true;
    }
}