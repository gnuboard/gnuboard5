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

// SMS 테이블명
$g5['sms5_prefix']                = 'sms5_';
$g5['sms5_config_table']          = $g5['sms5_prefix'] . 'config';
$g5['sms5_write_table']           = $g5['sms5_prefix'] . 'write';
$g5['sms5_history_table']         = $g5['sms5_prefix'] . 'history';
$g5['sms5_book_table']            = $g5['sms5_prefix'] . 'book';
$g5['sms5_book_group_table']      = $g5['sms5_prefix'] . 'book_group';
$g5['sms5_form_table']            = $g5['sms5_prefix'] . 'form';
$g5['sms5_form_group_table']      = $g5['sms5_prefix'] . 'form_group';
$g5['sms5_member_history_table']  = $g5['sms5_prefix'] . 'member_history';

if ($config['cf_sms_use'] == 'icode') {

    $sms5 = sql_fetch("select * from {$g5['sms5_config_table']} ", false);
    if( $sms5['cf_member'] && trim($member['mb_hp']) ) {
        $g5['sms5_use_sideview'] = true; //회원 사이드뷰 레이어에 추가
    } else {
        $g5['sms5_use_sideview'] = false;
    }

    //==============================================================================
    // 스킨경로
    //------------------------------------------------------------------------------

    $sms5_skin_path = G5_SMS5_PATH.'/skin/'.$sms5['cf_skin']; //sms5 스킨 path
    $sms5_skin_url = G5_SMS5_URL .'/skin/'.$sms5['cf_skin']; //sms5 스킨 url

    // Demo 설정
    if (file_exists(G5_PATH.'/DEMO'))
    {
        // 받는 번호를 010-000-0000 으로 만듭니다.
        $g5['sms5_demo'] = true;

        // 아이코드에 실제로 보내지 않고 가상(Random)으로 전송결과를 저장합니다.
        $g5['sms5_demo_send'] = true;
    }

    include_once(G5_LIB_PATH.'/icode.sms.lib.php');
    include_once(G5_SMS5_PATH.'/sms5.lib.php');

}
?>