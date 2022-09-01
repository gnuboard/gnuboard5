<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

/*
배송업체에 데이터를 추가하는 경우 아래 형식으로 추가하세요.
.'(배송업체명^택배조회URL^연락처)'
*/
define('G5_DELIVERY_COMPANY',
     '(경동택배^https://kdexp.com/basicNewDelivery.kd?barcode=^080-873-2178)'
    .'(대신택배^https://www.ds3211.co.kr/freight/internalFreightSearch.ht?billno=^043-222-4582)'
    .'(동부택배^http://www.dongbups.com/delivery/delivery_search_view.jsp?item_no=^1588-8848)'
    .'(로젠택배^https://www.ilogen.com/m/personal/trace.pop/^1588-9988)'
    .'(우체국^https://m.epost.go.kr/postal/mobile/mobile.trace.RetrieveDomRigiTraceList.comm?ems_gubun=E&sid1=^1588-1300)'
    .'(이노지스택배^http://www.innogis.co.kr/tracking_view.asp?invoice=^1566-4082)'
    .'(한진택배^https://www.hanjin.co.kr/kor/CMS/DeliveryMgr/WaybillResult.do?mCode=MN038&schLang=KR&wblnumText2=^1588-0011)'
    .'(롯데택배^https://www.lotteglogis.com/open/tracking?invno=^1588-2121)'
    .'(CJ대한통운^https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=^1588-1255)'
    .'(CVSnet편의점택배^https://www.cvsnet.co.kr/invoice/tracking.do?invoice_no=^1577-1287)'
    .'(KG옐로우캡택배^http://www.yellowcap.co.kr/custom/inquiry_result.asp?invoice_no=^1588-0123)'
    .'(KGB택배^http://www.kgbls.co.kr/sub5/trace.asp?f_slipno=^1577-4577)'
    .'(KG로지스^http://www.kglogis.co.kr/contents/waybill.jsp?item_no=^1588-8848)'
    .'(건영택배^https://www.kunyoung.com/goods/goods_01.php?mulno=^031-460-2700)'
    .'(호남택배^http://www.honamlogis.co.kr/04estimate/songjang_list.php?c_search1=^031-376-6070)'
);

include_once(G5_LIB_PATH.'/shop.data.lib.php');
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

// 매출전표 url 설정
if($default['de_card_test']) {
    define('G5_BILL_RECEIPT_URL', 'https://testadmin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=');
    define('G5_CASH_RECEIPT_URL', 'https://testadmin8.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?term_id=PGNW');
} else {
    define('G5_BILL_RECEIPT_URL', 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=');
    define('G5_CASH_RECEIPT_URL', 'https://admin.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?term_id=PGNW');
}

// 상품상세 페이지에서 재고체크 실행 여부 선택
// 상품의 옵션이 많아 로딩 속도가 느린 경우 false 로 설정
define('G5_SOLDOUT_CHECK', true);

// 주문폼의 상품이 재고 차감에 포함되는 기준 시간설정
// 0 이면 재고 차감에 계속 포함됨
define('G5_CART_STOCK_LIMIT', 3);

// 아이코드 코인 최소금액 설정
// 코인 잔액이 설정 금액보다 작을 때는 주문시 SMS 발송 안함
define('G5_ICODE_COIN', 100);

include_once(G5_LIB_PATH.'/shop.uri.lib.php');

add_replace('get_pretty_url', 'add_pretty_shop_url', 10, 5);
add_replace('false_short_url_clean', 'shop_short_url_clean', 10, 4);
add_replace('add_nginx_conf_rules', 'add_shop_nginx_conf_rules', 10, 3);
add_replace('add_mod_rewrite_rules', 'add_shop_mod_rewrite_rules', 10, 3);
add_replace('admin_dbupgrade', 'add_shop_admin_dbupgrade', 10, 3);
add_replace('exist_check_seo_title', 'shop_exist_check_seo_title', 10, 4);