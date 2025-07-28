<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$ct_chk_count = isset($_POST['ct_chk']) ? count($_POST['ct_chk']) : 0;

$status_normal = array('활성화', '비활성화');

if (in_array($_POST['ct_status'], $status_normal)) {; // 통과

} else {
    if (!$ct_chk_count) {
        alert('처리할 자료를 하나 이상 선택해 주십시오.');
    }
    // alert('변경할 상태가 올바르지 않습니다.');
}

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$mod_history = '';
$cnt = (isset($_POST['ct_id']) && is_array($_POST['ct_id'])) ? count($_POST['ct_id']) : 0;
$arr_it_id = array();
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

$od = get_subscription_order($od_id);

if (!(isset($od['od_id']) && $od['od_id'])) {
    alert('주문자료가 존재하지 않습니다.');
}

for ($i = 0; $i < $cnt; $i++) {
    $k = isset($_POST['ct_chk'][$i]) ? (int) $_POST['ct_chk'][$i] : '';

    if ($k === '') continue;

    $ct_id = isset($_POST['ct_id'][$k]) ? (int) $_POST['ct_id'][$k] : 0;

    if (!$ct_id)
        continue;

    $sql = "SELECT * 
        FROM {$g5['g5_subscription_cart_table']} 
        WHERE od_id = '$od_id' 
        AND ct_id = '$ct_id'";
    $ct = sql_fetch($sql);

    if (! (isset($ct['ct_id']) && $ct['ct_id']))
        continue;

    // 수량이 변경됐다면
    $ct_qty = isset($_POST['ct_qty'][$k]) ? (int) $_POST['ct_qty'][$k] : 0;
    if ($ct['ct_qty'] != $ct_qty) {
        $diff_qty = $ct['ct_qty'] - $ct_qty;

        // 재고에 차이 반영.
        if ($ct['ct_stock_use']) {
            if ($ct['io_id']) {

                $sql = "UPDATE {$g5['g5_shop_item_option_table']} 
                        SET io_stock_qty = io_stock_qty + '$diff_qty' 
                        WHERE it_id = '" . $ct['it_id'] . "' 
                        AND io_id = '" . $ct['io_id'] . "' 
                        AND io_type = '" . $ct['io_type'] . "'";
            } else {

                $sql = "UPDATE {$g5['g5_shop_item_table']} 
                        SET it_stock_qty = it_stock_qty + '$diff_qty' 
                        WHERE it_id = '" . $ct['it_id'] . "'";
            }

            sql_query($sql);
        }

        // 수량변경
        $sql = " update {$g5['g5_subscription_cart_table']}
                    set ct_qty = '$ct_qty'
                    where ct_id = '$ct_id'
                      and od_id = '$od_id' ";
        sql_query($sql);

        $mod_history .= G5_TIME_YMDHIS . ' ' . $ct['ct_option'] . ' 수량변경 ' . $ct['ct_qty'] . ' -> ' . $ct_qty . "\n";
    }
}

$sql = "UPDATE {$g5['g5_subscription_cart_table']} 
        SET od_enable_status = '" . (($ct_status === '비활성화') ? 0 : 1) . "' 
        WHERE od_id = '$od_id'";
sql_query($sql);

if ($ct_status === '활성화') {
    
    $sql = " update {$g5['g5_subscription_cart_table']}
                set ct_status = '주문'
                where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' ";
    sql_query($sql);
    
} else if ($ct_status === '비활성화') {

    $sql = " update {$g5['g5_subscription_cart_table']}
                set ct_status = '취소'
                where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' ";
    sql_query($sql);
    
}

$message = ($ct_status === '비활성화') ? '구독주문이 비활성화 되었습니다.' : '구독주문이 활성화 되었습니다.';

add_subscription_order_history($message, array(
    'hs_type' => ($ct_status === '비활성화') ? 'subscription_disable_order' : 'subscription_enable_order',
    'od_id' => $od_id,
    'mb_id' => $member['mb_id']
));

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

$url = "./orderform.php?od_id=$od_id&amp;$qstr";

goto_url($url);
