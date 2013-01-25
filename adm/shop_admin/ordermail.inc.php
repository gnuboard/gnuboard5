<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 제대로된 include 시에만 실행
if (!defined("_ORDERMAIL_")) exit;

// 주문자님께 메일발송 체크를 했다면
if ($od_send_mail) 
{
    $od = sql_fetch(" select * from $g4[yc4_order_table] where od_id = '$od_id' ");

    $addmemo = nl2br(stripslashes($addmemo));

    unset($cart_list);
    unset($card_list);
    unset($bank_list);
    unset($point_list);
    unset($delivery_list);

    $sql = " select a.*,
                    b.it_name,
                    b.it_opt1_subject,
                    b.it_opt2_subject,
                    b.it_opt3_subject,
                    b.it_opt4_subject,
                    b.it_opt5_subject,
                    b.it_opt6_subject
               from $g4[yc4_cart_table] a inner join $g4[yc4_item_table] b on (b.it_id=a.it_id)
              where a.on_uid = '$od[on_uid]'
              order by a.ct_id ";
    $result = sql_query($sql);
    for ($i=0; $ct=mysql_fetch_array($result); $i++) {
        // 상품 옵션
        $s_option = "";
        $str_split = "";
        for ($k=1; $k<=6; $k++) {
            if ($ct["it_opt{$k}"] == "") {
                continue;
            }

            $s_option .= $str_split;
            $it_opt_subject = $ct["it_opt{$k}_subject"];

            unset($opt);
            $opt = explode( ";", trim($ct["it_opt{$k}"]) );
            $s_option .= "$it_opt_subject = $opt[0]";
            $str_split = "<br>";
        }

        if ($s_option == "") {
            $s_option = "없음";
        }

        $cart_list[$i][it_id]   = $ct[it_id];
        $cart_list[$i][it_name] = $ct[it_name];
        $cart_list[$i][it_opt]  = $s_option;

        $ct_status = $ct[ct_status];
        if ($ct_status == "준비") {
            $ct_status = "상품준비중";
        } else if ($ct_status == "배송") {
            $ct_status = "배송중";
        }

        $cart_list[$i][ct_status] = $ct_status;
        $cart_list[$i][ct_qty]    = $ct[ct_qty];
    }


    /*
    ** 입금정보
    */
    $is_receipt = false;

    // 신용카드 입금
    if ($od[od_receipt_card] > 0) {
        $card_list[od_card_time] = $od[od_card_time];
        $card_list[od_receipt_card] = display_amount($od[od_receipt_card]);

        $is_receipt = true;
    }

    // 무통장 입금
    if ($od[od_receipt_bank] > 0) {
        $bank_list[od_bank_time]    = $od[od_bank_time];
        $bank_list[od_receipt_bank] = display_amount($od[od_receipt_bank]);
        $bank_list[od_deposit_name] = $od[od_deposit_name];

        $is_receipt = true;
    }

    // 포인트 입금
    if ($od[od_receipt_point] > 0) {
        $point_list[od_time]          = $od[od_time];
        $point_list[od_receipt_point] = display_point($od[od_receipt_point]);

        $is_receipt = true;
    }

    // 배송정보
    $is_delivery = false;
    if ((int)$od[dl_id] > 0) {
        $dl = sql_fetch(" select * from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ");

        $delivery_list[dl_url]          = $dl[dl_url];
        if (strpos($delivery_list[dl_url], "=")) $delivery_list[dl_url] .= $od[od_invoice];
        $delivery_list[dl_company]      = $dl[dl_company];
        $delivery_list[dl_tel]          = $dl[dl_tel];
        $delivery_list[od_invoice]      = $od[od_invoice];
        $delivery_list[od_invoice_time] = $od[od_invoice_time];

        $is_delivery = true;
    }

    // 입금 또는 배송내역이 있다면 메일 발송
    if ($is_receipt || $is_delivery) 
    {
        ob_start();
        include "$g4[shop_path]/mail/ordermail.mail.php";
        $content = ob_get_contents();
        ob_end_clean();

        $title = "{$od[od_name]}님께서 주문하신 내역을 다음과 같이 처리하였습니다.";
        $email = $od[od_email];

        // 메일 보낸 내역 상점메모에 update
        $od_shop_memo = "$g4[time_ymdhis] - 결제/배송내역 메일발송\n" . $od[od_shop_memo];;
        /* 1.00.06 
        ** 주석처리 - 처리하지 않음
        if ($receipt_check)
            $od_shop_memo .= ", 입금확인";
        if ($invoice_check)
            $od_shop_memo .= ", 송장번호";
        */

        sql_query(" update $g4[yc4_order_table] set od_shop_memo = '$od_shop_memo' where od_id = '$od_id' ");

        $admin = get_admin('super');

        mailer($config[cf_title], $admin[mb_email], $email, $title, $content, 1);
    }
}
?>
