<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$ct_chk_count = count($_POST['ct_chk']);
if(!$ct_chk_count)
    alert('처리할 자료를 하나 이상 선택해 주십시오.');

$status_normal = array('주문','입금','준비','배송','완료');
$status_cancel = array('취소','반품','품절');

if (in_array($_POST['ct_status'], $status_normal) || in_array($_POST['ct_status'], $status_cancel)) {
    ; // 통과
} else {
    alert('변경할 상태가 올바르지 않습니다.');
}

$mod_history = '';
$cnt = count($_POST['ct_id']);
for ($i=0; $i<$cnt; $i++)
{
    $k = $_POST['ct_chk'][$i];
    $ct_id = $_POST['ct_id'][$k];

    if(!$ct_id)
        continue;

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_id  = '$ct_id' ";
    $ct = sql_fetch($sql);
    if(!$ct['ct_id'])
        continue;

    // 수량이 변경됐다면
    $ct_qty = $_POST['ct_qty'][$k];
    if($ct['ct_qty'] != $ct_qty) {
        $diff_qty = $ct['ct_qty'] - $ct_qty;

        // 재고에 차이 반영.
        if($ct['ct_stock_use']) {
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
        }

        // 수량변경
        $sql = " update {$g5['g5_shop_cart_table']}
                    set ct_qty = '$ct_qty'
                    where ct_id = '$ct_id'
                      and od_id = '$od_id' ";
        sql_query($sql);
        $mod_history .= G5_TIME_YMDHIS.' '.$ct['ct_option'].' 수량변경 '.$ct['ct_qty'].' -> '.$ct_qty."\n";
    }

    // 재고를 이미 사용했다면 (재고에서 이미 뺐다면)
    $stock_use = $ct['ct_stock_use'];
    if ($ct['ct_stock_use'])
    {
        if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
        {
            $stock_use = 0;
            // 재고에 다시 더한다.
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
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
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
        }
        /* 주문 수정에서 "품절" 선택시 해당 상품 자동 품절 처리하기
        else if ($ct_status == '품절') {
            $stock_use = 1;
            // 재고에서 뺀다.
            $sql =" update {$g5['g5_shop_item_table']} set it_stock_qty = 0 where it_id = '{$ct['it_id']}' ";
            sql_query($sql);
        } */
    }

    $point_use = $ct['ct_point_use'];
    // 회원이면서 포인트가 0보다 크면
    // 이미 포인트를 부여했다면 뺀다.
    if ($mb_id && $ct['ct_point'] && $ct['ct_point_use'])
    {
        $point_use = 0;
        //insert_point($mb_id, (-1) * ($ct[ct_point] * $ct[ct_qty]), "주문번호 $od_id ($ct_id) 취소");
        delete_point($mb_id, "@delivery", $mb_id, "$od_id,$ct_id");
    }

    // 히스토리에 남김
    // 히스토리에 남길때는 작업|시간|IP|그리고 나머지 자료
    $now = G5_TIME_YMDHIS;
    $ct_history="\n$ct_status|$now|$REMOTE_ADDR";

    $sql = " update {$g5['g5_shop_cart_table']}
                set ct_point_use  = '$point_use',
                    ct_stock_use  = '$stock_use',
                    ct_status     = '$ct_status',
                    ct_history    = CONCAT(ct_history,'$ct_history')
                where od_id = '$od_id'
                and ct_id  = '$ct_id' ";
    sql_query($sql);
}

// 장바구니 상품 모두 취소일 경우 주문상태 변경
$cancel_change = false;
if (in_array($_POST['ct_status'], $status_cancel)) {
    $sql = " select count(*) as od_count1,
                    SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '환불', 1, 0)) as od_count2
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id' ";
    $row = sql_fetch($sql);

    if($row['od_count1'] == $row['od_count2']) {
        $cancel_change = true;

        // 관리자 주문취소 로그
        $mod_history .= G5_TIME_YMDHIS.' '.$member['mb_id'].' 주문'.$_POST['ct_status'].' 처리'."\n";
    }
}

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
    if (in_array($_POST['ct_status'], $status_normal)) { // 정상인 주문상태만 기록
        $sql .= " , od_status = '{$_POST['ct_status']}' ";
    }
}

$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

$url = "./orderform.php?od_id=$od_id&amp;$qstr";

// 1.06.06
$od = sql_fetch(" select od_receipt_point from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
if ($od['od_receipt_point'])
    alert("포인트로 결제한 주문은,\\n\\n주문상태 변경으로 인해 포인트의 가감이 발생하는 경우\\n\\n회원관리 > 포인트관리에서 수작업으로 포인트를 맞추어 주셔야 합니다.", $url);
else
    goto_url($url);
?>
