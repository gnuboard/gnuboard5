<?php
include_once('./_common.php');

define('G5_IS_SHOP_AJAX_LIST', true);

$ca_id = isset($_REQUEST['ca_id']) ? safe_replace_regex($_REQUEST['ca_id'], 'ca_id') : '';

$data = array();

$sql = " select *
           from {$g5['g5_shop_category_table']}
          where ca_id = '$ca_id'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    die(json_encode($data['error'] = '등록된 분류가 없습니다.'));

// 스킨경로
$skin_dir = G5_MSHOP_SKIN_PATH;

if($ca['ca_mobile_skin_dir']) {
    $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$ca['ca_mobile_skin_dir'];

    if(is_dir($skin_dir)) {
        $skin_file = $skin_dir.'/'.$ca['ca_mobile_skin'];

        if(!is_file($skin_file))
            $skin_dir = G5_MSHOP_SKIN_PATH;
    } else {
        $skin_dir = G5_MSHOP_SKIN_PATH;
    }
}

$skin_file = $skin_dir.'/'.$ca['ca_mobile_skin'];

// 상품 출력순서가 있다면
if ($sort != "")
    $order_by = $sort.' '.$sortodr.' , it_order, it_id desc';
else
    $order_by = 'it_order, it_id desc';

// 총몇개
$items = $ca['ca_mobile_list_mod'] * $ca['ca_mobile_list_row'];
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page < 1) $page = 1;

$page++;

// 시작 레코드 구함
$from_record = ($page - 1) * $items;

ob_start();

$list = new item_list($skin_file, $ca['ca_mobile_list_mod'], $ca['ca_mobile_list_row'], $ca['ca_mobile_img_width'], $ca['ca_mobile_img_height']);
$list->set_category($ca['ca_id'], 1);
$list->set_category($ca['ca_id'], 2);
$list->set_category($ca['ca_id'], 3);
$list->set_is_page(true);
$list->set_mobile(true);
$list->set_order_by($order_by);
$list->set_from_record($from_record);
$list->set_view('it_img', true);
$list->set_view('it_id', false);
$list->set_view('it_name', true);
$list->set_view('it_price', true);
if(isset($use_sns) && $use_sns){
    $list->set_view('sns', true);
}
echo $list->run();

$content = ob_get_contents();
ob_end_clean();

$data['item']  = $content;
$data['error'] = '';
$data['page']  = $page;

die(json_encode($data));
