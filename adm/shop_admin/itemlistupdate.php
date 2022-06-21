<?php
$sub_menu = '400300';
include_once('./_common.php');

check_demo();

check_admin_token();

$count_post_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$post_act_button = isset($_POST['act_button']) ? $_POST['act_button'] : '';

if (! $count_post_chk) {
    alert($post_act_button." 하실 항목을 하나 이상 체크하세요.");
}

if ($post_act_button == "선택수정") {

    auth_check_menu($auth, $sub_menu, 'w');

    for ($i=0; $i< $count_post_chk; $i++) {

        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        if( ! (isset($_POST['ca_id'][$k]) && $_POST['ca_id'][$k])) {
            alert("기본분류는 반드시 선택해야 합니다.");
        }

        $p_ca_id = (isset($_POST['ca_id']) && is_array($_POST['ca_id'])) ? strip_tags($_POST['ca_id'][$k]) : '';
        $p_ca_id2 = (isset($_POST['ca_id2']) && is_array($_POST['ca_id2'])) ? strip_tags($_POST['ca_id2'][$k]) : '';
        $p_ca_id3 = (isset($_POST['ca_id3']) && is_array($_POST['ca_id3'])) ? strip_tags($_POST['ca_id3'][$k]) : '';
        $p_it_name = (isset($_POST['it_name']) && is_array($_POST['it_name'])) ? strip_tags(clean_xss_attributes($_POST['it_name'][$k])) : '';
        $p_it_cust_price = (isset($_POST['it_cust_price']) && is_array($_POST['it_cust_price'])) ? strip_tags($_POST['it_cust_price'][$k]) : '';
        $p_it_price = (isset($_POST['it_price']) && is_array($_POST['it_price'])) ? strip_tags($_POST['it_price'][$k]) : '';
        $p_it_stock_qty = (isset($_POST['it_stock_qty']) && is_array($_POST['it_stock_qty'])) ? strip_tags($_POST['it_stock_qty'][$k]) : '';
        $p_it_skin = (isset($_POST['it_skin']) && is_array($_POST['it_skin'])) ? strip_tags($_POST['it_skin'][$k]) : '';
        $p_it_mobile_skin = (isset($_POST['it_mobile_skin']) && is_array($_POST['it_mobile_skin'])) ? strip_tags($_POST['it_mobile_skin'][$k]) : '';
        $p_it_use       = isset($_POST['it_use'][$k])       ? clean_xss_tags($_POST['it_use'][$k], 1, 1)        : 0;
        $p_it_soldout   = isset($_POST['it_soldout'][$k])   ? clean_xss_tags($_POST['it_soldout'][$k], 1, 1)    : 0;
        $p_it_order = (isset($_POST['it_order']) && is_array($_POST['it_order'])) ? strip_tags($_POST['it_order'][$k]) : '';
        $p_it_id = isset($_POST['it_id'][$k]) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]) : '';

        if ($is_admin != 'super') {     // 최고관리자가 아니면 체크
            $sql = "select a.it_id, b.ca_mb_id from {$g5['g5_shop_item_table']} a , {$g5['g5_shop_category_table']} b where (a.ca_id = b.ca_id) and a.it_id = '$p_it_id'";
            $checks = sql_fetch($sql);

            if( ! $checks['ca_mb_id'] || $checks['ca_mb_id'] !== $member['mb_id'] ){
                continue;
            }
        }

        $sql = "update {$g5['g5_shop_item_table']}
                   set ca_id          = '".sql_real_escape_string($p_ca_id)."',
                       ca_id2         = '".sql_real_escape_string($p_ca_id2)."',
                       ca_id3         = '".sql_real_escape_string($p_ca_id3)."',
                       it_name        = '".$p_it_name."',
                       it_cust_price  = '".sql_real_escape_string($p_it_cust_price)."',
                       it_price       = '".sql_real_escape_string($p_it_price)."',
                       it_stock_qty   = '".sql_real_escape_string($p_it_stock_qty)."',
                       it_skin        = '".sql_real_escape_string($p_it_skin)."',
                       it_mobile_skin = '".sql_real_escape_string($p_it_mobile_skin)."',
                       it_use         = '".sql_real_escape_string($p_it_use)."',
                       it_soldout     = '".sql_real_escape_string($p_it_soldout)."',
                       it_order       = '".sql_real_escape_string($p_it_order)."',
                       it_update_time = '".G5_TIME_YMDHIS."'
                 where it_id   = '".$p_it_id."' ";

        sql_query($sql);

		if( function_exists('shop_seo_title_update') ) shop_seo_title_update($p_it_id, true);
    }
} else if ($post_act_button == "선택삭제") {

    if ($is_admin != 'super')
        alert('상품 삭제는 최고관리자만 가능합니다.');

    auth_check_menu($auth, $sub_menu, 'd');

    // _ITEM_DELETE_ 상수를 선언해야 itemdelete.inc.php 가 정상 작동함
    define('_ITEM_DELETE_', true);

    for ($i=0; $i<$count_post_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        // include 전에 $it_id 값을 반드시 넘겨야 함
        $it_id = isset($_POST['it_id'][$k]) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]) : '';
        include ('./itemdelete.inc.php');
    }
}

goto_url("./itemlist.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");