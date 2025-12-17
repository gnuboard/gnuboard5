<?php
include_once('./_common.php');

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';

if (!$od_id) {
    goto_url(G5_SHOP_URL);
}

if (!$is_member) {
    alert("로그인 후에 이용해 주세요.", G5_BBS_URL . "/login.php?url=" . urlencode(G5_SUBSCRIPTION_URL . "/subscription_list.php"));
}

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    set_session("ss_token", "");
    alert("토큰 에러", G5_SHOP_URL);
}

// 해당 회원만 조회가 가능하다.
$od = get_subscription_order($od_id, 1);

if (!(isset($od['od_id']) && $od['od_id'])) {
    alert("존재하는 주문이 아닙니다.");
}

if ($od['mb_id'] !== $member['mb_id']) {
    alert("권한이 없습니다.");
}

// 정기구독정보의 배송주기와 이용횟수 등을 가져옴
$crp = calculateRecurringPaymentDetails($od);

// 정기구독이 진행중이명 0, 끝났으면 1
$is_end_subscription = $crp['is_end_subscription'];

if ($is_end_subscription) {
    alert('정기구독이 종료되어 취소할수 없습니다.');
}

// 주문 취소
$cancel_memo = addslashes(strip_tags($cancel_memo));
$cancel_price = $od['od_cart_price'];

// 장바구니 자료 취소
$sql = "UPDATE {$g5['g5_subscription_cart_table']} 
        SET ct_status = '취소' 
        WHERE od_id = '" . $od['od_id'] . "' 
        AND mb_id = '" . $member['mb_id'] . "'";
$result = sql_query($sql);

// 주문 비활성화
$sql = "UPDATE {$g5['g5_subscription_order_table']} 
        SET od_enable_status = '0', 
            od_subscription_memo = CONCAT(od_subscription_memo, '\n주문자 본인 직접 취소 - " . G5_TIME_YMDHIS . " (취소이유 : $cancel_memo)') 
        WHERE od_id = '" . $od['od_id'] . "' 
        AND mb_id = '" . $member['mb_id'] . "'";
$result = sql_query($sql);

add_subscription_order_history('주문자 본인이 직접 구독을 취소했습니다.', array(
    'hs_type' => 'subscription_member_cancel_order',
    'hs_category' => 'user',
    'od_id' => $od['od_id'],
    'mb_id' => $od['mb_id']
));

goto_url(G5_SUBSCRIPTION_URL . "/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
