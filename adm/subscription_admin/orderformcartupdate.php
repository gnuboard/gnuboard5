<?php
$sub_menu = '400400';
include_once('./_common.php');

// print_r2($_POST);
// exit;

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$ct_chk_count = isset($_POST['ct_chk']) ? count($_POST['ct_chk']) : 0;
if(!$ct_chk_count)
    alert('처리할 자료를 하나 이상 선택해 주십시오.');

$status_normal = array('활성화', '비활성화');

if (in_array($_POST['ct_status'], $status_normal)) {
    ; // 통과
} else {
    alert('변경할 상태가 올바르지 않습니다.');
}

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$mod_history = '';
$cnt = (isset($_POST['ct_id']) && is_array($_POST['ct_id'])) ? count($_POST['ct_id']) : 0;
$arr_it_id = array();

for ($i=0; $i<$cnt; $i++)
{
    $k = isset($_POST['ct_chk'][$i]) ? (int) $_POST['ct_chk'][$i] : '';

    if($k === '') continue;

    $ct_id = isset($_POST['ct_id'][$k]) ? (int) $_POST['ct_id'][$k] : 0;

    if(!$ct_id)
        continue;

    //$sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_id  = '$ct_id' ";
    //$ct = sql_fetch($sql);
    
    $ct = sql_bind_select_fetch($g5['g5_subscription_cart_table'], '*', array('od_id'=>$od_id, 'ct_id'=>$ct_id));
    
    print_r2($ct);
    
    if(! (isset($ct['ct_id']) && $ct['ct_id']))
        continue;

    // 수량이 변경됐다면
    $ct_qty = isset($_POST['ct_qty'][$k]) ? (int) $_POST['ct_qty'][$k] : 0;
    if($ct['ct_qty'] != $ct_qty) {
        $diff_qty = $ct['ct_qty'] - $ct_qty;
        
        // 재고에 차이 반영.
        if($ct['ct_stock_use']) {
            if($ct['io_id']) {
                /*
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
                              */
                              
                sql_bind_update(
                    $g5['g5_shop_item_option_table'],
                    array(
                        'io_stock_qty' => array(
                            'expression' => array(
                                'column' => 'io_stock_qty',
                                'operator' => '+',
                                'value' => $diff_qty
                            )
                        )
                    ),
                    array(
                        'it_id' => $ct['it_id'],
                        'io_id' => $ct['io_id'],
                        'io_type' => $ct['io_type']
                    )
                );
            } else {
                /*
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}' ";
                            */
                            
                sql_bind_update(
                    $g5['g5_shop_item_table'],
                    array(
                        'it_stock_qty' => array(
                            'expression' => array(
                                'column' => 'it_stock_qty',
                                'operator' => '+',
                                'value' => $diff_qty
                            )
                        )
                    ),
                    array(
                        'it_id' => $ct['it_id']
                    )
                );
            }

            sql_query($sql);
        }

        // 수량변경
        /*
        $sql = " update {$g5['g5_shop_cart_table']}
                    set ct_qty = '$ct_qty'
                    where ct_id = '$ct_id'
                      and od_id = '$od_id' ";
        sql_query($sql);
        */
        
        sql_bind_update(
            $g5['g5_subscription_cart_table'],
            array(
                'ct_qty' => $ct_qty
            ),
            array(
                'ct_id' => $ct_id,
                'od_id' => $od_id
            )
        );
        
        $mod_history .= G5_TIME_YMDHIS.' '.$ct['ct_option'].' 수량변경 '.$ct['ct_qty'].' -> '.$ct_qty."\n";
    }
}

sql_bind_update(
    $g5['g5_subscription_order_table'],
    array(
        'od_enable_status' => ($ct_status === '비활성화') ? 0 : 1,
    ),
    array(
        'od_id' => $od_id
    )
);

$message = ($ct_status === '비활성화') ? '구독주문이 비활성화 되었습니다.' : '구독주문이 활성화 되었습니다.';

add_subscription_order_history($message, array(
    'hs_type' => 'subscription_order',
    'od_id' => $od_id,
    'mb_id' => $member['mb_id']
));

exit;

// 미수금 등의 정보
$info = get_order_info($od_id);

if(!$info)
    alert('주문자료가 존재하지 않습니다.');

$sql = " update {$g5['g5_shop_order_table']}
            set od_cart_price   = '{$info['od_cart_price']}',
                od_cart_coupon  = '{$info['od_cart_coupon']}',
                od_coupon       = '{$info['od_coupon']}',
                od_send_coupon  = '{$info['od_send_coupon']}',
                od_cancel_price = '{$info['od_cancel_price']}',
                od_send_cost    = '{$info['od_send_cost']}',
                od_misu         = '{$info['od_misu']}',
                od_tax_mny      = '{$info['od_tax_mny']}',
                od_vat_mny      = '{$info['od_vat_mny']}',
                od_free_mny     = '{$info['od_free_mny']}' ";
if ($mod_history) { // 주문변경 히스토리 기록
    $sql .= " , od_mod_history = CONCAT(od_mod_history,'$mod_history') ";
}

if($cancel_change) {
    $sql .= " , od_status = '취소' "; // 주문상품 모두 취소, 반품, 품절이면 주문 취소
} else {
    if (isset($_POST['ct_status']) && in_array($_POST['ct_status'], $status_normal)) { // 정상인 주문상태만 기록
        $sql .= " , od_status = '{$_POST['ct_status']}' ";
    }
}

$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

$url = "./orderform.php?od_id=$od_id&amp;$qstr";

// 신용카드 취소 때 오류가 있으면 알림
if($pg_cancel == 1 && $pg_res_cd && $pg_res_msg) {
    alert('오류코드 : '.$pg_res_cd.' 오류내용 : '.$pg_res_msg, $url);
} else {
    // 1.06.06
    $od = sql_fetch(" select od_receipt_point from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if ($od['od_receipt_point'])
        alert("포인트로 결제한 주문은,\\n\\n주문상태 변경으로 인해 포인트의 가감이 발생하는 경우\\n\\n회원관리 > 포인트관리에서 수작업으로 포인트를 맞추어 주셔야 합니다.", $url);
    else
        goto_url($url);
}