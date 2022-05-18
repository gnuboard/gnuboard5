<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//삼성페이 또는 Lpay 또는 이니시스 카카오페이 사용시에만 해당함
if( ! ($default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use']) || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}

include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');