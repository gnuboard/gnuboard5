<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
//if (!defined('G4_IS_SHOP') || !G4_IS_SHOP) return;

include_once(G4_LIB_PATH.'/shop.lib.php');

//==============================================================================
// 쇼핑몰 설정 상수 및 변수
//------------------------------------------------------------------------------
// 미수금에 대한 QUERY 문
// 테이블 a 는 장바구니 ($g4[yc4_cart_table])
// 테이블 b 는 주문서 ($g4[yc4_order_table])
define(_MISU_QUERY_, "
    count(distinct b.od_id) as ordercount, /* 주문서건수 */
    count(a.ct_id) as itemcount, /* 상품건수 */
    (SUM((a.ct_amount + a.it_amount - a.cp_amount) * a.ct_qty) + b.od_send_cost + b.od_send_cost_area - b.od_coupon_amount - b.od_send_coupon) as orderamount , /* 주문합계 */
    (SUM(IF(a.ct_status = '취소' OR a.ct_status = '반품' OR a.ct_status = '품절', (a.ct_amount + a.it_amount) * a.ct_qty, 0))) as ordercancel, /* 주문취소 */
    (b.od_receipt_amount + b.od_receipt_point) as receiptamount, /* 입금합계 */
    (b.od_refund_amount) as receiptcancel, /* 입금취소 */
    (
        (SUM((a.ct_amount + a.it_amount - a.cp_amount) * a.ct_qty) + b.od_send_cost + b.od_send_cost_area - b.od_coupon_amount - b.od_send_coupon) -
        (SUM(IF(a.ct_status = '취소' OR a.ct_status = '반품' OR a.ct_status = '품절', (a.ct_amount + a.it_amount) * a.ct_qty, 0))) -
        b.od_dc_amount -
        (b.od_receipt_amount + b.od_receipt_point) +
        (b.od_refund_amount)
    ) as misu /* 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소 */");


// 쇼핑몰 디렉토리
$g4['shop']           = "shop";
$g4['shop_path']      = "$g4[path]/$g4[shop]";
$g4['shop_url']       = "$g4[url]/$g4[shop]";
define('G4_SHOP_PATH',  G4_PATH.'/shop');
define('G4_SHOP_URL',   G4_URL.'/shop');

$g4['shop_admin']     = "shop_admin";
$g4['shop_admin_path']= "$g4[path]/$g4[admin]/$g4[shop_admin]";
$g4['shop_admin_url'] = "$g4[url]/$g4[admin]/$g4[shop_admin]";
define('G4_SHOP_ADMIN_PATH',  G4_ADMIN_PATH.'/shop_admin');
define('G4_SHOP_ADMIN_URL',   G4_ADMIN_URL.'/shop_admin');

$g4['shop_img']       = "img";
$g4['shop_img_path']  = "$g4[path]/$g4[shop]/$g4[shop_img]";
$g4['shop_img_url']   = "$g4[url]/$g4[shop]/$g4[shop_img]";
define('G4_SHOP_IMG_URL',   G4_SHOP_URL.'/img');

// 쇼핑몰 테이블명
$g4['yc4_default_table']       = "yc4_default";               // 쇼핑몰설정 테이블
$g4['yc4_banner_table']        = "yc4_banner";                // 배너 테이블
$g4['yc4_card_history_table']  = "yc4_card_history";          // 전자결제이력 테이블
$g4['yc4_cart_table']          = "yc4_cart";                  // 장바구니 테이블
$g4['yc4_category_table']      = "yc4_category";              // 상품분류 테이블
$g4['yc4_content_table']       = "yc4_content";               // 내용(컨텐츠)정보 테이블
$g4['yc4_delivery_table']      = "yc4_delivery";              // 배송정보 테이블
$g4['yc4_event_table']         = "yc4_event";                 // 이벤트 테이블
$g4['yc4_event_item_table']    = "yc4_event_item";            // 상품, 이벤트 연결 테이블
$g4['yc4_faq_table']           = "yc4_faq";                   // 자주하시는 질문 테이블
$g4['yc4_faq_master_table']    = "yc4_faq_master";            // 자주하시는 질문 마스터 테이블
$g4['yc4_item_table']          = "yc4_item";                  // 상품 테이블
$g4['yc4_item_ps_table']       = "yc4_item_ps";               // 상품 사용후기 테이블
$g4['yc4_item_qa_table']       = "yc4_item_qa";               // 상품 질문답변 테이블
$g4['yc4_item_relation_table'] = "yc4_item_relation";         // 관련 상품 테이블
$g4['yc4_new_win_table']       = "yc4_new_win";               // 새창 테이블
$g4['yc4_onlinecalc_table']    = "yc4_onlinecalc";            // 온라인견적 테이블
$g4['yc4_order_table']         = "yc4_order";                 // 주문서 테이블
$g4['yc4_wish_table']          = "yc4_wish";                  // 보관함(위시리스트) 테이블
$g4['yc4_on_uid_table']        = "yc4_on_uid";                // 주문번호생성 유니크키 테이블
$g4['yc4_option_table']        = "yc4_option";                // 선택 옵션 테이블
$g4['yc4_supplement_table']    = "yc4_supplement";            // 추가옵션 테이블
$g4['yc4_coupon_table']        = "yc4_coupon";                // 쿠폰정보 테이블
$g4['yc4_coupon_history_table']= "yc4_coupon_history";        // 쿠폰사용내역 테이블
$g4['yc4_sendcost_table']      = "yc4_sendcost";              // 추가배송비 테이블
$g4['yc4_uniqid_table']        = "yc4_uniqid";

// 신용카드결제대행사 URL
$g4['yc4_cardpg']['kcp']        = "http://admin.kcp.co.kr";
$g4['yc4_cardpg']['banktown']   = "http://ebiz.banktown.com/index.cs";
$g4['yc4_cardpg']['telec']      = "http://www.ebizpro.co.kr";
$g4['yc4_cardpg']['inicis']     = "https://iniweb.inicis.com/DefaultWebApp/index.html";
$g4['yc4_cardpg']['allthegate'] = "http://www.allthegate.com/login/r_login.jsp";
$g4['yc4_cardpg']['allat']      = "http://www.allatbiz.net/servlet/AllatBizSrvX/bizcon/jspx/login/login.jsp?next=/servlet/AllatBizSrvX/bizable/jspx/login/login.jsp";
$g4['yc4_cardpg']['tgcorp']     = "https://npg.tgcorp.com/mdbop/login.jsp";
$g4['yc4_cardpg']['kspay']      = "http://nims.ksnet.co.kr:7001/pg_infoc/src/login.jsp"; // ksnet
$g4['yc4_cardpg']['dacom']      = "https://pgweb.dacom.net";
$g4['yc4_cardpg']['dacom_xpay'] = "https://pgweb.dacom.net";

//==============================================================================


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g4['yc4_default_table']} ");

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

// 프로그램 전반에 걸쳐 사용하는 유일한 키 (주문번호 키)
if (!get_session('ss_uniqid')) {
    set_session('ss_uniqid', get_uniqid());
}
?>