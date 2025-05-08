<?php
define('G5_IS_ADMIN', true);
define('G5_IS_SUBSCRIPTION_ADMIN_PAGE', true);
include_once ('../../common.php');

if (!strstr($_SERVER['SCRIPT_NAME'], 'install.php')) {
    
    $sql = " show tables like '{$g5['g5_subscription_cart_table']}' ";
    
    //if (!sql_num_rows(sql_query(" show tables like '{$g5['g5_subscription_config_table']}' "))) {
    //    goto_url('install.php');
    //}
    if (!sql_num_rows(sql_query($sql))) {
        goto_url('install.php');
    }
}

if (defined('SUBSCRIPTION_INSTALL_PAGE') && SUBSCRIPTION_INSTALL_PAGE) {
    // 인스톨 페이지에서는 체크 안함
} else {
    if (!defined('G5_USE_SUBSCRIPTION') || !G5_USE_SUBSCRIPTION) {
        die('<p>정기결제 프로그램을 사용하지 않습니다.</p>');
    }
}

if (isset($token)) {
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once('admin.subscription.lib.php');

add_stylesheet('<link rel="stylesheet" href="' . G5_ADMIN_URL . '/css/subscription.css">', 10);