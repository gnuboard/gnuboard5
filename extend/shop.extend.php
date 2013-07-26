<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 시작
//------------------------------------------------------------------------------

define('G4_SHOP_DIR', 'shop');

define('G4_SHOP_PATH',  G4_PATH.'/'.G4_SHOP_DIR);
define('G4_SHOP_URL',   G4_URL.'/'.G4_SHOP_DIR);
define('G4_MSHOP_PATH', G4_MOBILE_PATH.'/'.G4_SHOP_DIR);
define('G4_MSHOP_URL',  G4_MOBILE_URL.'/'.G4_SHOP_DIR);

// 보안서버주소 설정
if (G4_HTTPS_DOMAIN) {
    define('G4_HTTPS_SHOP_URL', G4_HTTPS_DOMAIN.'/'.G4_SHOP_DIR);
    define('G4_HTTPS_MSHOP_URL', G4_HTTPS_DOMAIN.'/'.G4_MOBILE_DIR.'/'.G4_SHOP_DIR);
} else {
    define('G4_HTTPS_SHOP_URL', G4_SHOP_URL);
    define('G4_HTTPS_MSHOP_URL', G4_MSHOP_URL);
}

// 미수금에 대한 QUERY 문
// 테이블 a 는 주문서 ($g4[shop_order_table])
// 테이블 b 는 장바구니 ($g4[shop_cart_table])
define(_MISU_QUERY_, "
    count(distinct a.od_id) as ordercount, /* 주문서건수 */
    count(b.ct_id) as itemcount, /* 상품건수 */
    (SUM(IF(b.io_type = 1, b.io_price * b.ct_qty, (b.ct_price + b.io_price) * b.ct_qty))  + a.od_send_cost + a.od_send_cost2) as orderamount, /* 주문합계 */
    (SUM(b.cp_amount) + a.od_coupon) as couponamount, /* 쿠폰합계*/
    (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', (IF(b.io_type = 1, b.io_price * b.ct_qty, (b.ct_price + b.io_price) * b.ct_qty)), 0))) as ordercancel, /* 주문취소 */
    (a.od_receipt_amount + a.od_receipt_point) as receiptamount, /* 입금합계 */
    (a.od_refund_amount + a.od_cancel_card) as receiptcancel, /* 입금취소 */
    (
        (SUM(IF(b.io_type = 1, b.io_price * b.ct_qty, (b.ct_price + b.io_price) * b.ct_qty))  + a.od_send_cost + a.od_send_cost2) -
        (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', (IF(b.io_type = 1, b.io_price * b.ct_qty, (b.ct_price + b.io_price) * b.ct_qty)), 0))) -
        a.od_dc_amount -
        (a.od_receipt_amount + a.od_receipt_point) +
        (a.od_refund_amount + a.od_cancel_card) -
        (SUM(b.cp_amount) + a.od_coupon)
    ) as misu /* 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소 - 쿠폰합계 */");
//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 끝
//------------------------------------------------------------------------------


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================

// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g4['shop_default_table']} ");

define('G4_SHOP_SKIN_PATH',  G4_PATH.'/'.G4_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
define('G4_SHOP_SKIN_URL',   G4_URL .'/'.G4_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
define('G4_MSHOP_SKIN_PATH', G4_MOBILE_PATH.'/'.G4_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);
define('G4_MSHOP_SKIN_URL',  G4_MOBILE_URL .'/'.G4_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================


include_once(G4_LIB_PATH.'/shop.lib.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');
?>