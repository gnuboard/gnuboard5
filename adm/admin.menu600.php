<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

$menu['menu600'] = [
    ['600000', '정기결제관리', G5_ADMIN_URL.'/subscription_admin/configform.php', 'subs_config'],
    ['600100', '정기결제설정', G5_ADMIN_URL.'/subscription_admin/configform.php', 'subs_config'],
    ['600200', '분류관리', G5_ADMIN_URL.'/subscription_admin/categorylist.php', 'subs_cate'],
    ['600300', '상품관리', G5_ADMIN_URL.'/subscription_admin/itemlist.php', 'subs_item'],
    ['600400', '주문내역', G5_ADMIN_URL.'/subscription_admin/orderlist.php', 'subs_orderlist'],
];
