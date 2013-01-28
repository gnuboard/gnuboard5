<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

$menu['menu400'] = array (
    array('400000', '쇼핑몰관리',       G4_SHOP_ADMIN_URL),
    array('400100', '쇼핑몰설정',       G4_SHOP_ADMIN_URL.'/configform.php'),

    array('400200', '분류관리',         G4_SHOP_ADMIN_URL.'/categorylist.php'),
    array('400300', '상품관리',         G4_SHOP_ADMIN_URL.'/itemlist.php'),
    array('400400', '주문관리',         G4_SHOP_ADMIN_URL.'/orderlist.php'),
    array('400410', '주문개별관리',     G4_SHOP_ADMIN_URL.'/orderstatuslist.php'),
    array('400420', '주문통합관리',     G4_SHOP_ADMIN_URL.'/orderlist2.php'),
    array('400500', '배송일괄처리',     G4_SHOP_ADMIN_URL.'/deliverylist.php'),

    array('400610', '상품유형관리',     G4_SHOP_ADMIN_URL.'/itemtypelist.php'),
    array('400620', '상품재고관리',     G4_SHOP_ADMIN_URL.'/itemstocklist.php'),
    array('400630', '이벤트관리',       G4_SHOP_ADMIN_URL.'/itemevent.php'),
    array('400640', '이벤트일괄처리',   G4_SHOP_ADMIN_URL.'/itemeventlist.php'),
    array('400650', '사용후기',         G4_SHOP_ADMIN_URL.'/itempslist.php'),
    array('400660', '상품문의',         G4_SHOP_ADMIN_URL.'/itemqalist.php'),

    array('400700', '내용관리',         G4_SHOP_ADMIN_URL.'/contentlist.php'),
    array('400710', 'FAQ 관리',         G4_SHOP_ADMIN_URL.'/faqmasterlist.php'),
    array('400720', '새창관리',         G4_SHOP_ADMIN_URL.'/newwinlist.php'),
    array('400730', '배너관리',         G4_SHOP_ADMIN_URL.'/bannerlist.php'),
    array('400740', '배송회사관리',     G4_SHOP_ADMIN_URL.'/deliverycodelist.php'),
    array('400750', '추가배송비관리',   G4_SHOP_ADMIN_URL.'/sendcostlist.php'),

    array('400800', '쿠폰관리',         G4_SHOP_ADMIN_URL.'/couponlist.php')
);
?>