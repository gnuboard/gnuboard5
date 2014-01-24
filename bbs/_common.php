<?php
include_once('../common.php');

if(isset($default['de_shop_layout_use']) && $default['de_shop_layout_use']) {
    if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
        die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

    define('_SHOP_', true);
}
?>