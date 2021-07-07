<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 타 PG 사용시 NHN KCP 네이버페이 사용이 설정되어 있는지 체크, 그렇지 않다면 return;
if( !(function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp')) ){
    return;
}

include_once(G5_MSHOP_PATH.'/settle_kcp.inc.php');