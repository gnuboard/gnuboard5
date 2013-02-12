<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

define('G4_USE_SHOP', true);

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

include_once(G4_LIB_PATH.'/shop.lib.php');

//==============================================================================
// 쇼핑몰 설정 상수 및 변수
//------------------------------------------------------------------------------
// 미수금에 대한 QUERY 문
// 테이블 a 는 장바구니 ($g4[shop_cart_table])
// 테이블 b 는 주문서 ($g4[shop_order_table])
/*
define(_MISU_QUERY_, "
    count(distinct b.od_id) as ordercount, -- 주문서건수
    count(a.ct_id) as itemcount, -- 상품건수
    (SUM((a.ct_amount + a.it_amount - a.cp_amount) * a.ct_qty) + b.od_send_cost + b.od_send_cost_area - b.od_coupon_amount - b.od_send_coupon) as orderamount , -- 주문합계
    (SUM(IF(a.ct_status = '취소' OR a.ct_status = '반품' OR a.ct_status = '품절', (a.ct_amount + a.it_amount) * a.ct_qty, 0))) as ordercancel, -- 주문취소
    (b.od_receipt_amount + b.od_receipt_point) as receiptamount, -- 입금합계
    (b.od_refund_amount) as receiptcancel, -- 입금취소
    (
        (SUM((a.ct_amount + a.it_amount - a.cp_amount) * a.ct_qty) + b.od_send_cost + b.od_send_cost_area - b.od_coupon_amount - b.od_send_coupon) -
        (SUM(IF(a.ct_status = '취소' OR a.ct_status = '반품' OR a.ct_status = '품절', (a.ct_amount + a.it_amount) * a.ct_qty, 0))) -
        b.od_dc_amount -
        (b.od_receipt_amount + b.od_receipt_point) +
        (b.od_refund_amount)
    ) as misu -- 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소");
*/

// 쇼핑몰 디렉토리
define('G4_SHOP_DIR',  'shop');
define('G4_SHOP_PATH', G4_PATH.'/shop');
define('G4_SHOP_URL',  G4_URL.'/shop');

define('G4_SHOP_ADMIN_DIR',  'shop_admin');
define('G4_SHOP_ADMIN_PATH', G4_ADMIN_PATH.'/'.G4_SHOP_ADMIN_DIR);
define('G4_SHOP_ADMIN_URL',  G4_ADMIN_URL.'/'.G4_SHOP_ADMIN_DIR);

define('G4_SHOP_IMG_DIR', '/img');
define('G4_SHOP_IMG_URL', G4_SHOP_URL.'/'.G4_SHOP_IMG_DIR);

// 쇼핑몰 테이블명
$g4['shop_default_table']       = G4_TABLE_PREFIX.'shop_default';               // 쇼핑몰설정 테이블
$g4['shop_banner_table']        = G4_TABLE_PREFIX.'shop_banner';                // 배너 테이블
$g4['shop_card_history_table']  = G4_TABLE_PREFIX.'shop_card_history';          // 전자결제이력 테이블
$g4['shop_cart_table']          = G4_TABLE_PREFIX.'shop_cart';                  // 장바구니 테이블
$g4['shop_category_table']      = G4_TABLE_PREFIX.'shop_category';              // 상품분류 테이블
$g4['shop_content_table']       = G4_TABLE_PREFIX.'shop_content';               // 내용(컨텐츠)정보 테이블
$g4['shop_delivery_table']      = G4_TABLE_PREFIX.'shop_delivery';              // 배송정보 테이블
$g4['shop_event_table']         = G4_TABLE_PREFIX.'shop_event';                 // 이벤트 테이블
$g4['shop_event_item_table']    = G4_TABLE_PREFIX.'shop_event_item';            // 상품, 이벤트 연결 테이블
$g4['shop_faq_table']           = G4_TABLE_PREFIX.'shop_faq';                   // 자주하시는 질문 테이블
$g4['shop_faq_master_table']    = G4_TABLE_PREFIX.'shop_faq_master';            // 자주하시는 질문 마스터 테이블
$g4['shop_item_table']          = G4_TABLE_PREFIX.'shop_item';                  // 상품 테이블
$g4['shop_item_ps_table']       = G4_TABLE_PREFIX.'shop_item_ps';               // 상품 사용후기 테이블
$g4['shop_item_qa_table']       = G4_TABLE_PREFIX.'shop_item_qa';               // 상품 질문답변 테이블
$g4['shop_item_relation_table'] = G4_TABLE_PREFIX.'shop_item_relation';         // 관련 상품 테이블
$g4['shop_new_win_table']       = G4_TABLE_PREFIX.'shop_new_win';               // 새창 테이블
$g4['shop_onlinecalc_table']    = G4_TABLE_PREFIX.'shop_onlinecalc';            // 온라인견적 테이블
$g4['shop_order_table']         = G4_TABLE_PREFIX.'shop_order';                 // 주문서 테이블
$g4['shop_wish_table']          = G4_TABLE_PREFIX.'shop_wish';                  // 보관함(위시리스트) 테이블
$g4['shop_on_uid_table']        = G4_TABLE_PREFIX.'shop_on_uid';                // 주문번호생성 유니크키 테이블
$g4['shop_option_table']        = G4_TABLE_PREFIX.'shop_option';                // 선택 옵션 테이블
$g4['shop_supplement_table']    = G4_TABLE_PREFIX.'shop_supplement';            // 추가옵션 테이블
$g4['shop_coupon_table']        = G4_TABLE_PREFIX.'shop_coupon';                // 쿠폰정보 테이블
$g4['shop_coupon_history_table']= G4_TABLE_PREFIX.'shop_coupon_history';        // 쿠폰사용내역 테이블
$g4['shop_sendcost_table']      = G4_TABLE_PREFIX.'shop_sendcost';              // 추가배송비 테이블
$g4['shop_item_info_table']     = G4_TABLE_PREFIX.'shop_item_info';             // 상품요약정보 테이블 (상품정보고시)
//==============================================================================


