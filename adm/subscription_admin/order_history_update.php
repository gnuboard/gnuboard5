<?php
$sub_menu = '600410';
include_once('./_common.php');

// print_r2($_POST);
// exit;

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$is_ajax = isset($_REQUEST['is_ajax']) ? clean_xss_tags($_REQUEST['is_ajax']) : '';

$od_id = isset($_REQUEST['od_id']) ? clean_xss_tags($_REQUEST['od_id']) : '';
$pay_id = isset($_REQUEST['pay_id']) ? clean_xss_tags($_REQUEST['pay_id']) : 0;
$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';
$hs_id = isset($_REQUEST['hs_id']) ? (int) $_REQUEST['hs_id'] : 0;

$mod_type = isset($_REQUEST['mod_type']) ? clean_xss_tags($_REQUEST['mod_type'], 1, 1) : '';
$od_subscription_history = isset($_REQUEST['od_subscription_history']) ? $_REQUEST['od_subscription_history'] : '';

$result = null;

if ($mod_type === 'add') {
    $result = add_subscription_order_history($od_subscription_history, array(
        'hs_type' => 'subscription_add_admin',
        'od_id' => $od_id,
        'pay_id' => $pay_id,
        'mb_id' => $member['mb_id'],
        'hs_date' => G5_TIME_YMDHIS
    ));
} else if ($mod_type === 'del'){
    // 히스토리 삭제, MySQLi 모드에서 DELETE는 결과를 반환하지 않는다.
    $result = delete_subscription_order_history($hs_id);
}

if ($is_ajax) {
    @header('Content-Type: application/json');
    $response = array(
        "success" => 1,
        "message" => ($mod_type === 'add') ? '추가완료' : '삭제완료'
    );
    
    if ($result && $mod_type === 'add') {

        if (!$result) {
            $response['success'] = 0;
            $response['message'] = '실패';
        }
        $response['hs_id'] = sql_insert_id();
        $response['hs_date'] = G5_TIME_YMDHIS;
        
    }
    
    die(json_encode($response, JSON_UNESCAPED_UNICODE));
}

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page#anc_sodr_history";

$url = "./orderform.php?od_id=$od_id&amp;$qstr";

goto_url($url);
