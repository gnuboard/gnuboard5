<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 시작
//------------------------------------------------------------------------------

define('G5_SHOP_DIR', 'shop');

define('G5_SHOP_PATH',  G5_PATH.'/'.G5_SHOP_DIR);
define('G5_SHOP_URL',   G5_URL.'/'.G5_SHOP_DIR);
define('G5_MSHOP_PATH', G5_MOBILE_PATH.'/'.G5_SHOP_DIR);
define('G5_MSHOP_URL',  G5_MOBILE_URL.'/'.G5_SHOP_DIR);

// 보안서버주소 설정
if (G5_HTTPS_DOMAIN) {
    define('G5_HTTPS_SHOP_URL', G5_HTTPS_DOMAIN.'/'.G5_SHOP_DIR);
    define('G5_HTTPS_MSHOP_URL', G5_HTTPS_DOMAIN.'/'.G5_MOBILE_DIR.'/'.G5_SHOP_DIR);
} else {
    define('G5_HTTPS_SHOP_URL', G5_SHOP_URL);
    define('G5_HTTPS_MSHOP_URL', G5_MSHOP_URL);
}

//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 끝
//------------------------------------------------------------------------------


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================

// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g5['g5_shop_default_table']} ");

define('G5_SHOP_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
define('G5_SHOP_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
define('G5_MSHOP_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);
define('G5_MSHOP_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);

/*
// 주문상태 상수
define('G5_OD_STATUS_ORDER'     , '입금확인중');
define('G5_OD_STATUS_SETTLE'    , '결제완료');
define('G5_OD_STATUS_READY'     , '배송준비중');
define('G5_OD_STATUS_DELIVERY'  , '배송중');
define('G5_OD_STATUS_FINISH'    , '배송완료');
*/

/*
# 주문상태는 상수로 처리하지 않고 실제 문자열 값을 처리한다.

'쇼핑'          : 고객이 장바구니에 상품을 담고 있는 경우 입니다.
'입금확인중'    : 무통장, 가상계좌의 경우 결제하기 전을 말합니다.
'결제완료'      : 결제가 완료된 상태를 말합니다.
'배송준비중'    : 배송준비중이 되면 취소가 불가합니다.
'배송중'        : 배송중이면 반품이 불가합니다.
'배송완료'      : 배송이 완료된 상태에서만 포인트적립이 가능합니다.
'취소'          : 입금확인중이나 결제완료후 취소가 가능합니다.
'반품'          : 배송완료 후에만 반품처리가 가능합니다.
'품절'          : 


# 13.10.04

'쇼핑'  : 고객이 장바구니에 상품을 담고 있는 경우 입니다.
'주문'  : 무통장, 가상계좌의 경우 결제하기 전을 말합니다.
'입금'  : 신용카드, 계좌이체, 휴대폰결제가 된 상태, 무통장, 가상계좌는 주문후 입금한 상태를 말합니다.
'배송'  : 배송이 되면 취소가 불가합니다.
'완료'  : 배송이 완료된 상태에서만 포인트적립이 가능합니다.
'취소'  : 입금이후로는 고객의 취소가 불가합니다.
'반품'  : 배송완료 후에만 반품처리가 가능합니다.
'품절'  : 주문이나 입금후 상품의 품절된 상태를 나타냅니다.
*/

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================


include_once(G5_LIB_PATH.'/shop.lib.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

//==============================================================================
// 쇼핑몰 미수금 등의 주문정보
//==============================================================================
/*
$info = get_order_info($od_id);

$info['od_cart_price']      // 장바구니 주문상품 총금액
$info['od_send_cost']       // 배송비
$info['od_coupon']          // 주문할인 쿠폰금액
$info['od_send_coupon']     // 배송할인 쿠폰금액
$info['od_cart_coupon']     // 상품할인 쿠폰금액
$info['od_tax_mny']         // 과세 공급가액
$info['od_vat_mny']         // 부가세액
$info['od_free_mny']        // 비과세 공급가액
$info['od_cancel_price']    // 주문 취소상품 총금액
$info['od_misu']            // 미수금액
*/
//==============================================================================
// 쇼핑몰 미수금 등의 주문정보
//==============================================================================
?>