<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G4_LIB_PATH.'/mailer.lib.php');
include_once(G4_LIB_PATH.'/icode.sms.lib.php');

auth_check($auth[$sub_menu], "w");

if ($od_receipt_time)
{
    if (check_datetime($od_receipt_time) == false)
        alert('결제일시 오류입니다.');
}

$sql = " update {$g4['shop_order_table']}
            set od_deposit_name    = '$od_deposit_name',
                od_bank_account    = '$od_bank_account',
                od_receipt_time    = '$od_receipt_time',
                od_receipt_amount  = '$od_receipt_amount',
                od_receipt_point   = '$od_receipt_point',
                od_cancel_card     = '$od_cancel_card',
                od_dc_amount       = '$od_dc_amount',
                od_refund_amount   = '$od_refund_amount',
                dl_id              = '$dl_id',
                od_invoice         = '$od_invoice',
                od_invoice_time    = '$od_invoice_time' ";
if (isset($od_send_cost))
    $sql .= " , od_send_cost = '$od_send_cost' ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);


// 메일발송
define("_ORDERMAIL_", true);
include "./ordermail.inc.php";


// SMS 문자전송
define("_ORDERSMS_", true);
include "./ordersms.inc.php";


$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
?>
