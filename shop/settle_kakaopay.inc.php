<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$is_kakaopay_use = false;
if($default['de_kakaopay_mid'] && $default['de_kakaopay_key'] && $default['de_kakaopay_enckey'] && $default['de_kakaopay_hashkey'] && $default['de_kakaopay_cancelpwd']) {
    $is_kakaopay_use = true;
    require_once(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
}
?>