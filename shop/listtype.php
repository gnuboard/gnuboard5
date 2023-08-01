<?php
include_once('./_common.php');

// 상품 리스트에서 다른 필드로 정렬을 하려면 아래의 배열 코드에서 해당 필드를 추가하세요.
$sort = (isset($_REQUEST['sort']) && in_array($_REQUEST['sort'], array('it_name', 'it_sum_qty', 'it_price', 'it_use_avg', 'it_use_cnt', 'it_update_time'))) ? $_REQUEST['sort'] : '';
$type = isset($_REQUEST['type']) ? (int) preg_replace("/[^0-9]/", "", $_REQUEST['type']) : 1;

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/listtype.php');
    return;
}

if ($type === 1)      $g5['title'] = '히트상품';
else if ($type === 2) $g5['title'] = '추천상품';
else if ($type === 3) $g5['title'] = '최신상품';
else if ($type === 4) $g5['title'] = '인기상품';
else if ($type === 5) $g5['title'] = '할인상품';
else
    alert('상품유형이 아닙니다.');

include_once('./_head.php');

// 한페이지에 출력하는 이미지수 = $list_mod * $list_row
$list_mod   = $default['de_listtype_list_mod'];   // 한줄에 이미지 몇개씩 출력?
$list_row   = $default['de_listtype_list_row'];   // 한 페이지에 몇라인씩 출력?

$img_width  = $default['de_listtype_img_width'];  // 출력이미지 폭
$img_height = $default['de_listtype_img_height']; // 출력이미지 높이

// 상품 출력순서가 있다면
$order_by = ' it_order, it_id desc ';
if ($sort != '')
    $order_by = $sort.' '.$sortodr.' , it_order, it_id desc';
else
    $order_by = 'it_order, it_id desc';

$skin = isset($skin) ? strip_tags($skin) : '';
if (!$skin || preg_match('#\.+[\\\/]#', $skin))
    $skin = $default['de_listtype_list_skin'];
else
    $skin = preg_replace('#\.+[\\\/]#', '', $skin);

define('G5_SHOP_CSS_URL', G5_SHOP_SKIN_URL);

// 리스트 유형별로 출력
$list_file = G5_SHOP_SKIN_PATH.'/'.$skin;
if (file_exists($list_file)) {
    // 총몇개 = 한줄에 몇개 * 몇줄
    $items = $list_mod * $list_row;
    // 페이지가 없으면 첫 페이지 (1 페이지)
    if ($page < 1) $page = 1;
    // 시작 레코드 구함
    $from_record = ($page - 1) * $items;

    $list = new item_list();
    $list->set_type($type);
    $list->set_list_skin($list_file);
    $list->set_list_mod($list_mod);
    $list->set_list_row($list_row);
    $list->set_img_size($img_width, $img_height);
    $list->set_is_page(true);
    $list->set_order_by($order_by);
    $list->set_from_record($from_record);
    $list->set_view('it_img', true);
    $list->set_view('it_id', false);
    $list->set_view('it_name', true);
    $list->set_view('it_cust_price', false);
    $list->set_view('it_price', true);
    $list->set_view('it_icon', true);
    $list->set_view('sns', true);
    echo $list->run();

    // where 된 전체 상품수
    $total_count = $list->total_count;
    // 전체 페이지 계산
    $total_page  = ceil($total_count / $items);
}
else
{
    echo '<div align="center">'.get_text($skin).' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</div>';
}

$qstr .= '&amp;type='.$type.'&amp;sort='.$sort;
echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page=");

include_once('./_tail.php');