<?
if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

$menu['menu500'] = array (
    array('500000', '쇼핑몰현황/기타', G4_ADMIN_URL.'/shop_admin/itemsellrank.php', 'shop_stats'),
    array('500110', '매출현황', G4_ADMIN_URL.'/shop_admin/sale1.php', 'sst_order_stats'),
    array('500100', '상품판매순위', G4_ADMIN_URL.'/shop_admin/itemsellrank.php', 'sst_rank'),
    array('500120', '주문내역출력', G4_ADMIN_URL.'/shop_admin/orderprint.php', 'sst_print_order', 1),
    array('500130', '전자결제내역', G4_ADMIN_URL.'/shop_admin/ordercardhistory.php', 'sst_pg', 1),
    array('500140', '보관함현황', G4_ADMIN_URL.'/shop_admin/wishlist.php', 'sst_wish'),
    array('500200', 'SMS 문자전송', G4_ADMIN_URL.'/shop_admin/smssend.php', 'sst_sms'),
    array('500210', '가격비교사이트', G4_ADMIN_URL.'/shop_admin/price.php', 'sst_compare', 1)
);
?>