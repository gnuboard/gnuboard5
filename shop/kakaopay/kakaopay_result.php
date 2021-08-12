<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( isset($_POST['P_NOTI']) ){

    $sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '".preg_replace("/\s+/", "", $_POST['P_NOTI'])."' ";
    $row = sql_fetch($sql);

    if (isset($row['dt_data']) && (base64_encode(base64_decode($row['dt_data'], true)) === $row['dt_data'])){
        $data = unserialize(base64_decode($row['dt_data']));
    } else {
        $data = isset($row['dt_data']) ? unserialize($row['dt_data']) : array();
    }

    if( isset($data['is_inicis_mobile_kakaopay']) && $data['is_inicis_mobile_kakaopay'] == 'mobile' ){
        
        include G5_SHOP_PATH.'/kakaopay/mobile_pay_result.php';
        return;
    }
}

if( isset($_REQUEST['P_STATUS']) && isset($_REQUEST['P_TID']) && isset($_REQUEST['P_REQ_URL']) && isset($_POST['P_NOTI']) && isset($_POST['P_AMT']) ){
    include G5_SHOP_PATH.'/kakaopay/mobile_pay_result.php';
    return;
}

include G5_SHOP_PATH.'/kakaopay/pc_pay_result.php';
return;