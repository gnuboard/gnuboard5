<?php
if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

$menu['menu500'] = array (
    array('500000', '쇼핑몰현황/기타', G5_ADMIN_URL.'/shop_admin/itemsellrank.php', 'shop_stats'),
    array('500110', '매출현황', G5_ADMIN_URL.'/shop_admin/sale1.php', 'sst_order_stats'),
    array('500100', '상품판매순위', G5_ADMIN_URL.'/shop_admin/itemsellrank.php', 'sst_rank'),
    array('500120', '주문내역출력', G5_ADMIN_URL.'/shop_admin/orderprint.php', 'sst_print_order', 1),
    array('500400', '재입고SMS알림', G5_ADMIN_URL.'/shop_admin/itemstocksms.php', 'sst_stock_sms', 1),
    array('500300', '이벤트관리', G5_ADMIN_URL.'/shop_admin/itemevent.php', 'scf_event'),
    array('500310', '이벤트일괄처리', G5_ADMIN_URL.'/shop_admin/itemeventlist.php', 'scf_event_mng'),
    array('500500', '배너관리', G5_ADMIN_URL.'/shop_admin/bannerlist.php', 'scf_banner', 1),
    array('500140', '보관함현황', G5_ADMIN_URL.'/shop_admin/wishlist.php', 'sst_wish'),
    array('500210', '가격비교사이트', G5_ADMIN_URL.'/shop_admin/price.php', 'sst_compare', 1)
);
?>