<?php
include_once('./_common.php');

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    alert_close("토큰 에러");
}

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' and mb_id = '{$member['mb_id']}' ");

if (!$od['od_id']) {
    alert("존재하는 주문이 아닙니다.");
}

// 주문상품의 상태가 주문인지 체크
$sql = " select SUM(IF(ct_status = '주문', 1, 0)) as od_count2,
                COUNT(*) as od_count1
            from {$g5['g5_shop_cart_table']}
            where od_id = '$od_id' ";
$ct = sql_fetch($sql);

$uid = md5($od['od_id'].$od['od_time'].$od['od_ip']);

if($od['od_cancel_price'] > 0 || $ct['od_count1'] != $ct['od_count2']) {
    alert("취소할 수 있는 주문이 아닙니다.", G5_SHOP_URL."/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
}

// PG 결제 취소
if($od['od_tno']) {
    require './settle_kcp.inc.php';

    $_POST['tno'] = $od['od_tno'];
    $_POST['req_tx'] = 'mod';
    $_POST['mod_type'] = 'STSC';
    if($od['od_escrow']) {
        $_POST['req_tx'] = 'mod_escrow';
        $_POST['mod_type'] = 'STE2';
        if($od['od_settle_case'] == '가상계좌')
            $_POST['mod_type'] = 'STE5';
    }
    $_POST['mod_desc'] = iconv("utf-8", "euc-kr", '주문자 본인 취소-'.$cancel_memo);
    $_POST['site_cd'] = $default['de_kcp_mid'];

    // 취소내역 한글깨짐방지
    $def_locale = setlocale(LC_CTYPE, 0);
    $locale_change = false;
    if(preg_match("/utf[\-]?8/i", $def_locale)) {
        setlocale(LC_CTYPE, 'ko_KR.euc-kr');
        $locale_change = true;
    }

    include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';

    if($locale_change)
        setlocale(LC_CTYPE, $def_locale);
}

// 장바구니 자료 취소
sql_query(" update {$g5['g5_shop_cart_table']} set ct_status = '취소' where od_id = '$od_id' ");

// 주문 취소
$cancel_memo = addslashes($cancel_memo);
$cancel_price = $od['od_cart_price'];

$sql = " update {$g5['g5_shop_order_table']}
            set od_send_cost = '0',
                od_send_cost2 = '0',
                od_receipt_price = '0',
                od_receipt_point = '0',
                od_misu = '0',
                od_cancel_price = '$cancel_price',
                od_cart_coupon = '0',
                od_coupon = '0',
                od_send_coupon = '0',
                od_status = '취소',
                od_shop_memo = concat(od_shop_memo,\"\\n주문자 본인 직접 취소 - ".G5_TIME_YMDHIS." (취소이유 : {$cancel_memo})\")
            where od_id = '$od_id' ";
sql_query($sql);

// 주문취소 회원의 포인트를 되돌려 줌
if ($od['od_receipt_point'] > 0)
    insert_point($member['mb_id'], $od['od_receipt_point'], "주문번호 $od_id 본인 취소");

goto_url(G5_SHOP_URL."/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
?>