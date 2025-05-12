<?php
define('G5_IS_ADMIN', true);
define('G5_IS_SUBSCRIPTION_ADMIN_PAGE', true);
include_once ('../../common.php');

if (!defined('G5_USE_SUBSCRIPTION') || !G5_USE_SUBSCRIPTION)
    die('<p>정기결제 프로그램을 설치 후 이용해 주십시오.</p>');

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once('./admin.subscription.lib.php');

run_event('admin_common');

echo "index화면";