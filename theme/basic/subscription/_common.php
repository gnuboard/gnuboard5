<?php
include_once('../../../common.php');

if (isset($_REQUEST['sort']) && !preg_match("/(--|#|\/\*|\*\/)/", $_REQUEST['sort']))  {
    $sort = trim($_REQUEST['sort']);
    $sort = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $sort);
} else {
    $sort = '';
}

if (isset($_REQUEST['sortodr']))  {
    $sortodr = preg_match("/^(asc|desc)$/i", $sortodr) ? $sortodr : '';
} else {
    $sortodr = '';
}

if (!defined('G5_SUBSCRIPTION_SHOP') || !G5_SUBSCRIPTION_SHOP)
    die('<p>정기결제 프로그램 설치 후 이용해 주십시오.</p>');

define('_SUBSCRIPTION_', true);