<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (!defined('G5_SHOP_DIRECT_NAVERPAY') || !G5_SHOP_DIRECT_NAVERPAY) return;

if(!$is_admin && !$default['de_card_test'] && $default['de_naverpay_test']) {
    if($default['de_naverpay_mb_id'] && ($is_guest || $member['mb_id'] != $default['de_naverpay_mb_id']))
        return;
}

if(!$default['de_naverpay_cert_key'] || !$default['de_naverpay_button_key'])
    return;

if(basename($_SERVER['SCRIPT_NAME']) == 'item.php') {
    if(!$is_orderable)
        return;
}

$naverpay_button_js = '';

$is_mobile_order = is_mobile();
//$is_mobile_order = G5_IS_MOBILE;
$naverpay_button_enable = 'Y';

$naverpay_button_count = 2;
if(basename($_SERVER['SCRIPT_NAME']) == 'cart.php')
    $naverpay_button_count = 1;

if($is_mobile_order) {
    if($default['de_naverpay_test'])
        $naverpay_button_js_url = 'https://test-pay.naver.com/customer/js/mobile/naverPayButton.js';
    else
        $naverpay_button_js_url = 'https://pay.naver.com/customer/js/mobile/naverPayButton.js';

    $naverpay_button_js = '<script type="text/javascript" src="'.$naverpay_button_js_url.'" charset="UTF-8"></script>
    <script type="text/javascript" >//<![CDATA[
    naver.NaverPayButton.apply({
    BUTTON_KEY: "'.$default['de_naverpay_button_key'].'", // 네이버페이에서 제공받은 버튼 인증 키 입력
    TYPE: "MA", // 버튼 모음 종류 설정
    COLOR: 1, // 버튼 모음의 색 설정
    COUNT: '.$naverpay_button_count.', // 버튼 개수 설정. 구매하기 버튼만 있으면 1, 찜하기 버튼도 있으면 2를 입력.
    ENABLE: "'.$naverpay_button_enable.'", // 품절 등의 이유로 버튼 모음을 비활성화할 때에는 "N" 입력
    BUY_BUTTON_HANDLER : buy_nc, // 구매하기 버튼 이벤트 Handler 함수 등록, 품절인 경우 not_buy_nc 함수 사용
    WISHLIST_BUTTON_HANDLER : wishlist_nc, // 찜하기 버튼 이벤트 Handler 함수 등록
    "":""
    });
    //]]></script>'.PHP_EOL;
} else {
    $naverpay_button_js = '<script type="text/javascript" src="https://pay.naver.com/customer/js/naverPayButton.js" charset="UTF-8"></script>
    <script type="text/javascript" >//<![CDATA[
    naver.NaverPayButton.apply({
    BUTTON_KEY: "'.$default['de_naverpay_button_key'].'", // 페이에서 제공받은 버튼 인증 키 입력
    TYPE: "A", // 버튼 모음 종류 설정
    COLOR: 1, // 버튼 모음의 색 설정
    COUNT: '.$naverpay_button_count.', // 버튼 개수 설정. 구매하기 버튼만 있으면 1, 찜하기 버튼도 있으면 2를 입력.
    ENABLE: "'.$naverpay_button_enable.'", // 품절 등의 이유로 버튼 모음을 비활성화할 때에는 "N" 입력
    BUY_BUTTON_HANDLER : buy_nc, // 구매하기 버튼 이벤트 Handler 함수 등록, 품절인 경우 not_buy_nc 함수 사용
    WISHLIST_BUTTON_HANDLER : wishlist_nc, // 찜하기 버튼 이벤트 Handler 함수 등록
    "":""
    });
    //]]></script>'.PHP_EOL;
}

$naverpay_button_js .= '<input type="hidden" name="naverpay_form" value="'.basename($_SERVER['SCRIPT_NAME']).'">'.PHP_EOL;

if($default['de_naverpay_test'] || $default['de_card_test']) {
    $req_addr     = 'ssl://test-pay.naver.com';
    $buy_req_url  = 'POST /customer/api/order.nhn HTTP/1.1';
    $wish_req_url = 'POST /customer/api/wishlist.nhn HTTP/1.1';
    $req_host     = 'test-pay.naver.com';
    $req_port     = 443;
    if($is_mobile_order) {
        $orderUrl = 'https://test-m.pay.naver.com/mobile/customer/order.nhn';
        $wishUrl  = 'https://test-m.pay.naver.com/mobile/customer/wishList.nhn';
    } else {
        $orderUrl = 'https://test-pay.naver.com/customer/order.nhn';
        $wishUrl  = 'https://test-pay.naver.com/customer/wishlistPopup.nhn';
    }
} else {
    $req_addr     = 'ssl://pay.naver.com';
    $buy_req_url  = 'POST /customer/api/order.nhn HTTP/1.1';
    $wish_req_url = 'POST /customer/api/wishlist.nhn HTTP/1.1';
    $req_host     = 'pay.naver.com';
    $req_port     = 443;
    if($is_mobile_order) {
        $orderUrl     = 'https://m.pay.naver.com/mobile/customer/order.nhn';
        $wishUrl      = 'https://m.pay.naver.com/mobile/customer/wishList.nhn';
    } else {
        $orderUrl     = 'https://pay.naver.com/customer/order.nhn';
        $wishUrl      = 'https://pay.naver.com/customer/wishlistPopup.nhn';
    }
}

define('SHIPPING_ADDITIONAL_PRICE', $default['de_naverpay_sendcost']);

$naverpay_request_js = '<script type="text/javascript" >//<![CDATA[
function buy_nc(url)
{
    var f = $(this).closest("form").get(0);

    var check = fsubmit_check(f);
    if ( check ) {
        //네이버페이로 주문 정보를 등록하는 가맹점 페이지로 이동.
        //해당 페이지에서 주문 정보 등록 후 네이버페이 주문서 페이지로 이동.
        //location.href=url;

        //var win_buy_nc = window.open("_blank", "win_buy_nc", "scrollbars=yes,width=900,height=700,top=10,left=10");
        //f.action = "'.G5_SHOP_URL.'/naverpay/naverpay_order.php";
        //f.target = "win_buy_nc";
        //f.submit();
        //return false;

        $.ajax({
            url : "'.G5_SHOP_URL.'/naverpay/naverpay_order.php",
            type : "POST",
            data : $(f).serialize(),
            async : false,
            cache : false,
            dataType : "json",
            success : function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                document.location.href = "'.$orderUrl.'?ORDER_ID="+data.ORDER_ID+"&SHOP_ID="+data.SHOP_ID+"&TOTAL_PRICE="+data.TOTAL_PRICE;
            }
        });
    }

    return false;
}
function wishlist_nc(url)
{
    var f = $(this).closest("form").get(0);

    // 네이버페이로 찜 정보를 등록하는 가맹점 페이지 팝업 창 생성.
    // 해당 페이지에서 찜 정보 등록 후 네이버페이 찜 페이지로 이동.
    '.($is_mobile_order ? '' : 'var win_wishlist_nc = window.open(url,"win_wishlist_nc","scrollbars=yes,width=400,height=267");'.PHP_EOL.'f.target = "win_wishlist_nc";').'
    f.action = "'.G5_SHOP_URL.'/naverpay/naverpay_wish.php";
    f.submit();

    return false;
}
function not_buy_nc()
{
    alert("죄송합니다. 네이버페이로 구매가 불가한 상품입니다.");
    return false;
}
//]]></script>'.PHP_EOL;