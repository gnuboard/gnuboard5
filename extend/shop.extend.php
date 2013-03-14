<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if(!G4_USE_SHOP)
    return;

include_once(G4_LIB_PATH.'/shop.lib.php');

//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 시작
//------------------------------------------------------------------------------

define('G4_SHOP_DIR', 'shop');

define('G4_SHOP_PATH', G4_PATH.'/'.G4_SHOP_DIR);
define('G4_SHOP_URL', G4_URL.'/'.G4_SHOP_DIR);

// 미수금에 대한 QUERY 문
// 테이블 a 는 주문서 ($g4[yc4_order_table])
// 테이블 b 는 장바구니 ($g4[yc4_cart_table])
define(_MISU_QUERY_, "
    count(distinct a.od_id) as ordercount, /* 주문서건수 */
    count(b.ct_id) as itemcount, /* 상품건수 */
    (SUM(b.ct_amount * b.ct_qty) + a.od_send_cost) as orderamount , /* 주문합계 */
    (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_amount * b.ct_qty, 0))) as ordercancel, /* 주문취소 */
    (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_hp + a.od_receipt_point) as receiptamount, /* 입금합계 */
    (a.od_refund_amount + a.od_cancel_card) as receiptcancel, /* 입금취소 */
    (
        (SUM(b.ct_amount * b.ct_qty) + a.od_send_cost) -
        (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_amount * b.ct_qty, 0))) -
        a.od_dc_amount -
        (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_hp + a.od_receipt_point) +
        (a.od_refund_amount + a.od_cancel_card)
    ) as misu /* 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소 */");
//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 끝
//------------------------------------------------------------------------------



//------------------------------------------------------------------------------
// 쇼핑몰 변수 모음 시작
//------------------------------------------------------------------------------
define('YC4_TABLE_PREFIX', 'yc4_');

// 쇼핑몰 테이블명
$g4['yc4_default_table']       = YC4_TABLE_PREFIX.'default';               // 쇼핑몰설정 테이블
$g4['yc4_banner_table']        = YC4_TABLE_PREFIX.'banner';                // 배너 테이블
$g4['yc4_card_history_table']  = YC4_TABLE_PREFIX.'card_history';          // 전자결제이력 테이블
$g4['yc4_cart_table']          = YC4_TABLE_PREFIX.'cart';                  // 장바구니 테이블
$g4['yc4_category_table']      = YC4_TABLE_PREFIX.'category';              // 상품분류 테이블
$g4['yc4_content_table']       = YC4_TABLE_PREFIX.'content';               // 내용(컨텐츠)정보 테이블
$g4['yc4_delivery_table']      = YC4_TABLE_PREFIX.'delivery';              // 배송정보 테이블
$g4['yc4_event_table']         = YC4_TABLE_PREFIX.'event';                 // 이벤트 테이블
$g4['yc4_event_item_table']    = YC4_TABLE_PREFIX.'event_item';            // 상품, 이벤트 연결 테이블
$g4['yc4_faq_table']           = YC4_TABLE_PREFIX.'faq';                   // 자주하시는 질문 테이블
$g4['yc4_faq_master_table']    = YC4_TABLE_PREFIX.'faq_master';            // 자주하시는 질문 마스터 테이블
$g4['yc4_item_table']          = YC4_TABLE_PREFIX.'item';                  // 상품 테이블
$g4['yc4_item_ps_table']       = YC4_TABLE_PREFIX.'item_ps';               // 상품 사용후기 테이블
$g4['yc4_item_qa_table']       = YC4_TABLE_PREFIX.'item_qa';               // 상품 질문답변 테이블
$g4['yc4_item_relation_table'] = YC4_TABLE_PREFIX.'item_relation';         // 관련 상품 테이블
$g4['yc4_new_win_table']       = YC4_TABLE_PREFIX.'new_win';               // 새창 테이블
$g4['yc4_onlinecalc_table']    = YC4_TABLE_PREFIX.'onlinecalc';            // 온라인견적 테이블
$g4['yc4_order_table']         = YC4_TABLE_PREFIX.'order';                 // 주문서 테이블
$g4['yc4_wish_table']          = YC4_TABLE_PREFIX.'wish';                  // 보관함(위시리스트) 테이블
$g4['yc4_uqid_table']          = YC4_TABLE_PREFIX.'uniqid';                // 주문번호생성 유니크키 테이블
$g4['yc4_item_info_table']     = YC4_TABLE_PREFIX.'item_info';             // 상품요약정보 테이블 (상품정보고시)


// 신용카드결제대행사 URL
$g4[yc4_cardpg][kcp]        = "http://admin.kcp.co.kr";
$g4[yc4_cardpg][banktown]   = "http://ebiz.banktown.com/index.cs";
$g4[yc4_cardpg][telec]      = "http://www.ebizpro.co.kr";
$g4[yc4_cardpg][inicis]     = "https://iniweb.inicis.com/DefaultWebApp/index.html";
$g4[yc4_cardpg][allthegate] = "http://www.allthegate.com/login/r_login.jsp";
$g4[yc4_cardpg][allat]      = "http://www.allatbiz.net/servlet/AllatBizSrvX/bizcon/jspx/login/login.jsp?next=/servlet/AllatBizSrvX/bizable/jspx/login/login.jsp";
$g4[yc4_cardpg][tgcorp]     = "https://npg.tgcorp.com/mdbop/login.jsp";
$g4[yc4_cardpg][kspay]      = "http://nims.ksnet.co.kr:7001/pg_infoc/src/login.jsp"; // ksnet
$g4[yc4_cardpg][dacom]      = "https://pgweb.dacom.net";
$g4[yc4_cardpg][dacom_xpay] = "https://pgweb.dacom.net";
//------------------------------------------------------------------------------
// 쇼핑몰 변수 모음 끝
//------------------------------------------------------------------------------


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from $g4[yc4_default_table] ");

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================
?>