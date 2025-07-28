<?php
include_once('./_common.php');

$pay_id = isset($_REQUEST['pay_id']) ? safe_replace_regex($_REQUEST['pay_id'], 'pay_id') : '';

if (!$pay_id) {
    die('');
}

if (!$is_member) {
    die(json_encode(array('error' => 1, 'msg' => '회원만 조회가 가능합니다.')));
}

$sql = "SELECT * 
        FROM {$g5['g5_subscription_pay_table']} 
        WHERE pay_id = '" . $pay_id . "'";

if ($is_member && !$is_admin) {
    $sql .= " AND mb_id = '" . $member['mb_id'] . "'";
}

$pays = sql_fetch($sql);

if (!(isset($pays['pay_id']) && $pays['pay_id'])) {

    die(json_encode(array('error' => 1, 'msg' => '조회 권한이 없습니다.')));
}

$pays['py_b_full_address'] = get_text(sprintf("(%s)", $pays['py_b_zip']) . ' ' . print_address($pays['py_b_addr1'], $pays['py_b_addr2'], $pays['py_b_addr3'], $pays['py_b_addr_jibeon']));
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
} else if ($pays['py_pg'] == 'inicis') {

    $pays['py_receipt_url'] = 'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid='.$pays['py_tno'].'&noMethod=1';
} else if ($pays['py_pg'] == 'nicepay') {

    // https://developers.nicepay.co.kr/receipt.php
    $pays['py_receipt_url'] = 'https://npg.nicepay.co.kr/issue/IssueLoader.do?type=0&TID=' . $pays['py_tno'];
}

$cards = get_customer_card_info($pays);

if ($cards) {
    $pays['card_txt'] = $cards['od_card_name'] . ' ('.$cards['card_mask_number'].')';
} else {
    $pays['card_txt'] = '카드정보가 삭제 되었거나 카드정보가 없습니다.';
}

// 결제 장바구니
$pays['cart_infos'] = get_subscription_cart_data($pays['od_id']);

header('Content-Type: application/json');
echo json_encode($pays, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

die('');

// die(json_encode($pays));