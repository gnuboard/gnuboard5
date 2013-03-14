<?
include_once("./_common.php");

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    alert_close("토큰 에러");
}

$od = sql_fetch(" select * from $g4[yc4_order_table] where od_id = '$od_id' and on_uid = '$on_uid' and mb_id = '$member[mb_id]' ");

if (!$od[od_id]) {
    alert("존재하는 주문이 아닙니다.");
}

if (($od[od_temp_bank] > 0 && $od[od_receipt_bank] == 0) || 
    ($od[od_temp_card] > 0 && $od[od_receipt_card] == 0)) {
    ;
} else {
    alert("취소할 수 있는 주문이 아닙니다.");
}

// 장바구니 자료 취소
sql_query(" update $g4[yc4_cart_table] set ct_status = '취소' where on_uid = '$on_uid' ");

// 주문 취소
$cancel_memo = addslashes($cancel_memo);
//sql_query(" update $g4[yc4_order_table] set od_temp_point = '0', od_receipt_point = '0', od_shop_memo = concat(od_shop_memo,\"\\n주문자 본인 직접 취소 - {$g4['time_ymdhis']} (취소이유 : {$cancel_memo})\") where on_uid = '$on_uid' ");
sql_query(" update $g4[yc4_order_table] set od_send_cost = '0', od_temp_point = '0', od_receipt_point = '0', od_shop_memo = concat(od_shop_memo,\"\\n주문자 본인 직접 취소 - {$g4['time_ymdhis']} (취소이유 : {$cancel_memo})\") where on_uid = '$on_uid' ");

// 주문취소 회원의 포인트를 되돌려 줌
if ($od[od_receipt_point] > 0) {
    insert_point($member[mb_id], $od[od_receipt_point], "주문번호 $od_id 본인 취소");
}

goto_url("./orderinquiryview.php?od_id=$od_id&on_uid=$on_uid");
?>