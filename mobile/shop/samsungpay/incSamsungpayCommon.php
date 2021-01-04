<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//삼성페이 또는 Lpay 사용시에만 해당함
if( ! is_inicis_simple_pay() || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}

include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');