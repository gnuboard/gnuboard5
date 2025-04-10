<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

$menu['menu600'] = array(
    array('600000', '정기결제관리', G5_ADMIN_URL.'/subscription_admin/configform.php', 'subs_config'),
    array('600100', '정기결제설정', G5_ADMIN_URL.'/subscription_admin/configform.php', 'subs_config'),
    array('600200', '분류관리', G5_ADMIN_URL.'/subscription_admin/categorylist.php', 'subs_cate'),
    array('600300', '상품관리', G5_ADMIN_URL.'/subscription_admin/itemlist.php', 'subs_item'),
    array('600400', '구독내역', G5_ADMIN_URL.'/subscription_admin/orderlist.php', 'subs_orderlist'),
    array('600410', '정기결제내역', G5_ADMIN_URL.'/subscription_admin/paylist.php', 'subs_paylist'),
    array('600500', '정기결제일정', G5_ADMIN_URL.'/subscription_admin/subscription_calendar.php', 'subs_subscription_calendar'),
);
