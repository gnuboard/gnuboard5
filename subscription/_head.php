<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once G5_MSUBSCRIPTION_PATH.'/subscription.head.php';
} else {
    include_once G5_SUBSCRIPTION_PATH.'/subscription.head.php';
}
