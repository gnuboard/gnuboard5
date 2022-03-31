<?php
define('G5_IS_ADMIN', true);
include_once ('../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_LIB_PATH.'/upgrade.lib.php');

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

if( !isset($g5['update'])) {
    $g5['update'] = new G5Update();
    $g5['update']->setNowVersion("v".G5_GNUBOARD_VER);
}

run_event('admin_common');