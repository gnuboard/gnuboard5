<?php
define('G5_IS_ADMIN', true);
define('G5_IS_SUBSCRIPTION_ADMIN_PAGE', true);
include_once ('../../common.php');

if (!strstr($_SERVER['SCRIPT_NAME'], 'install.php')) {
    
    $sql = " show tables like '{$g5['g5_subscription_cart_table']}' ";
    
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

$sql = " select * from {$g5['g5_shop_category_table']} limit 1 ";
$ca = sql_fetch($sql);

if (!isset($ca['ca_class_num'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}` ADD `ca_class_num` TINYINT NOT NULL DEFAULT '0' after `ca_id`, ADD INDEX (`ca_class_num`) ", false);
    
    $sql = " select * from {$g5['g5_shop_item_table']} limit 1 ";
    $it = sql_fetch($sql);

    if (!isset($it['it_class_num'])) {
        sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}` ADD `it_class_num` TINYINT NOT NULL DEFAULT '0' after `it_id`, ADD INDEX (`it_class_num`) ", false);
    }
    
    unset($it);
}

unset($ca);

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once('admin.subscription.lib.php');

run_event('subscription_admin_common');

add_stylesheet('<link rel="stylesheet" href="' . G5_ADMIN_URL . '/css/subscription.css">', 10);