<?php
$sub_menu = '400400';
include_once('./_common.php');

//print_r2($_POST); exit;

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

    $sql = " update {$g5['g5_shop_order_table']} set od_delivery_company = '{$delivery['delivery_company']}', od_invoice = '{$delivery['invoice']}', od_invoice_time = '{$delivery['invoice_time']}' where od_id = '$od_id' and od_status = '배송' ";
    sql_query($sql);

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 재고를 이미 사용했거나 재고에서 이미 뺐다면
        $stock_use = $row['ct_stock_use'];

        if ($row['ct_stock_use'])
        {
            if ($change_status == '주문' ||
                $change_status == '취소' ||
                $change_status == '반품' ||
                $change_status == '품절')
            {
                // 재고에 다시 더한다.
                add_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
                $stock_use = 0;
            }
        }
        else
        {
            // 재고 오류로 인한 수정
            if ($change_status == '배송' ||
                $change_status == '완료')
            {
                // 재고에서 뺀다.
                subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
                $stock_use = 1;
            }
        }

        $point_use = $row['ct_point_use'];

        // 회원이면서 포인트가 0보다 크거나 이미 포인트를 부여했다면 뺀다.
        if ($mb_id && $row['ct_point'] && $row['ct_point_use'])
        {
            delete_point($mb_id, "@delivery", $mb_id, "$od_id,{$row['ct_id']}");
            $point_use = 0;
        }

        /*
        $sql = " update {$g5['g5_shop_cart_table']}
                    set ct_point_use  = '$point_use',
                        ct_stock_use  = '$stock_use',
                        ct_history    = CONCAT(ct_history,'$ct_history')
                    where od_id = '$od_id' ";
        */
        $sql = " update {$g5['g5_shop_cart_table']} set ct_point_use  = '$point_use', ct_stock_use  = '$stock_use' where od_id = '$od_id' ";
        sql_query($sql);
    }
}


for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k     = $_POST['chk'][$i];
    $od_id = $_POST['od_id'][$k];

    $invoice      = $_POST['od_invoice'][$k];
    $invoice_time = $_POST['od_invoice_time'][$k];
    $delivery_company = $_POST['od_delivery_company'][$k];

    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od) continue;

    //change_order_status($od['od_status'], $_POST['od_status'], $od);
    //echo $od_id . "<br>";

    $current_status = $od['od_status'];
    $change_status  = $_POST['od_status'];

    switch ($current_status)
    {
        case '주문' :
            if ($change_status != '입금') continue;
            if ($od['od_settle_case'] != '무통장') continue;
            change_status($od_id, '주문', '입금');
            order_update_receipt($od_id);
            break;

        case '입금' :
            if ($change_status != '준비') continue;
            change_status($od_id, '입금', '준비');
            break;

        case '준비' :
            if ($change_status != '배송') continue;
            change_status($od_id, '준비', '배송');

            $delivery['invoice'] = $invoice;
            $delivery['invoice_time'] = $invoice_time;
            $delivery['delivery_company'] = $delivery_company;

            order_update_delivery($od_id, $od['mb_id'], $change_status, $delivery);

            /*
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_delivery_company = '$delivery_company',
                            od_invoice      = '$invoice',
                            od_invoice_time = '$invoice_time'
                      where od_id = '$od_id' and od_status = '배송' ";
            sql_query($sql, true);

            $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
            $result = sql_query($sql);

            for ($i=0; $row=sql_fetch_array($result); $i++)
            {
                // 재고를 이미 사용했거나 재고에서 이미 뺐다면
                $stock_use = $row['ct_stock_use'];

                if ($row['ct_stock_use'])
                {
                    if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
                    {
                        $stock_use = 0;
                        // 재고에 다시 더한다.
                        add_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
                    }
                }
                else
                {
                    // 재고 오류로 인한 수정
                    if ($ct_status == '배송' || $ct_status == '완료')
                    {
                        $stock_use = 1;
                        // 재고에서 뺀다.
                        subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
                    }
                }

                $point_use = $row['ct_point_use'];

                // 회원이면서 포인트가 0보다 크거나 이미 포인트를 부여했다면 뺀다.
                if ($od['mb_id'] && $row['ct_point'] && $row['ct_point_use'])
                {
                    $point_use = 0;
                    delete_point($od['mb_id'], "@delivery", $od['mb_id'], "$od_id,{$row['ct_id']}");
                }

                $sql = " update {$g5['g5_shop_cart_table']}
                            set ct_point_use  = '$point_use',
                                ct_stock_use  = '$stock_use',
                                ct_history    = CONCAT(ct_history,'$ct_history')
                            where od_id = '{$row['od_id']}' ";
                sql_query($sql);
            }
            */

            break;

        case '배송' :
            if ($change_status != '완료') continue;
            change_status($od_id, '배송', '완료');
            break;

    } // switch end


    // 주문정보
    $info = get_order_info($od_id);
    if(!$info) continue;

    $sql = " update {$g5['g5_shop_order_table']}
                set od_misu         = '{$info['od_misu']}',
                    od_tax_mny      = '{$info['od_tax_mny']}',
                    od_vat_mny      = '{$info['od_vat_mny']}',
                    od_free_mny     = '{$info['od_free_mny']}',
                    od_send_cost    = '{$info['od_send_cost']}'
                where od_id = '$od_id' ";
    sql_query($sql, true);

}

$qstr  = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search";
$qstr .= "&amp;od_status=$od_status";
$qstr .= "&amp;od_settle_case=$od_settle_case";
$qstr .= "&amp;od_misu=$od_misu";
$qstr .= "&amp;od_cancel_price=$od_cancel_price";
$qstr .= "&amp;od_receipt_price=$od_receipt_price";
$qstr .= "&amp;od_receipt_point=$od_receipt_point";
$qstr .= "&amp;od_receipt_coupon=$od_receipt_coupon";
//$qstr .= "&amp;page=$page";

//exit;

goto_url("./orderlist.php?$qstr");
?>