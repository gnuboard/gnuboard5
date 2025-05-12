<?php
if (!defined('_GNUBOARD_')) exit;

// 관리자 분류 업데이트시 hook
add_event('shop_admin_category_created', 'fn_subscription_update_category', 1, 1);
add_event('shop_admin_category_updated', 'fn_subscription_update_category', 1, 1);

// 관리자 상품 업데이트시 hook
add_event('shop_admin_itemformupdate', 'fn_shop_admin_itemformupdate', 1, 2);

// 관리자 분류 업데이트시 처리 함수
function fn_subscription_update_category($ca_id) {
    global $g5, $w;

    if (isset($_POST['ca_class_num'])) {
        $ca_class_num = (int) $_POST['ca_class_num'];
        $sql = " update {$g5['g5_shop_category_table']} set ca_class_num = '$ca_class_num' where ca_id = '$ca_id' ";
        
        sql_query($sql, false);
    }
}

// 관리자 상품 업데이트시 처리 함수
function fn_shop_admin_itemformupdate($it_id, $w) {
    global $g5, $w, $ca_id;

    if (isset($_POST['it_class_num'])) {
        $it_class_num = (int) $_POST['it_class_num'];
        $ca_fields = '';
        
        $sql = " update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where it_id = '$it_id' ";
        
        sql_query($sql, false);
        
        $ca_id = preg_replace('/[^0-9a-z]/i', '', $ca_id);
        
        // 분류적용
        if (isset($_POST['chk_ca_it_class_num']) && is_checked('chk_ca_it_class_num') && $ca_id) {
            
            sql_query(" update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where ca_id = '$ca_id' ", false);
        } else {
            
            $sql = " update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where it_id = '$it_id' ";
            sql_query($sql, false);
        }
        
        // 전체적용
        if (isset($_POST['chk_all_it_class_num'])) {
            sql_query(" update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' ");
        }
    }
}