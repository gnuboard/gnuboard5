<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/icode.sms.lib.php');

auth_check($auth[$sub_menu], "w");

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od  = sql_fetch($sql);
if(!$od['od_id'])
    alert('주문자료가 존재하지 않습니다.');

if ($od_receipt_time) {
    if (check_datetime($od_receipt_time) == false)
        alert('결제일시 오류입니다.');
}

// 결제정보 반영
$sql = " update {$g5['g5_shop_order_table']}
            set od_deposit_name    = '{$_POST['od_deposit_name']}',
                od_bank_account    = '{$_POST['od_bank_account']}',
                od_receipt_time    = '{$_POST['od_receipt_time']}',
                od_receipt_price   = '{$_POST['od_receipt_price']}',
                od_receipt_point   = '{$_POST['od_receipt_point']}',
                od_refund_price    = '{$_POST['od_refund_price']}',
                od_delivery_company= '{$_POST['od_delivery_company']}',
                od_invoice         = '{$_POST['od_invoice']}',
                od_invoice_time    = '{$_POST['od_invoice_time']}',
                od_send_cost       = '{$_POST['od_send_cost']}',
                od_send_cost2      = '{$_POST['od_send_cost2']}'
            where od_id = '$od_id' ";
sql_query($sql);

// 주문정보
$info = get_order_info($od_id);
if(!$info)
    alert('주문자료가 존재하지 않습니다.');

// 미수금 정보 등 반영
$sql = " update {$g5['g5_shop_order_table']}
            set od_misu         = '{$info['od_misu']}',
                od_tax_mny      = '{$info['od_tax_mny']}',
                od_vat_mny      = '{$info['od_vat_mny']}',
                od_free_mny     = '{$info['od_free_mny']}',
                od_send_cost    = '{$info['od_send_cost']}'
            where od_id = '$od_id' ";
sql_query($sql);


// 메일발송
define("_ORDERMAIL_", true);
include "./ordermail.inc.php";


// SMS 문자전송
define("_ORDERSMS_", true);
include "./ordersms.inc.php";


// 에스크로 배송처리
if($_POST['od_tno'] && $_POST['od_escrow'] == 1) 
{
    $arr_tno  = array();
    $arr_corp = array();
    $arr_numb = array();

    /*
    // 배송회사정보
    $sql = " select dl_company from {$g5['g5_shop_delivery_table']} where dl_id = '$dl_id' ";
    $row = sql_fetch($sql);

    $arr_tno[0]  = $_POST['od_tno'];
    $arr_corp[0] = $row['dl_company'];
    $arr_numb[0] = $od_invoice;
    $cust_ip = getenv('REMOTE_ADDR');
    */

    $arr_tno[0]  = $_POST['od_tno'];
    $arr_corp[0] = $od_delivery_company;
    $arr_numb[0] = $od_invoice;
    $cust_ip = getenv('REMOTE_ADDR');

    include_once('./orderescrow.inc.php');
}


$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
?>
