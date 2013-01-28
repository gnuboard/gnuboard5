<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

$menu['menu500'] = array (
<<<<<<< HEAD
    array('500000', '쇼핑몰현황/기타',  G4_SHOP_ADMIN_URL.'/itemsellrank.php'),
    array('500100', '상품판매순위',     G4_SHOP_ADMIN_URL.'/itemsellrank.php'),
    array('500110', '매출현황',         G4_SHOP_ADMIN_URL.'/sale1.php'),
    array('500120', '주문내역출력',     G4_SHOP_ADMIN_URL.'/orderprint.php'),
    array('500130', '전자결제내역',     G4_SHOP_ADMIN_URL.'/ordercardhistory.php'),
    array('500140', '보관함현황',       G4_SHOP_ADMIN_URL.'/wishlist.php'),

    array('500200', 'SMS 문자전송',     G4_SHOP_ADMIN_URL.'/smssend.php'),
    array('500210', '가격비교사이트',   G4_SHOP_ADMIN_URL.'/price.php')
=======
    array('500000', '쇼핑몰현황/기타',      G4_SHOP_ADMIN_URL.'/itemsellrank.php'),
    array('500100', '상품판매순위',         G4_SHOP_ADMIN_URL.'/itemsellrank.php'),
    array('500110', '매출현황',             G4_SHOP_ADMIN_URL.'/sale1.php'),
    array('500120', '주문내역출력',         G4_SHOP_ADMIN_URL.'/orderprint.php'),
    array('500130', '전자결제내역',         G4_SHOP_ADMIN_URL.'/ordercardhistory.php'),
    array('500140', '보관함현황',           G4_SHOP_ADMIN_URL.'/wishlist.php'),

    array('500200', 'SMS 문자전송',         G4_SHOP_ADMIN_URL.'/smssend.php'),
    array('500210', '가격비교사이트',       G4_SHOP_ADMIN_URL.'/price.php')
>>>>>>> e82fa69b4c2eeb9e9eb9eefa11b485ed5a6846c6
);
?>