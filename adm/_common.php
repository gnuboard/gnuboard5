<?php
define('G5_IS_ADMIN', true);
require_once $GLOBALS['baseDir'] . '/g5/common.php';
require_once $GLOBALS['baseDir'] . '/g5/admin.lib.php';

if (isset($token)) {
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
