<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_naverpay.inc.php');
include_once(G5_LIB_PATH.'/naverpay.lib.php');

$pattern = '#[/\'\"%=*\#\(\)\|\+\&\!\$~\{\}\[\]`;:\?\^\,]#';
$post_naverpay_form = isset($_POST['naverpay_form']) ? clean_xss_tags($_POST['naverpay_form']) : '';

$is_collect = false;    //착불체크 변수 초기화
$is_prepay = false;     //선불체크 변수 초기화
$is_cart = false;       //장바구니 체크 변수 초기화

if($post_naverpay_form == 'cart.php') {
    if(! (isset($_POST['ct_chk']) && is_array($_POST['ct_chk']) && count($_POST['ct_chk'])))
        return_error2json('구매하실 상품을 하나이상 선택해 주십시오.');

    $s_cart_id = get_session('ss_cart_id');
    $fldcnt = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;
    $items = array();

    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = isset($_POST['ct_chk'][$i]) ? $_POST['ct_chk'][$i] : '';
        $it_id = isset($_POST['it_id'][$i]) ? preg_replace($pattern, '', $_POST['it_id'][$i]) : '';

        if(!$ct_chk || !$it_id)
            continue;

        // 장바구니 상품
        $sql = " select ct_id, it_id, ct_option, io_id, io_type, ct_qty, ct_send_cost, it_sc_type from {$g5['g5_shop_cart_table']} where od_id = '$s_cart_id' and it_id = '$it_id' and ct_status = '쇼핑' order by ct_id asc ";
        $result = sql_query($sql);

        for($k=0; $row=sql_fetch_array($result); $k++) {
            $_POST['io_id'][$it_id][] = $row['io_id'];
            $_POST['io_type'][$it_id][] = $row['io_type'];
            $_POST['ct_qty'][$it_id][] = $row['ct_qty'];
            $_POST['io_value'][$it_id][] = $row['ct_option'];
            $_POST['ct_send_cost'][$it_id][] = $row['ct_send_cost'];

            $is_free = false;   //무료 인지 체크 변수 초기화

            if( $row['it_sc_type'] == 2 ){
                // 합계금액 계산
                $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                                SUM(ct_point * ct_qty) as point,
                                SUM(ct_qty) as qty
                            from {$g5['g5_shop_cart_table']}
                            where it_id = '{$row['it_id']}'
                              and od_id = '$s_cart_id' ";
                $sum = sql_fetch($sql);

                $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $s_cart_id);

                if($sendcost == 0)
                    $is_free = true;
            }

            if( !$is_free && ! $row['ct_send_cost'] ){  //무료가 아니며 선불인 경우
                $is_prepay = true;
            } else if ( !$is_free && $row['ct_send_cost'] == 1 ){   //무료가 아니며 착불인 경우
                $is_collect = true;
            }
        }

        if($k > 0)
            $items[] = $it_id;
    }

    $is_cart = true;
    $_POST['it_id'] = $items;
}

//착불인 상품과 선불인 상품을 주문할수 없게 하려면
/*
if( $is_cart && $is_prepay && $is_collect ){
    return_error2json("배송비 착불인 상품과 선불인 상품을 동시에 주문할수 없습니다.\n\n장바구니에서 착불 또는 선불 중 한가지를 선택하여 상품들을 주문해 주세요.");
}
*/

$count = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;
if ($count < 1)
    return_error2json('구매하실 상품을 선택하여 주십시오.');

$itm_ids     = array();
$sel_options = array();
$sup_options = array();

if($post_naverpay_form == 'item.php')
    $back_uri = isset($_POST['it_id'][0]) ? shop_item_url($_POST['it_id'][0]) : '';
else if($post_naverpay_form == 'cart.php')
    $back_uri = G5_SHOP_URL.'/cart.php';
else
    $back_uri = '';

define('NAVERPAY_BACK_URL', $back_uri);

