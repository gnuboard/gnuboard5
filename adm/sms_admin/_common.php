<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_SMS5_PATH.'/sms5.lib.php');

if (!strstr($_SERVER['SCRIPT_NAME'], 'install.php')) {
    // SMS5 테이블 G5_TABLE_PREFIX 적용
    if($g5['sms5_prefix'] != 'sms5_' && sql_num_rows(sql_query("show tables like 'sms5_config'")))
    {
        echo '<script>
            alert("기존 SMS5 테이블을 sms5 prefix 기준으로 변경합니다.\n(DB 업그레이드에서 자동 적용됩니다.)");
            location.href = "'.G5_ADMIN_URL.'/dbupgrade.php";
        </script>';
        exit;
    }

    if(!sql_num_rows(sql_query(" show tables like '{$g5['sms5_config_table']}' ")))
        goto_url('install.php');

    // SMS 설정값 배열변수
    //$sms5 = sql_fetch("select * from ".$g5['sms5_config_table'] );
}

$sv = isset($_REQUEST['sv']) ? get_search_string($_REQUEST['sv']) : '';
$st = (isset($_REQUEST['st']) && $st) ? substr(get_search_string($_REQUEST['st']), 0, 12) : '';

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

add_stylesheet('<link rel="stylesheet" href="'.G5_SMS5_ADMIN_URL.'/css/sms5.css">', 0);