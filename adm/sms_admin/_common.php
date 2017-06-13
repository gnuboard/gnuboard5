<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_SMS5_PATH.'/sms5.lib.php');

if (!strstr($_SERVER['SCRIPT_NAME'], 'install.php')) {
    if(!sql_num_rows(sql_query(" show tables like '{$g5['sms5_config_table']}' ")))
        goto_url('install.php');

    // SMS 설정값 배열변수
    //$sms5 = sql_fetch("select * from ".$g5['sms5_config_table'] );
}

$sv = isset($_REQUEST['sv']) ? get_search_string($_REQUEST['sv']) : '';

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

add_stylesheet('<link rel="stylesheet" href="'.G5_SMS5_ADMIN_URL.'/css/sms5.css">', 0);
?>