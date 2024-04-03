<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_nicepay.inc.php');

if (function_exists('add_log')) add_log($_POST);

$authResultCode = isset($_POST['AuthResultCode']) ? clean_xss_tags($_POST['AuthResultCode']) : '';		// authentication result code 0000:success
$authResultMsg = isset($_POST['AuthResultMsg']) ? clean_xss_tags($_POST['AuthResultMsg']) : '';		// authentication result message
$mid = isset($_POST['MID']) ? clean_xss_tags($_POST['MID']) : '';							// merchant id
$moid = isset($_POST['Moid']) ? clean_xss_tags($_POST['Moid']) : '';							// order number

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$moid' ";
$row = sql_fetch($sql);

if (empty($row)) {
    die('');
}

$data = unserialize(base64_decode($row['dt_data']));

if(isset($data['pp_id']) && $data['pp_id']) {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/personalpayformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$data['pp_id'];
} else {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/orderformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/orderform.php';
    if($_SESSION['ss_direct'])
        $page_return_url .= '?sw_direct=1';
}

$params = array();
$var_datas = array();

foreach($data as $key=>$value) {
    if(is_array($value)) {
        foreach($value as $k=>$v) {
            $_POST[$key][$k] = $params[$key][$k] = clean_xss_tags(strip_tags($v));
        }
    } else {
        if(in_array($key, array('od_memo'))){
            $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value), 0, 0, 0, 0);
        } else {
            $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value));
        }
    }
}

// 성공했다면
if ($authResultCode === '0000') {

    if(isset($data['pp_id']) && $data['pp_id']) {   //개인결제

        foreach($params as $key=>$value){

            if( in_array($key, shop_order_data_fields(1)) ){

                $var_datas[$key] = $value;
                
                $$key = $value;
            }

        }
        
        include_once(G5_MSHOP_PATH.'/personalpayformupdate.php');

    } else {    //상점주문

        foreach($params as $key=>$value){

            if( in_array($key, shop_order_data_fields()) ){

                $var_datas[$key] = $value;
                
                $$key = $value;
            }

        }

        $od_send_cost = (int) $_POST['od_send_cost'];
        $od_send_cost2 = (int) $_POST['od_send_cost2'];

        include_once(G5_MSHOP_PATH.'/orderformupdate.php');
    }

} else {
    // 실패시

    alert('오류 : '.$authResultMsg.' 코드 : '.$authResultCode, $page_return_url);
}