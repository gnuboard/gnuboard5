<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$nicepay_returnURL = G5_MSHOP_URL.'/nicepay/return_url_result.php';