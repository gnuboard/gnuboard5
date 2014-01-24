<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(isset($default['de_shop_layout_use']) && $default['de_shop_layout_use'])
    include_once(G5_SHOP_PATH.'/_tail.php');
else
    include_once(G5_PATH.'/tail.php');
?>