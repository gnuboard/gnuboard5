<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/icode.sms.lib.php');

define("_ORDERMAIL_", true);

//print_r2($_POST); exit;

$sms_count = 0;
if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'])
{
    $SMS = new SMS;
	$SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
}

$escrow_count = 0;
if($_POST['send_escrow']) {
    $escrow_tno  = array();
    $escrow_corp = array();
    $escrow_numb = array();
    $escrow_idx  = 0;
}

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
        $sms_contents = preg_replace("/{이름}/", $od['od_name'], $sms_contents);
        $sms_contents = preg_replace("/{입금액}/", number_format($od['od_receipt_price']), $sms_contents);
        $sms_contents = preg_replace("/{택배회사}/", $od['od_delivery_company'], $sms_contents);
        $sms_contents = preg_replace("/{운송장번호}/", $od['od_invoice'], $sms_contents);
        $sms_contents = preg_replace("/{주문번호}/", $od['od_id'], $sms_contents);
        $sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);
    }

    return iconv("utf-8", "euc-kr", stripslashes($sms_contents));
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

            // SMS
            if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'] && $default['de_sms_use4']) {
                $sms_contents = conv_sms_contents($od_id, $default['de_sms_cont4']);
                if($sms_contents) {
                    $receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']);	// 수신자번호
                    $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

                    if($receive_number && $send_number) {
                        $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], $sms_contents, "");
                        $sms_count++;
                    }
                }
            }

            // 메일
            if($config['cf_email_use'] && $_POST['od_send_mail'])
                include './ordermail.inc.php';

            break;

        case '입금' :
            if ($change_status != '준비') continue;
            change_status($od_id, '입금', '준비');
            break;

        case '준비' :
            if ($change_status != '배송') continue;

            $delivery['invoice'] = $invoice;
            $delivery['invoice_time'] = $invoice_time;
            $delivery['delivery_company'] = $delivery_company;

            order_update_delivery($od_id, $od['mb_id'], $change_status, $delivery);
            change_status($od_id, '준비', '배송');

            // SMS
            if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'] && $default['de_sms_use5']) {
                $sms_contents = conv_sms_contents($od_id, $default['de_sms_cont5']);
                if($sms_contents) {
                    $receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']);	// 수신자번호
                    $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

                    if($receive_number && $send_number) {
                        $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], $sms_contents, "");
                        $sms_count++;
                    }
                }
            }

            // 메일
            if($config['cf_email_use'] && $_POST['od_send_mail'])
                include './ordermail.inc.php';

            // 에스크로 배송
            if($_POST['send_escrow'] && $od['od_tno'] && $od['od_escrow']) {
                $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

                $escrow_tno[$escrow_idx]  = $od['od_tno'];
                $escrow_numb[$escrow_idx] = $od['od_invoice'];
                $escrow_corp[$escrow_idx] = $od['od_delivery_company'];
                $escrow_idx++;
                $escrow_count++;
            }

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

// SMS
if($config['cf_sms_use'] == 'icode' && $_POST['send_sms'] && $sms_count)
{
    $SMS->Send();
}

// 에스크로 배송
if($_POST['send_escrow'] && $escrow_count)
{
    include_once('./orderescrow.inc.php');
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