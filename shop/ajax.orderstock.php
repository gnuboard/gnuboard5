<?php
include_once('./_common.php');

if (get_session('ss_direct'))
    $tmp_cart_id = get_session('ss_cart_direct');
else
    $tmp_cart_id = get_session('ss_cart_id');

if (get_cart_count($tmp_cart_id) == 0)// 장바구니에 담기
    die("장바구니가 비어 있습니다.\n\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.");

$keep_term = $default['de_cart_keep_term'];
if(!$keep_term)
    $keep_term = 15; // 기본값 15일

if(defined('G5_CART_STOCK_LIMIT'))
    $cart_stock_limit = G5_CART_STOCK_LIMIT;
else
    $cart_stock_limit = 3;

// 기준 시간을 초과한 경우 체크
if($cart_stock_limit > 0) {
    if($cart_stock_limit > $keep_term * 24)
        $cart_stock_limit = $keep_term * 24;

    $stocktime = G5_SERVER_TIME - (3600 * $cart_stock_limit);

    $sql = " select count(*) as cnt
                from {$g5['g5_shop_cart_table']}
                where od_id = '$tmp_cart_id'
                  and ct_status = '쇼핑'
                  and ct_select = '1'
                  and UNIX_TIMESTAMP(ct_select_time) > '$stocktime' ";
    $row = sql_fetch($sql);

    if(!$row['cnt'])
        die("주문 요청 때까지 ".$cart_stock_limit."시간 이상 경과되어 주문 상품이 초기화 됐습니다.\n\n 장바구니에서 주문하실 상품을 다시 확인해 주십시오.");
}

if (function_exists('before_check_cart_price')) {
    if(! before_check_cart_price($tmp_cart_id) ) die("장바구니 금액에 변동사항이 있습니다.\n장바구니를 다시 확인해 주세요.");
}

// 재고체크
$sql = " select *
            from {$g5['g5_shop_cart_table']}
            where od_id = '$tmp_cart_id'
              and ct_select = '1'
              and ct_status = '쇼핑' ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    $ct_qty = $row['ct_qty'];

    // 해당 상품이 품절 또는 판매중지 상태인지 체크합니다.
    $sql = " select it_soldout, it_use, ca_id, ca_id2, ca_id3 from {$g5['g5_shop_item_table']} where it_id = '".$row['it_id']."' ";
    $item = sql_fetch($sql);
    
    $category_str = '';

    // 분류에서 판매가능한지 체크합니다.
    if( $item['it_use'] && ($item['ca_id'] || $item['ca_id2'] || $item['ca_id3']) ){
        $sql = " select ca_use from {$g5['g5_shop_category_table']} where (ca_id = '".$item['ca_id']."' or ca_id = '".$item['ca_id2']."' or ca_id = '".$item['ca_id3']."') ";
        $result2 = sql_query($sql);

        while($ca=sql_fetch_array($result2)){
            if ( ! $ca['ca_use']) {
                $item['it_use'] = false;
                $category_str = '분류에서 ';
            }
        }
    }

    // 해당 상품이 품절 상태 또는 판매중지이면
    if( $item['it_soldout'] || !$item['it_use'] ){

        $soldout_txt = $item['it_soldout'] ? '품절' : $category_str.'판매중지';
        $item_option = $row['it_name'];
        if($row['io_id'])
            $item_option .= '('.$row['ct_option'].')';

        die( $item_option." 상품이 ".$soldout_txt." 되었습니다.\n\n장바구니에서 해당 상품을 삭제후 다시 주문해 주세요." );
    }

    if(!$row['io_id'])
        $it_stock_qty = get_it_stock_qty($row['it_id']);
    else
        $it_stock_qty = get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);

    if ($ct_qty > $it_stock_qty)
    {
        $item_option = $row['it_name'];
        if($row['io_id'])
            $item_option .= '('.$row['ct_option'].')';

        die($item_option." 의 재고수량이 부족합니다.\n\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
    }
}

die("");