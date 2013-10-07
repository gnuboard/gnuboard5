<?php
$sub_menu = '400400';
include_once('./_common.php');

//print_r2($_POST); 

// 주문상태변경 처리
function change_order_status($od_status1, $od_status2, $od) 
{
    global $g5;

    // 원래 주문상태와 바뀔 주문상태가 같다면 처리하지 않음
    if ($od_status1 == $od_status2) return;

    $od = sql_fetch(" select od_settle_case from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od) return;

    switch ($od_status1) 
    {
        case '주문' :

            if ($od_status2 != '입금') return;
            if ($od['od_settle_case'] != '무통장') return;

            $sql = " update {$g5['g5_shop_order_table']} 
                        set od_status = '입금',  
                            od_receipt_price = od_misu,
                            od_misu = 0,
                            od_receipt_time = '".G5_TIME_YMDHIS."'
                      where od_id = '$od_id' and od_status = '주문' ";
            sql_query($sql, true);

            $sql = " update {$g5['g5_shop_cart_table']} 
                        set ct_status = '입금'
                      where od_id = '$od_id' and ct_status = '주문' ";
            sql_query($sql, true);

            break;

        case '입금' :

            if ($od_status2 != '준비') return;

            $sql = " update {$g5['g5_shop_order_table']} 
                        set od_status = '준비'
                      where od_id = '$od_id' and od_status = '입금' ";
            sql_query($sql, true);

            $sql = " update {$g5['g5_shop_cart_table']} 
                        set ct_status = '준비'
                      where od_id = '$od_id' and ct_status = '입금' ";
            sql_query($sql, true);

            break;

        case '준비' :

            if ($od_status2 != '배송') return;

            $sql = " update {$g5['g5_shop_order_table']} 
                        set od_status = '배송'
                      where od_id = '$od_id' and od_status = '준비' ";
            sql_query($sql, true);

            $sql = " update {$g5['g5_shop_cart_table']} 
                        set ct_status = '배송'
                      where od_id = '$od_id' and ct_status = '준비' ";
            sql_query($sql, true);

            $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                // 재고를 이미 사용했다면 (재고에서 이미 뺐다면)
                $stock_use = $row['ct_stock_use'];
                if ($row['ct_stock_use'])
                {
                    if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
                    {
                        $stock_use = 0;
                        // 재고에 다시 더한다.
                        if($row['io_id']) {
                            $sql = " update {$g5['g5_shop_item_option_table']}
                                        set io_stock_qty = io_stock_qty + '{$row['ct_qty']}'
                                        where it_id = '{$row['it_id']}'
                                          and io_id = '{$row['io_id']}'
                                          and io_type = '{$row['io_type']}' ";
                        } else {
                            $sql = " update {$g5['g5_shop_item_table']}
                                        set it_stock_qty = it_stock_qty + '{$row['ct_qty']}'
                                        where it_id = '{$row['it_id']}' ";
                        }

                        sql_query($sql);
                    }
                }
                else
                {
                    // 재고 오류로 인한 수정
                    if ($ct_status == '배송' || $ct_status == '완료')
                    {
                        $stock_use = 1;
                        // 재고에서 뺀다.
                        if($row['io_id']) {
                            $sql = " update {$g5['g5_shop_item_option_table']}
                                        set io_stock_qty = io_stock_qty - '{$row['ct_qty']}'
                                        where it_id = '{$row['it_id']}'
                                          and io_id = '{$row['io_id']}'
                                          and io_type = '{$row['io_type']}' ";
                        } else {
                            $sql = " update {$g5['g5_shop_item_table']}
                                        set it_stock_qty = it_stock_qty - '{$row['ct_qty']}'
                                        where it_id = '{$row['it_id']}' ";
                        }

                        sql_query($sql);
                    }
                }

                $point_use = $row['ct_point_use'];
                // 회원이면서 포인트가 0보다 크면
                // 이미 포인트를 부여했다면 뺀다.
                if ($od['mb_id'] && $row['ct_point'] && $row['ct_point_use'])
                {
                    $point_use = 0;
                    delete_point($od['mb_id'], "@delivery", $od['mb_id'], "$od_id,{$row['ct_id']}");
                }

                $sql = " update {$g5['g5_shop_cart_table']}
                            set ct_point_use  = '$point_use',
                                ct_stock_use  = '$stock_use',
                                ct_status     = '$ct_status',
                                ct_history    = CONCAT(ct_history,'$ct_history')
                            where od_id = '$row['od_id']' ";
                sql_query($sql);
            }

            break;
    }

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

for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $od_id = $_POST['od_id'][$k];
    
    change_order_status($od['od_status'], $_POST['od_status'], $od_id);

    
    echo $od_id . "<br>";
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

goto_url("./orderlist.php?$qstr");
?>