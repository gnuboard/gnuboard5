<?php
include_once('../../../common.php');

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

if (!defined('G5_USE_SUBSCRIPTION') || !G5_USE_SUBSCRIPTION)
    die('<p>정기구독 설치 후 이용해 주십시오.</p>');
define('_SUBSCRIPTION_', true);