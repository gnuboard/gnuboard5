<?php
$sub_menu = '600500';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$pay_id = isset($_REQUEST['pay_id']) ? safe_replace_regex($_REQUEST['pay_id'], 'pay_id') : '';
$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';

if (!($pay_id || $od_id)) {
    die('');
}

if (!$is_member) {
    die(json_encode(array('error' => 1, 'msg' => '회원만 조회가 가능합니다.')));
}

$prints = array();

// 정기결제내역 
if ($pay_id) {

    $pays = get_subscription_pay($pay_id);

    if (!(isset($pays['pay_id']) && $pays['pay_id'])) {

        die(json_encode(array('error' => 1, 'msg' => '정기결제내역이 존재하지 않습니다.')));
    }

    $pays['py_b_full_address'] = get_text($pays['py_b_zip'] . ' ' . print_address($pays['py_b_addr1'], $pays['py_b_addr2'], $pays['py_b_addr3'], $pays['py_b_addr_jibeon']));
    $pays['py_delivery_full_info'] = $pays['py_delivery_company'] . ' ' . get_delivery_inquiry($pays['py_delivery_company'], $pays['py_invoice'], 'dvr_link');

    // 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인 - 배송비할인
    $tot_price = $pays['py_cart_price'] + $pays['py_send_cost'] + $pays['py_send_cost2']
        - $pays['py_cart_coupon'] - $pays['py_coupon'] - $pays['py_send_coupon']
        - $pays['py_cancel_price'];

    $pays['py_tot_price'] = $tot_price;

    // 영수증

    $pays['py_receipt_url'] = '';

    if ($pays['py_pg'] == 'kcp') {
        $pays['py_receipt_url'] = G5_SUBSCRIPTION_KCP_BILL_RECEIPT_URL . 'card_bill&tno=' . $pays['py_tno'] . '&order_no=' . $pays['subscription_pg_id'] . '&trade_mony=' . (int) $pays['py_receipt_price'];
    } else if ($pays['py_pg'] == 'nicepay') {

        // https://developers.nicepay.co.kr/receipt.php
        $pays['py_receipt_url'] = 'https://npg.nicepay.co.kr/issue/IssueLoader.do?type=0&TID=' . $pays['py_tno'];
    }

    // 결제 장바구니
    $pays['cart_infos'] = get_subscription_cart_data($pays['od_id']);

    $prints = $pays;
} elseif ($od_id) {

    // 구독내역
    $od = get_subscription_order($od_id);

    if (!(isset($od['od_id']) && $od['od_id'])) {

        die(json_encode(array('error' => 1, 'msg' => '구독내역이 존재하지 않습니다.')));
    }

    $od['od_b_full_address'] = get_text($od['od_b_zip'] . ' ' . print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon']));
    // $od['od_delivery_full_info'] = $od['od_delivery_company'].' '.get_delivery_inquiry($od['od_delivery_company'], $od['od_invoice'], 'dvr_link');

    // 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인 - 배송비할인
    $tot_price = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']
        - $od['od_cart_coupon'] - $od['od_coupon'] - $od['od_send_coupon'];

    $od['od_tot_price'] = $tot_price;

    // 결제 장바구니
    $od['cart_infos'] = get_subscription_cart_data($od['od_id']);

    $prints = $od;
}

header('Content-Type: application/json');
echo json_encode($prints, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
