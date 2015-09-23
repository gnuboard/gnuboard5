<?php
if (!defined('_GNUBOARD_')) exit;

// 상품옵션별재고 또는 상품재고에 더하기
function add_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 상품옵션별재고 또는 상품재고에서 빼기
function subtract_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 주문과 장바구니의 상태를 변경한다.
function change_status($od_id, $current_status, $change_status)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_status = '{$change_status}' where od_id = '{$od_id}' and od_status = '{$current_status}' ";
    sql_query($sql, true);

    $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '{$change_status}' where od_id = '{$od_id}' and ct_status = '{$current_status}' ";
    sql_query($sql, true);
}


// 주문서에 입금시 update
function order_update_receipt($od_id)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_receipt_price = od_misu, od_misu = 0, od_receipt_time = '".G5_TIME_YMDHIS."' where od_id = '$od_id' and od_status = '입금' ";
    return sql_query($sql);
}


// 주문서에 배송시 update
function order_update_delivery($od_id, $mb_id, $change_status, $delivery)
{
    global $g5;

    if($change_status != '배송')
        return;

    $sql = " update {$g5['g5_shop_order_table']} set od_delivery_company = '{$delivery['delivery_company']}', od_invoice = '{$delivery['invoice']}', od_invoice_time = '{$delivery['invoice_time']}' where od_id = '$od_id' and od_status = '준비' ";
    sql_query($sql);

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 재고를 사용하지 않았다면
        $stock_use = $row['ct_stock_use'];

        if(!$row['ct_stock_use'])
        {
            // 재고에서 뺀다.
            subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
            $stock_use = 1;

            $sql = " update {$g5['g5_shop_cart_table']} set ct_stock_use  = '$stock_use' where ct_id = '{$row['ct_id']}' ";
            sql_query($sql);
        }
    }
}

// 처리내용 SMS
function conv_sms_contents($od_id, $contents)
{
    global $g5, $config, $default;

    $sms_contents = '';

    if ($od_id && $config['cf_sms_use'] == 'icode')
    {
        $sql = " select od_id, od_name, od_invoice, od_receipt_price, od_delivery_company
                    from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $od = sql_fetch($sql);

        $sms_contents = $contents;
        $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
        $sms_contents = str_replace("{입금액}", number_format($od['od_receipt_price']), $sms_contents);
        $sms_contents = str_replace("{택배회사}", $od['od_delivery_company'], $sms_contents);
        $sms_contents = str_replace("{운송장번호}", $od['od_invoice'], $sms_contents);
        $sms_contents = str_replace("{주문번호}", $od['od_id'], $sms_contents);
        $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);
    }

    return stripslashes($sms_contents);
}
?>