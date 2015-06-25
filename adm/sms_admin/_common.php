<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');

if (!strstr($_SERVER['SCRIPT_NAME'], 'install.php')) {
    if(!mysql_num_rows(mysql_query(" show tables like '{$g5['sms5_config_table']}' ")))
        goto_url('install.php');

    // SMS 설정값 배열변수
    //$sms5 = sql_fetch("select * from ".$g5['sms5_config_table'] );
}

add_stylesheet('<link rel="stylesheet" href="'.G5_SMS5_ADMIN_URL.'/css/sms5.css">', 0);
?>