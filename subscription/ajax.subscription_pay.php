<?php
include_once('./_common.php');

$pay_id = isset($_REQUEST['pay_id']) ? safe_replace_regex($_REQUEST['pay_id'], 'pay_id') : '';

if (!$pay_id) {
    die('');
}

if (!$is_member) {
    die(json_encode(array('error' => 1, 'msg'=>'회원만 조회가 가능합니다.')));
}

$sql_wheres = array('id' => $pay_id);

if ($is_member && !$is_admin) {
    $sql_wheres['mb_id'] = $member['mb_id'];
}

$pays = sql_bind_select_fetch($g5['g5_subscription_pay_table'], '*', $sql_wheres);

if (!(isset($pays['id']) && $pays['id'])) {

    die(json_encode(array('error' => 1, 'msg'=>'조회 권한이 없습니다.')));
    
}

$pays['py_b_full_address'] = get_text(sprintf("(%s%s)", $pays['py_b_zip1'], $pays['py_b_zip2']).' '.print_address($pays['py_b_addr1'], $pays['py_b_addr2'], $pays['py_b_addr3'], $pays['py_b_addr_jibeon']));
$pays['py_delivery_full_info'] = $pays['py_delivery_company'].' '.get_delivery_inquiry($pays['py_delivery_company'], $pays['py_invoice'], 'dvr_link');

// 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인 - 배송비할인
$tot_price = $pays['py_cart_price'] + $pays['py_send_cost'] + $pays['py_send_cost2']
                - $pays['py_cart_coupon'] - $pays['py_coupon'] - $pays['py_send_coupon']
                - $pays['py_cancel_price'];

$pays['py_tot_price'] = $tot_price;

// 영수증

$pays['py_receipt_url'] = '';

if ($pays['py_pg'] == 'kcp') {
    $pays['py_receipt_url'] = G5_SUBSCRIPTION_KCP_BILL_RECEIPT_URL.'card_bill&tno='.$pays['py_tno'].'&order_no='.$pays['subscription_id'].'&trade_mony='.(int) $pays['py_receipt_price'];
} else if ($pays['py_pg'] == 'nicepay') {
    
    // https://developers.nicepay.co.kr/receipt.php
    $pays['py_receipt_url'] = 'https://npg.nicepay.co.kr/issue/IssueLoader.do?type=0&TID='.$pays['py_tno'];
}

// 결제 장바구니
$pays['cart_infos'] = get_subscription_cart_data($pays['od_id']);

die(json_encode($pays));