for($i=0; $i<$count; $i++) {
    $it_id = isset($_POST['it_id'][$i]) ? preg_replace($pattern, '', $_POST['it_id'][$i]) : '';
    $opt_count = (isset($_POST['io_id'][$it_id]) && is_array($_POST['io_id'][$it_id])) ? count($_POST['io_id'][$it_id]) : 0;
    
    if( ! $it_id) continue;

    if($opt_count && $_POST['io_type'][$it_id][0] != 0)
        return_error2json('상품의 선택옵션을 선택해 주십시오.');

    for($k=0; $k<$opt_count; $k++) {
        if ($_POST['ct_qty'][$it_id][$k] < 1)
            return_error2json('수량은 1 이상 입력해 주십시오.');
    }

    // 상품정보
    $it = get_shop_item($it_id, true);

    if(! (isset($it['it_id']) && $it['it_id']))
        return_error2json('상품정보가 존재하지 않습니다.');

    if(!$it['it_use'] || $it['it_soldout'] || $it['it_tel_inq'])
        return_error2json($it['it_name'].' 는(은) 구매할 수 없는 상품입니다.');

    // 최소, 최대 수량 체크
    if($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
        $sum_qty = 0;
        for($k=0; $k<$opt_count; $k++) {
            if($_POST['io_type'][$it_id][$k] == 0)
                $sum_qty += $_POST['ct_qty'][$it_id][$k];
        }

        if($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty'])
            return_error2json($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_min_qty']).'개 이상 주문해 주십시오.');

        if($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty'])
            return_error2json($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.');
    }

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for($k=0; $row=sql_fetch_array($result); $k++) {
        $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
        $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
        $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
        $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];

        // 선택옵션 개수
        if(!$row['io_type'])
            $lst_count++;
    }

    //--------------------------------------------------------
    //  재고 검사
    //--------------------------------------------------------
    for($k=0; $k<$opt_count; $k++) {
        $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['io_id'][$it_id][$k]))) : '';
        $io_type = isset($_POST['io_type'][$it_id][$k]) ? (int) $_POST['io_type'][$it_id][$k] : 0;
        $io_value = isset($_POST['io_value'][$it_id][$k]) ? (int) $_POST['io_value'][$it_id][$k] : 0;

        // 재고 구함
        $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;
        if(!$io_id)
            $it_stock_qty = get_it_stock_qty($it_id);
        else
            $it_stock_qty = get_option_stock_qty($it_id, $io_id, $io_type);

        if ($ct_qty > $it_stock_qty)
        {
            return_error2json($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
        }
    }
    //--------------------------------------------------------

    $itm_ids[] = $it_id;

    for($k=0; $k<$opt_count; $k++) {
        $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['io_id'][$it_id][$k]))) : '';
        $io_type = isset($_POST['io_type'][$it_id][$k]) ? (int) $_POST['io_type'][$it_id][$k] : 0;
        $io_value = isset($_POST['io_value'][$it_id][$k]) ? $_POST['io_value'][$it_id][$k] : '';

        // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
        if($lst_count && $io_id == '')
            continue;

        // 구매할 수 없는 옵션은 건너뜀
        if($io_id && !$opt_list[$io_type][$io_id]['use'])
            continue;

        $io_price = isset($opt_list[$io_type][$io_id]['price']) ? $opt_list[$io_type][$io_id]['price'] : 0;
        $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;

        $it_price = get_price($it);

        // 구매가격이 음수인지 체크
        if($io_type) {
            /*  // 구매금액이 0원 이하일 경우
            if((int)$io_price <= 0) {
                return_error2json('구매금액이 음수 또는 0원인 상품은 구매할 수 없습니다.');
            }
            */
            if((int)$io_price < 0) {
                return_error2json('구매금액이 0원 미만인 상품은 구매할 수 없습니다.');
            }
        } else {
            if((int)$it_price + (int)$io_price <= 0)
                return_error2json('구매금액이 음수 또는 0원인 상품은 구매할 수 없습니다.');
        }
        
        $ct_send_cost = 0;
        if($ct_send_cost != 1) {  //
            if( $is_cart && !empty($_POST['ct_send_cost'][$it_id][$k]) ){

                $ct_send_cost = $_POST['ct_send_cost'][$it_id][$k];

            } else {
                // 배송비결제
                if($it['it_sc_type'] == 1)
                    $ct_send_cost = 2; // 무료
                else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
                    $ct_send_cost = 1; // 착불
            }

            // 조건부 무료배송시 착불일 경우 ( 야수님이 알려주심 )
            if ($it['it_sc_type'] === 2 && $ct_send_cost === 1 && ((int)$io_price + (int)$it_price) * $ct_qty >= $it['it_sc_minimum'] ){
                $ct_send_cost = 2; // 무료
            }
        }

        // 옵션정보배열에 저장
        $options[$it_id][] = array(
            'option'    => get_text(stripslashes($io_value)),
            'price'     => $io_price,
            'qty'       => $ct_qty,
            'send_cost' => $ct_send_cost,
            'type'      => $io_type,
            'io_id'     => $io_id
        );
    }
}

$order = new naverpay_register($options, $ct_send_cost);
$query = $order->query();
$totalPrice = $order->total_price;

//echo $query.'<br>'.PHP_EOL;

$nc_sock = @fsockopen($req_addr, $req_port, $errno, $errstr);
if ($nc_sock) {
    fwrite($nc_sock, $buy_req_url."\r\n" );
    fwrite($nc_sock, "Host: ".$req_host.":".$req_port."\r\n" );
    fwrite($nc_sock, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n");
    fwrite($nc_sock, "Content-length: ".strlen($query)."\r\n");
    fwrite($nc_sock, "Accept: */*\r\n");
    fwrite($nc_sock, "\r\n");
    fwrite($nc_sock, $query."\r\n");
    fwrite($nc_sock, "\r\n");
    
    $headers = $bodys = '';

    // get header
    while(!feof($nc_sock)) {
        $header=fgets($nc_sock,4096);
        if($header=="\r\n") {
            break;
        } else {
            $headers .= $header;
        }
    }
    // get body
    while(!feof($nc_sock)) {
        $bodys.=fgets($nc_sock,4096);
    }

    fclose($nc_sock);

    $resultCode = substr($headers,9,3);
    if ($resultCode == 200) {
        // success
        $orderId = $bodys;
    } else {
        // fail
        return_error2json($bodys);
    }
} else {
    //echo "$errstr ($errno)<br>\n";
    return_error2json($errstr ($errno));
    exit(-1);
    //에러처리
}

if($resultCode == 200)
    die(json_encode(array('error'=>'', 'ORDER_ID'=>$orderId, 'SHOP_ID'=>$default['de_naverpay_mid'], 'TOTAL_PRICE'=>$totalPrice)));