//==============================================================================
// 주문상태를 선언
// 실제 주문테이블에는 set 방식으로 들어감
//------------------------------------------------------------------------------
// 절대 수정하시면 안됩니다.
//------------------------------------------------------------------------------
define('G4_STATUS_SHOPPING',    '쇼핑중');
define('G4_STATUS_STANDBY',     '입금대기');
define('G4_STATUS_PAYMENT',     '결제완료');
define('G4_STATUS_READY',       '배송준비중');
define('G4_STATUS_DELIVERY',    '배송중');
// 대형쇼핑몰 들과는 달리 배송사와의 정보 연동이 되지 않으므로 "구매확정" 으로만 사용한다.
//define('G4_STATUS_COMPLETE',    '배송완료'); 
define('G4_STATUS_PURCHASE',    '구매확정');
define('G4_STATUS_CANCEL',      '취소');
define('G4_STATUS_RETURN',      '반품');
define('G4_STATUS_EXCHANGE',    '교환');


//==============================================================================
// 상품유형을 & 연산을 위해 상수로 선언
// 합계 = 1,073,741,823 
// MySQL Interger 의 경우 -2,147,483,648 ~ 2,147,483,647 이므로 
// 정수형 필드 한개를 사용하여 & 연산으로 30개 조건을 만족한다.
//------------------------------------------------------------------------------
// 절대 수정하시면 안됩니다.
//------------------------------------------------------------------------------
define('G4_TYPE1', 1);
define('G4_TYPE2', 2);
define('G4_TYPE3', 4);
define('G4_TYPE4', 8);
define('G4_TYPE5', 16);
define('G4_TYPE6', 32);
define('G4_TYPE7', 64);
define('G4_TYPE8', 128);
define('G4_TYPE9', 256);
define('G4_TYPE10', 512);
define('G4_TYPE11', 1024);
define('G4_TYPE12', 2048);
define('G4_TYPE13', 4096);
define('G4_TYPE14', 8192);
define('G4_TYPE15', 16384);
define('G4_TYPE16', 32768);
define('G4_TYPE17', 65536);
define('G4_TYPE18', 131072);
define('G4_TYPE19', 262144);
define('G4_TYPE20', 524288);
define('G4_TYPE21', 1048576);
define('G4_TYPE22', 2097152);
define('G4_TYPE23', 4194304);
define('G4_TYPE24', 8388608);
define('G4_TYPE25', 16777216);
define('G4_TYPE26', 33554432);
define('G4_TYPE27', 67108864);
define('G4_TYPE28', 134217728);
define('G4_TYPE29', 268435456);
define('G4_TYPE30', 536870912);
//==============================================================================


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//------------------------------------------------------------------------------
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g4['shop_default_table']} ");

// 비회원장바구니 사용할 때
if($default['de_guest_cart_use']) {
    $tmp_uq_id = get_cookie('ck_guest_cart_uqid');

    if($tmp_uq_id) {
        set_session('ss_uniqid', $tmp_uq_id);
    }

    unset($tmp_uq_id);
}
//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================
?>