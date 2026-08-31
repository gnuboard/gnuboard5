<?php
include_once('../../../common.php');

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

$is_admin = get_super_admin_type($is_admin);

$request_sort = (isset($_REQUEST['sort']) && is_string($_REQUEST['sort'])) ? $_REQUEST['sort'] : '';
$request_sortodr = (isset($_REQUEST['sortodr']) && is_string($_REQUEST['sortodr'])) ? $_REQUEST['sortodr'] : '';
list($sort, $sortodr) = get_shop_item_sort($request_sort, $request_sortodr);
unset($request_sort, $request_sortodr);

define('_SHOP_', true);
