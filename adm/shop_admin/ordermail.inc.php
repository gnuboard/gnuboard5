<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 제대로된 include 시에만 실행
if (!defined("_ORDERMAIL_")) exit;

// 주문자님께 메일발송 체크를 했다면
if ($od_send_mail)
{
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

    $addmemo = nl2br(stripslashes($addmemo));

    unset($cart_list);
    unset($card_list);
    unset($bank_list);
    unset($point_list);
    unset($delivery_list);

    $sql = " select *
               from {$g5['g5_shop_cart_table']}
              where od_id = '{$od['od_id']}'
              order by ct_id ";
    $result = sql_query($sql);
    for ($j=0; $ct=sql_fetch_array($result); $j++) {
        $cart_list[$j]['it_id']   = $ct['it_id'];
        $cart_list[$j]['it_name'] = $ct['it_name'];
        $cart_list[$j]['it_opt']  = $ct['ct_option'];

        $ct_status = $ct['ct_status'];
        if ($ct_status == "준비") {
            $ct_status = "상품준비중";
        } else if ($ct_status == "배송") {
            $ct_status = "배송중";
        }

        $cart_list[$j]['ct_status'] = $ct_status;
        $cart_list[$j]['ct_qty']    = $ct['ct_qty'];
    }


    /*
    ** 입금정보
    */
    $is_receipt = false;

    // 신용카드 입금
    if ($od['od_receipt_price'] > 0 && $od['od_settle_case'] == '신용카드') {
        $card_list['od_receipt_time'] = $od['od_receipt_time'];
        $card_list['od_receipt_price'] = display_price($od['od_receipt_price']);

        $is_receipt = true;
    }

    // 무통장 입금
    if ($od['od_receipt_price'] > 0 && $od['od_settle_case'] == '무통장') {
        $bank_list['od_receipt_time']    = $od['od_receipt_time'];
        $bank_list['od_receipt_price'] = display_price($od['od_receipt_price']);
        $bank_list['od_deposit_name'] = $od['od_deposit_name'];

        $is_receipt = true;
    }

    // 포인트 입금
    if ($od['od_receipt_point'] > 0) {
        $point_list['od_time']          = $od['od_time'];
        $point_list['od_receipt_point'] = display_point($od['od_receipt_point']);

        $is_receipt = true;
    }

    // 배송정보
    $is_delivery = false;
    if ($od['od_delivery_company'] && $od['od_invoice']) {
        $delivery_list['dl_company']      = $od['od_delivery_company'];
        $delivery_list['od_invoice']      = $od['od_invoice'];
        $delivery_list['od_invoice_time'] = $od['od_invoice_time'];
        $delivery_list['dl_inquiry']      = get_delivery_inquiry($od['od_delivery_company'], $od['od_invoice'], 'dvr_link');

        $is_delivery = true;
    }

    // 입금 또는 배송내역이 있다면 메일 발송
    if ($is_receipt || $is_delivery)
    {
        ob_start();
        include G5_SHOP_PATH.'/mail/ordermail.mail.php';
        $content = ob_get_contents();
        ob_end_clean();

        $title = $config['cf_title'].' - '.$od['od_name'].'님 주문 처리 내역 안내';
        $email = $od['od_email'];

        // 메일 보낸 내역 상점메모에 update
        $od_shop_memo = G5_TIME_YMDHIS.' - 결제/배송내역 메일발송\n' . $od['od_shop_memo'];
        /* 1.00.06
        ** 주석처리 - 처리하지 않음
        if ($receipt_check)
            $od_shop_memo .= ", 입금확인";
        if ($invoice_check)
            $od_shop_memo .= ", 송장번호";
        */

        sql_query(" update {$g5['g5_shop_order_table']} set od_shop_memo = '$od_shop_memo' where od_id = '$od_id' ");

        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $email, $title, $content, 1);
    }
}
?>
