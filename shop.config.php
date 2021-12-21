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

define('G5_SHOP_IMG_URL',  G5_SHOP_URL.'/'.G5_IMG_DIR);
define('G5_MSHOP_IMG_URL', G5_MSHOP_URL.'/'.G5_IMG_DIR);

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

if(!defined('_THEME_PREVIEW_')) {
    // 테마 경로 설정
    if(defined('G5_THEME_PATH')) {
        define('G5_THEME_SHOP_PATH',   G5_THEME_PATH.'/'.G5_SHOP_DIR);
        define('G5_THEME_SHOP_URL',    G5_THEME_URL.'/'.G5_SHOP_DIR);
        define('G5_THEME_MSHOP_PATH',  G5_THEME_PATH.'/'.G5_MOBILE_DIR.'/'.G5_SHOP_DIR);
        define('G5_THEME_MSHOP_URL',   G5_THEME_URL.'/'.G5_MOBILE_DIR.'/'.G5_SHOP_DIR);
    }

    // 스킨 경로 설정
    if(preg_match('#^theme/(.+)$#', $default['de_shop_skin'], $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_SHOP_SKIN_PATH',  G5_THEME_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
			define('G5_SHOP_SKIN_URL',   G5_THEME_URL .'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
		} else {
			define('G5_SHOP_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
			define('G5_SHOP_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
		}
    } else {
        define('G5_SHOP_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
        define('G5_SHOP_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_skin']);
    }

    if(preg_match('#^theme/(.+)$#', $default['de_shop_mobile_skin'], $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_MSHOP_SKIN_PATH', G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
			define('G5_MSHOP_SKIN_URL',  G5_THEME_URL .'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
		} else {
			define('G5_MSHOP_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
			define('G5_MSHOP_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/shop/'.$match[1]);
		}
    } else {
        define('G5_MSHOP_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);
        define('G5_MSHOP_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/shop/'.$default['de_shop_mobile_skin']);
    }
}

if( !isset($g5['g5_shop_post_log_table']) || !$g5['g5_shop_post_log_table'] ){
    $g5['g5_shop_post_log_table'] = G5_SHOP_TABLE_PREFIX.'order_post_log'; // 주문요청 로그 테이블
}

// 옵션 ID 특수문자 필터링 패턴
define('G5_OPTION_ID_FILTER', '/[\'\"\\\'\\\"]/');

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
    // 토스페이먼츠 현금영수증 실결제 URL 상수
    define('SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS', 'https://pgweb.tosspayments.com/WEB_SERVER/js/receipt_link.js');
    // 토스페이먼츠 현금영수증 테스트 URL 상수
    define('SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS', 'https://pgweb.tosspayments.com:7085/WEB_SERVER/js/receipt_link.js');
} else {
    // 토스페이먼츠 현금영수증 실결제 URL 상수
    define('SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS', 'http://pgweb.tosspayments.com/WEB_SERVER/js/receipt_link.js');
    // 토스페이먼츠 현금영수증 테스트 URL 상수
    define('SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS', 'http://pgweb.tosspayments.com:7085/WEB_SERVER/js/receipt_link.js');
}

// 네이버페이를 신용카드 결제창에서 지원하고 있으므로 네이버에 직접신청하는 결제 기능을 미사용(false:기본설정) 합니다. (kagla,211019)
// 네이버에서 직접신청 결제를 사용(true)하시는 경우 모든 문제를 직접 해결해 주셔야 합니다.
define('G5_SHOP_DIRECT_NAVERPAY', false); // false 미사용(기본설정), true 사용

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
//==============================================================================;