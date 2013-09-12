<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

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
    ( od_cart_amount + od_send_cost + od_send_cost2 - od_cart_coupon - od_coupon - od_send_coupon - od_receipt_amount - od_cancel_amount ) as misu,
    ( od_cart_coupon + od_coupon + od_send_coupon ) as couponamount
    ");
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

// 주문상태 상수
define('G4_OD_STATUS_ORDER'     , '입금확인중');
define('G4_OD_STATUS_SETTLE'    , '결제완료');
define('G4_OD_STATUS_READY'     , '배송준비중');
define('G4_OD_STATUS_DELIVERY'  , '배송중');
define('G4_OD_STATUS_FINISH'    , '배송완료');

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================


include_once(G4_LIB_PATH.'/shop.lib.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');
?>