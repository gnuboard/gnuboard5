<?php
define('IS_SUBSCRIPTION_ORDER_FORM', 1);
include_once('./_common.php');

// 정기구독은 회원만 구독이 가능합니다.
if (!$is_member) {
    goto_url(G5_BBS_URL . '/login.php?url=' . urlencode(G5_SUBSCRIPTION_URL . '/'. basename(__FILE__)));
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

// 주문상품 재고체크 js 파일
add_javascript('<script src="'.G5_JS_URL.'/subscription.order.js"></script>', 0);

$sw_direct = isset($_REQUEST['sw_direct']) ? preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['sw_direct']) : '';
$aparams_array = (isset($_REQUEST['aparams']) && isValidBase64($_REQUEST['aparams'])) ? unserialize(base64_decode($_REQUEST['aparams'])) : array('hope_delivery_date'=>'', 'delivery_cycle'=>'');

// if (isset($aparams_array['hope_delivery_date']) && $aparams_array['hope_delivery_date']) {
    
    $business_next_day = getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after'));
    
    if ($aparams_array['hope_delivery_date'] && isValidDate($aparams_array['hope_delivery_date'])) {
        
        if (strtotime($aparams_array['hope_delivery_date']) < strtotime($business_next_day)) {
            $aparams_array['hope_delivery_date'] = $business_next_day;
        }
        
    } else {
        $aparams_array['hope_delivery_date'] = $business_next_day;
    }

// }

// $aparams2 = base64_decode($_REQUEST['aparams']);

// Array ( [delivery_cycle] => 2||3||day [usage_count] => 0||4 [hope_delivery_date] => 0||4 )
// print_r($aparams2);
// exit;

// print_r($aparams_array);
// exit;

// 모바일 주문인지
$is_mobile_order = is_mobile();

set_session("subs_direct", $sw_direct);
// 장바구니가 비어있는가?
if ($sw_direct) {
    $tmp_cart_id = get_session('subs_cart_direct');
}
else {
    $tmp_cart_id = get_session('subs_cart_id');
}

if (get_subscription_cart_count($tmp_cart_id) == 0) {
    alert('장바구니가 비어 있습니다.', G5_SUBSCRIPTION_URL.'/cart.php');
}

if (function_exists('before_check_subscription_cart_price')) {
    if (!before_check_subscription_cart_price($tmp_cart_id)) {
        alert('장바구니 금액에 변동사항이 있습니다.\n장바구니를 다시 확인해 주세요.', G5_SUBSCRIPTION_URL.'/cart.php');
    }
}

// 새로운 주문번호 생성
$od_id = get_uniqid();
set_session('subs_order_id', $od_id);
$s_cart_id = $tmp_cart_id;

$tot_price = 0;

// 연락처에 전화번호가 없을시 회원 전화번호로 정함
$member['mb_tel'] = (isset($member['mb_tel']) && $member['mb_tel']) ? $member['mb_tel'] : $member['mb_hp'];

if (get_subs_option('su_pg_service') == 'nicepay') {
    add_javascript('<script src="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.js"></script>', 10);
    add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.css">', 10);
}

$g5['title'] = '정기구독 주문서 작성';

if(G5_IS_MOBILE)
    include_once(G5_MSUBSCRIPTION_PATH.'/_head.php');
else
    include_once(G5_SUBSCRIPTION_PATH.'/_head.php');

// 희망배송일 지정
if (get_subs_option('su_hope_date_use')) {
    include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
}

// 기기별 주문폼 include
if($is_mobile_order) {
    $order_action_url = G5_HTTPS_MSUBSCRIPTION_URL.'/orderformupdate.php';
    require_once(G5_MSUBSCRIPTION_PATH.'/orderform.sub.php');
} else {
    $order_action_url = G5_HTTPS_SUBSCRIPTION_URL.'/orderformupdate.php';
    require_once(G5_SUBSCRIPTION_PATH.'/orderform.sub.php');
}

if(G5_IS_MOBILE)
    include_once(G5_MSUBSCRIPTION_PATH.'/_tail.php');
else
    include_once(G5_SUBSCRIPTION_PATH.'/_tail.php');