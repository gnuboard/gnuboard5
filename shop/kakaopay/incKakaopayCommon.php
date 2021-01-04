<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 카카오페이를 사용하지 않을 경우 return;
if( ! $default['de_kakaopay_enckey'] ) return;

if( !isset($is_mobile_order) ){
    $is_mobile_order = is_mobile();
}

if( $is_mobile_order ){
    include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');

    if ( $default['de_card_test']) {
        if ($default['de_escrow_use'] == 1) {
            $default['de_kakaopay_mid'] = 'iniescrow0';
            $default['de_kakaopay_cancelpwd'] = '1111';
        } else {
            $default['de_kakaopay_mid'] = 'INIpayTest';
            $default['de_kakaopay_cancelpwd'] = '1111';
        }
    } else {
        $default['de_kakaopay_mid'] = 'SIRK'.$default['de_kakaopay_mid'];
    }

    $noti_url   = G5_SHOP_URL.'/kakaopay/mobile_settle_common.php';
    $next_url   = G5_SHOP_URL.'/kakaopay/mobile_pay_approval.php';
    $return_url = G5_SHOP_URL.'/kakaopay/mobile_pay_return.php?oid=';

    return;
}

include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

if ($default['de_card_test']) {
    if ($default['de_escrow_use'] == 1) {
        $default['de_kakaopay_mid'] = 'iniescrow0';
        $default['de_kakaopay_key'] = 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS';
        $default['de_kakaopay_cancelpwd'] = '1111';
    } else {
        $default['de_kakaopay_mid'] = 'INIpayTest';
        $default['de_kakaopay_key'] = 'SU5JTElURV9UUklQTEVERVNfS0VZU1RS';
        $default['de_kakaopay_cancelpwd'] = '1111';
    }
    
    if( !(isset($stdpay_js_url) && $stdpay_js_url) ){
        $stdpay_js_url = 'https://stgstdpay.inicis.com/stdjs/INIStdPay.js';
    }
} else {
    $default['de_kakaopay_mid'] = 'SIRK'.$default['de_kakaopay_mid'];

    // 실 결제 URL
    if( !(isset($stdpay_js_url) && $stdpay_js_url) ){
        $stdpay_js_url = 'https://stdpay.inicis.com/stdjs/INIStdPay.js';
    }
}

$returnUrl = G5_SHOP_URL.'/kakaopay/inicis_kk_return.php';