<?php
$sub_menu = '600410';
include_once('./_common.php');
include_once('../shop_admin/admin.shop.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

// print_r2($_POST);
// exit;

auth_check_menu($auth, $sub_menu, "w");

// check_admin_token();

$pay_id = isset($_POST['pay_id']) ? preg_replace('[^0-9]', '', $_POST['pay_id']) : '';

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$check_keys = array(
'py_deposit_name',
'py_bank_account',
'py_receipt_time',
'py_receipt_price',
'py_receipt_point',
'py_refund_price',
'py_delivery_company',
'py_invoice',
'py_invoice_time',
'py_send_cost',
'py_send_cost2',
'py_tno',
'py_escrow',
'py_send_mail',
'next_delivery_date'
);

$posts = array();

foreach($check_keys as $key){
    $posts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
}

$py_send_mail = $posts['py_send_mail'];

$pay = get_subscription_pay($pay_id);

if (!(isset($pay['pay_id']) && $pay['pay_id'])) {
    alert('정기구독 주문자료가 존재하지 않습니다.');
}

if ($posts['py_receipt_time']) {
    if (check_datetime($posts['py_receipt_time']) == false)
        alert('결제일시 오류입니다.');
}

// 결제정보 반영
$sql = " update {$g5['g5_subscription_pay_table']}
            set py_bank_account    = '{$posts['py_bank_account']}',
                py_receipt_time    = '{$posts['py_receipt_time']}',
                py_receipt_price   = '{$posts['py_receipt_price']}',
                py_receipt_point   = '{$posts['py_receipt_point']}',
                py_refund_price    = '{$posts['py_refund_price']}',
                py_delivery_company= '{$posts['py_delivery_company']}',
                py_invoice         = '{$posts['py_invoice']}',
                py_invoice_time    = '{$posts['py_invoice_time']}',
                py_send_cost       = '{$posts['py_send_cost']}',
                py_send_cost2      = '{$posts['py_send_cost2']}',
                next_delivery_date = '{$posts['next_delivery_date']}'
            where pay_id = '$pay_id' ";
            
sql_query($sql);

$py_status = $op['py_status'];
$cart_status = false;

// 배송정보가 있으면 주문상태 배송으로 변경
$order_status = array('입금');
if($posts['py_delivery_company'] && $posts['py_invoice'] && in_array($od['py_status'], $order_status))
{
    $py_status = '배송';
    $cart_status = true;
}

// 미수금액
$py_misu = ( $od['py_cart_price'] - $od['py_cancel_price'] + (int) $posts['py_send_cost'] + (int) $posts['py_send_cost2'] )
           - ( $od['py_cart_coupon'] + $od['py_coupon'] + $od['py_send_coupon'] )
           - ( (int) $posts['py_receipt_price'] + (int) $posts['py_receipt_point'] - (int) $posts['py_refund_price'] );

// 미수금 정보 등 반영
$sql = " update {$g5['g5_subscription_pay_table']}
            set py_misu         = '$py_misu',
                py_tax_mny      = '{$info['py_tax_mny']}',
                py_vat_mny      = '{$info['py_vat_mny']}',
                py_free_mny     = '{$info['py_free_mny']}',
                py_status       = '$py_status'
            where py_id = '$py_id' ";
sql_query($sql);

// 장바구니 상태 변경
if($cart_status) {
    $sql = " update {$g5['g5_subscription_pay_basket_table']}
                set ct_status = '$py_status'
                where pay_id = '$pay_id' ";
    
    switch($py_status) {

        case '배송':
            $sql .= " and ct_status IN ('".implode("', '", $order_status)."') ";
            break;
        default:
            ;
    }

    sql_query($sql);
}


// 배송때 재고반영
if($info['py_misu'] == 0 && $py_status == '배송') {
    $sql = " select * from {$g5['g5_subscription_pay_basket_table']} where pay_id = '$pay_id' ";
    $result = sql_query($sql);

    subscription_pay_process_stock($pay_id);

    unset($sql);
    unset($result);
    unset($row);
}


// 메일발송
/*
define("_ORDERMAIL_", true);
include "./ordermail.inc.php";


// SMS 문자전송
define("_ORDERSMS_", true);
include "./ordersms.inc.php";
*/

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./payform.php?pay_id=$pay_id&amp;$qstr");