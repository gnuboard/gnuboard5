<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once('./admin.shop.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

check_admin_token();

define("_ORDERMAIL_", true);

//print_r2($_POST); exit;

$sms_count = 0;
$sms_messages = array();

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

                    if($receive_number)
                        $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_contents);
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

                    if($receive_number)
                        $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_contents);
                }
            }

            // 메일
            if($config['cf_email_use'] && $_POST['od_send_mail'])
                include './ordermail.inc.php';

            // 에스크로 배송
            if($_POST['send_escrow'] && $od['od_tno'] && $od['od_escrow']) {
                $escrow_tno  = $od['od_tno'];
                $escrow_numb = $invoice;
                $escrow_corp = $delivery_company;

                include(G5_SHOP_PATH.'/'.$od['od_pg'].'/escrow.register.php');
            }

            break;

        case '배송' :
            if ($change_status != '완료') continue;
            change_status($od_id, '배송', '완료');

            // 완료인 경우에만 상품구입 합계수량을 상품테이블에 저장한다.
            $sql2 = " select it_id from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_status = '완료' group by it_id ";
            $result2 = sql_query($sql2);
            for ($k=0; $row2=sql_fetch_array($result2); $k++) {
                $sql3 = " select sum(ct_qty) as sum_qty from {$g5['g5_shop_cart_table']} where it_id = '{$row2['it_id']}' and ct_status = '완료' ";
                $row3 = sql_fetch($sql3);

                $sql4 = " update {$g5['g5_shop_item_table']} set it_sum_qty = '{$row3['sum_qty']}' where it_id = '{$row2['it_id']}' ";
                sql_query($sql4);
            }
            /*
            $sql2 = " select it_id, sum(ct_qty) as sum_qty from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_status = '완료' group by it_id ";
            $result2 = sql_query($sql2);
            for ($k=0; $row2=sql_fetch_array($result2); $k++) {
                $sql3 = " update {$g5['g5_shop_item_table']} set it_sum_qty = it_sum_qty + '{$row2['sum_qty']}' where it_id = '{$row2['it_id']}' ";
                sql_query($sql3);
            }
            */
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
$sms_count = count($sms_messages);
if($sms_count > 0) {
    if($config['cf_sms_type'] == 'LMS') {
        include_once(G5_LIB_PATH.'/icode.lms.lib.php');

        $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

        // SMS 모듈 클래스 생성
        if($port_setting !== false) {
            $SMS = new LMS;
            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

            for($s=0; $s<$sms_count; $s++) {
                $strDest     = array();
                $strDest[]   = $sms_messages[$s]['recv'];
                $strCallBack = $sms_messages[$s]['send'];
                $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                $strSubject  = '';
                $strURL      = '';
                $strData     = iconv_euckr($sms_messages[$s]['cont']);
                $strDate     = '';
                $nCount      = count($strDest);

                $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        }
    } else {
        include_once(G5_LIB_PATH.'/icode.sms.lib.php');

        $SMS = new SMS; // SMS 연결
        $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

        for($s=0; $s<$sms_count; $s++) {
            $recv_number = $sms_messages[$s]['recv'];
            $send_number = $sms_messages[$s]['send'];
            $sms_content = iconv_euckr($sms_messages[$s]['cont']);

            $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
        }

        $SMS->Send();
        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
    }
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