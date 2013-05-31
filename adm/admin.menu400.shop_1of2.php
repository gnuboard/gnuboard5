<?
if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

$menu['menu400'] = array (
    array('400000', '쇼핑몰관리', G4_ADMIN_URL.'/shop_admin/', 'shop_config'),
    array('400100', '쇼핑몰설정', G4_ADMIN_URL.'/shop_admin/configform.php', 'scf_config'),
    array('400400', '주문내역', G4_ADMIN_URL.'/shop_admin/orderlist.php', 'scf_order', 1),
    array('400410', '주문개별내역', G4_ADMIN_URL.'/shop_admin/orderstatuslist.php', 'scf_order_by', 1),
    array('400420', '주문통합내역', G4_ADMIN_URL.'/shop_admin/orderlist2.php', 'scf_order_all', 1),
    array('400200', '분류관리', G4_ADMIN_URL.'/shop_admin/categorylist.php', 'scf_cate'),
    array('400300', '상품관리', G4_ADMIN_URL.'/shop_admin/itemlist.php', 'scf_item'),
    array('400660', '상품문의', G4_ADMIN_URL.'/shop_admin/itemqalist.php', 'scf_item_qna'),
    array('400650', '사용후기', G4_ADMIN_URL.'/shop_admin/itempslist.php', 'scf_ps'),
    array('400620', '상품재고관리', G4_ADMIN_URL.'/shop_admin/itemstocklist.php', 'scf_item_stock'),
    array('400610', '상품유형관리', G4_ADMIN_URL.'/shop_admin/itemtypelist.php', 'scf_item_type'),
    array('400500', '상품옵션재고관리', G4_ADMIN_URL.'/shop_admin/optionstocklist.php', 'scf_item_option'),
    array('400490', '마일리지관리', G4_ADMIN_URL.'/shop_admin/mileagelist.php', 'scf_mileage'),
    array('400500', '배송일괄처리', G4_ADMIN_URL.'/shop_admin/deliverylist.php', 'scf_deli', 1),
    array('400740', '배송업체관리', G4_ADMIN_URL.'/shop_admin/deliverycodelist.php', 'scf_deli_co', 1),
    array('400630', '이벤트관리', G4_ADMIN_URL.'/shop_admin/itemevent.php', 'scf_event'),
    array('400640', '이벤트일괄처리', G4_ADMIN_URL.'/shop_admin/itemeventlist.php', 'scf_event_mng'),
    array('400700', '내용관리', G4_ADMIN_URL.'/shop_admin/contentlist.php', 'scf_contents', 1),
    array('400710', 'FAQ관리', G4_ADMIN_URL.'/shop_admin/faqmasterlist.php', 'scf_faq', 1),
    array('400730', '배너관리', G4_ADMIN_URL.'/shop_admin/bannerlist.php', 'scf_banner', 1),
    array('400720', '팝업레이어관리', G4_ADMIN_URL.'/shop_admin/newwinlist.php', 'scf_poplayer', 1)
);
?>