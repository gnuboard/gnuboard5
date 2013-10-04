<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/icode.sms.lib.php');

auth_check($auth[$sub_menu], "w");

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    alert('주문자료가 존재하지 않습니다.');

if ($od_receipt_time)
{
    if (check_datetime($od_receipt_time) == false)
        alert('결제일시 오류입니다.');
}

// 주문 합계
$sql = " select SUM( IF( ct_notax = 0, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as tax_mny,
                SUM( IF( ct_notax = 1, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as free_mny
            from {$g5['g5_shop_cart_table']}
            where od_id = '{$od['od_id']}'
              and ct_status IN ( '주문', '준비', '배송', '완료' ) ";
$sum = sql_fetch($sql);

$tax_mny = $sum['tax_mny'];
$free_mny = $sum['free_mny'];

// 과세, 비과세
if($od['od_tax_flag']) {
    $tot_tax_mny = ( $tax_mny + $od_send_cost + $od_send_cost2 )
                   - ( $od['od_coupon'] + $od['od_send_coupon'] + $od_receipt_point );
    if($tot_tax_mny < 0) {
        $free_mny += $tot_tax_mny;
        $tot_tax_mny = 0;
    }
} else {
    $tot_tax_mny = ( $tax_mny + $free_mny + $od_send_cost + $od_send_cost2 )
                   - ( $od['od_coupon'] + $od['od_send_coupon'] + $od_receipt_point );
    $free_mny = 0;
}

$od_tax_mny = round($tot_tax_mny / 1.1);
$od_vat_mny = $tot_tax_mny - $od_tax_mny;
$od_free_mny = $free_mny;

// 미수
$od_misu = ( $od['od_cart_price'] + $od_send_cost + $od_send_cost2 )
           - ( $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'] )
           - ( $od_receipt_price + $od_receipt_point - $od_refund_price );

$sql = " update {$g5['g5_shop_order_table']}
            set od_deposit_name    = '$od_deposit_name',
                od_bank_account    = '$od_bank_account',
                od_receipt_time    = '$od_receipt_time',
                od_receipt_price   = '$od_receipt_price',
                od_receipt_point   = '$od_receipt_point',
                od_refund_price    = '$od_refund_price',
                od_misu            = '$od_misu',
                od_tax_mny         = '$od_tax_mny',
                od_vat_mny         = '$od_vat_mny',
                od_free_mny        = '$od_free_mny',
                dl_id              = '$dl_id',
                od_invoice         = '$od_invoice',
                od_invoice_time    = '$od_invoice_time',
                od_send_cost       = '$od_send_cost',
                od_send_cost2      = '$od_send_cost2'
            where od_id = '$od_id' ";
sql_query($sql);


// 메일발송
define("_ORDERMAIL_", true);
include "./ordermail.inc.php";


// SMS 문자전송
define("_ORDERSMS_", true);
include "./ordersms.inc.php";


// 에스크로 배송처리
if($_POST['od_tno'] && $_POST['od_escrow'] == 1) {
    $arr_tno = array();
    $arr_corp = array();
    $arr_numb = array();

    // 배송회사정보
    $sql = " select dl_company from {$g5['g5_shop_delivery_table']} where dl_id = '$dl_id' ";
    $row = sql_fetch($sql);

    $arr_tno[0] = $_POST['od_tno'];
    $arr_corp[0] = $row['dl_company'];
    $arr_numb[0] = $od_invoice;
    $cust_ip = getenv('REMOTE_ADDR');

    include_once('./orderescrow.inc.php');
}


$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
?>
