<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
require_once(G5_SHOP_PATH.'/inicis/libs/HttpClient.php');
require_once(G5_SHOP_PATH.'/inicis/libs/json_lib.php');

@header("Progma:no-cache");
@header("Cache-Control:no-cache,must-revalidate");

$request_mid = isset($_POST['mid']) ? clean_xss_tags($_POST['mid']) : '';

if( ($request_mid != $default['de_inicis_mid']) ){
    alert("요청된 mid 와 설정된 mid 가 틀립니다.");
}

$orderNumber = isset($_POST['orderNumber']) ? preg_replace("/[ #\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", "", strip_tags($_POST['orderNumber'])) : 0;
$session_order_num = get_session('ss_order_inicis_id');

if( !$orderNumber ){
    alert("주문번호가 없습니다.");
}

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$orderNumber' ";
$row = sql_fetch($sql);

if( empty($row) ){
    alert("임시 주문정보가 저장되지 않았습니다.");
}

$data = unserialize(base64_decode($row['dt_data']));

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

if(isset($data['pp_id']) && $data['pp_id']) {   //개인결제

    foreach($params as $key=>$value){

        if( in_array($key, array('pp_name', 'pp_email', 'pp_hp', 'pp_settle_case')) ){

            $var_datas[$key] = $value;
            
            $$key = $value;
        }

    }

    include_once(G5_SHOP_PATH.'/personalpayformupdate.php');

} else {    //상점주문

    foreach($params as $key=>$value){

        if( in_array($key, array('od_price', 'od_name', 'od_tel', 'od_hp', 'od_email', 'od_memo', 'od_settle_case', 'max_temp_point', 'od_temp_point', 'od_bank_account', 'od_deposit_name', 'od_test', 'od_ip', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon', 'od_b_zip', 'od_send_cost', 'od_send_cost2', 'od_hope_date')) ){

            $var_datas[$key] = $value;
            
            $$key = $value;
        }

    }

    $od_send_cost = (int) $_POST['od_send_cost'];
    $od_send_cost2 = (int) $_POST['od_send_cost2'];

    include_once(G5_SHOP_PATH.'/orderformupdate.php');
}