<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

$menu['menu600'] = array(
    array('600000', '정기결제관리', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/configform.php', 'subs_config'),
    array('600100', '정기결제설정', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/configform.php', 'subs_config'),
    array('600200', '분류관리', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/categorylist.php', 'subs_cate'),
    array('600300', '상품관리', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/itemlist.php', 'subs_item'),
    array('600400', '구독내역', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/orderlist.php', 'subs_orderlist'),
    array('600410', '정기결제내역', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/paylist.php', 'subs_paylist'),
    array('600500', '정기결제일정', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/subscription_calendar.php', 'subs_subscription_calendar'),
    array('600510', '공휴일설정', G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/subscription_holidays.php', 'subs_subscription_holidays'),
);
