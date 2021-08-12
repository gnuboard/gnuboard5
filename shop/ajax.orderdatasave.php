<?php
include_once('./_common.php');

if(empty($_POST))
    die('정보가 넘어오지 않았습니다.');

// 일정 기간이 경과된 임시 데이터 삭제
/*
$limit_time = date("Y-m-d H:i:s", (G5_SERVER_TIME - 86400 * 1));
$sql = " delete from {$g5['g5_shop_order_data_table']} where dt_type = '1' and dt_time < '$limit_time' ";
sql_query($sql);
*/

$od_settle_case = isset($_POST['od_settle_case']) ? clean_xss_tags($_POST['od_settle_case'], 1, 1) : '';

if(isset($_POST['pp_id']) && $_POST['pp_id']) {
    $od_id   = get_session('ss_personalpay_id');
    $cart_id = 0;

    $sql = "select pp_use, pp_tno from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ";
    $pp_row = sql_fetch($sql);

    if( $pp_row['pp_tno'] ){
        die('해당 개인결제는 이미 결제되었습니다.');
    } else if( ! $pp_row['pp_use'] ){
        die('해당 개인결제는 사용이 금지되어 있습니다.');
    }

} else {
    $od_id   = get_session('ss_order_id');
    $_POST['sw_direct'] = get_session('ss_direct');
    $_POST['od_test']   = $default['de_card_test'];
    $_POST['od_ip']     = $_SERVER['REMOTE_ADDR'];

    if ($_POST['sw_direct']) {
        $cart_id = get_session('ss_cart_direct');
    }
    else {
        $cart_id = get_session('ss_cart_id');
    }

    if( G5_IS_MOBILE && $default['de_pg_service'] == 'inicis' ){
        $_POST['post_cart_id'] = $cart_id;
    }
}

$dt_data = base64_encode(serialize($_POST));

// 동일한 주문번호가 있는지 체크
$sql = " select count(*) as cnt from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ";
$row = sql_fetch($sql);
if($row['cnt'])
    sql_query(" delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ");

$default_pg = $default['de_pg_service'];

if( $od_settle_case == '삼성페이' ){    //현재 삼성페이인 경우에는 pg를 inicis로 처리 
    $default_pg = 'inicis';
}

$sql = " insert into {$g5['g5_shop_order_data_table']}
            set od_id   = '$od_id',
                cart_id = '$cart_id',
                mb_id   = '{$member['mb_id']}',
                dt_pg   = '$default_pg',
                dt_data = '$dt_data',
                dt_time = '".G5_TIME_YMDHIS."' ";
sql_query($sql);

die('');