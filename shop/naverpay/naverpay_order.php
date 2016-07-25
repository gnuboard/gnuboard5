<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_naverpay.inc.php');
include_once(G5_LIB_PATH.'/naverpay.lib.php');

if($_POST['naverpay_form'] == 'cart.php') {
    if(!count($_POST['ct_chk']))
        return_error2json('구매하실 상품을 하나이상 선택해 주십시오.');

    $s_cart_id = get_session('ss_cart_id');
    $fldcnt = count($_POST['it_id']);
    $items = array();

    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = $_POST['ct_chk'][$i];

        if(!$ct_chk)
            continue;

        $it_id = $_POST['it_id'][$i];

        // 장바구니 상품
        $sql = " select ct_id, it_id, ct_option, io_id, io_type, ct_qty from {$g5['g5_shop_cart_table']} where od_id = '$s_cart_id' and it_id = '$it_id' and ct_status = '쇼핑' order by ct_id asc ";
        $result = sql_query($sql);

        for($k=0; $row=sql_fetch_array($result); $k++) {
            $_POST['io_id'][$it_id][] = $row['io_id'];
            $_POST['io_type'][$it_id][] = $row['io_type'];
            $_POST['ct_qty'][$it_id][] = $row['ct_qty'];
            $_POST['io_value'][$it_id][] = $row['ct_option'];
        }

        if($k > 0)
            $items[] = $it_id;
    }

    $_POST['it_id'] = $items;
}

$count = count($_POST['it_id']);
if ($count < 1)
    return_error2json('구매하실 상품을 선택하여 주십시오.');

$itm_ids     = array();
$sel_options = array();
$sup_options = array();

if($_POST['naverpay_form'] == 'item.php')
    $back_uri = '/item.php?it_id='.$_POST['it_id'][0];
else if($_POST['naverpay_form'] == 'cart.php')
    $back_uri = '/cart.php';
else
    $back_uri = '';

define('NAVERPAY_BACK_URL', G5_SHOP_URL.$back_uri);

for($i=0; $i<$count; $i++) {
    $it_id = $_POST['it_id'][$i];
    $opt_count = count($_POST['io_id'][$it_id]);

    if($opt_count && $_POST['io_type'][$it_id][0] != 0)
        return_error2json('상품의 선택옵션을 선택해 주십시오.');

    for($k=0; $k<$opt_count; $k++) {
        if ($_POST['ct_qty'][$it_id][$k] < 1)
            return_error2json('수량은 1 이상 입력해 주십시오.');
    }

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);
    if(!$it['it_id'])
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
        $io_id = $_POST['io_id'][$it_id][$k];
        $io_type = $_POST['io_type'][$it_id][$k];
        $io_value = $_POST['io_value'][$it_id][$k];

        // 재고 구함
        $ct_qty = $_POST['ct_qty'][$it_id][$k];
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
        $io_id = $_POST['io_id'][$it_id][$k];
        $io_type = $_POST['io_type'][$it_id][$k];
        $io_value = $_POST['io_value'][$it_id][$k];

        // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
        if($lst_count && $io_id == '')
            continue;

        // 구매할 수 없는 옵션은 건너뜀
        if($io_id && !$opt_list[$io_type][$io_id]['use'])
            continue;

        $io_price = $opt_list[$io_type][$io_id]['price'];
        $ct_qty = $_POST['ct_qty'][$it_id][$k];

        $it_price = get_price($it);

        // 구매가격이 음수인지 체크
        if($io_type) {
            if((int)$io_price <= 0)
                return_error2json('구매금액이 음수 또는 0원인 상품은 구매할 수 없습니다.');
        } else {
            if((int)$it_price + (int)$io_price <= 0)
                return_error2json('구매금액이 음수 또는 0원인 상품은 구매할 수 없습니다.');
        }

        // 배송비결제
        if($it['it_sc_type'] == 1)
            $ct_send_cost = 2; // 무료
        else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
            $ct_send_cost = 1; // 착불

        // 옵션정보배열에 저장
        $options[$it_id][] = array(
            'option'    => $io_value,
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
?>