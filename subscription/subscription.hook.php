<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_event('g5_shop_mypage_sub_top', 'subscription_add_mypage_sub', 1, 0);

function subscription_add_mypage_sub()
{
    global $config, $g5, $member, $is_member, $is_mobile;

    if (!$is_member) {
        return '';
    }
    $limit = " limit 0, 5 ";

    $sql = " select *
               from {$g5['g5_subscription_order_table']}
              where mb_id = '{$member['mb_id']}'
              order by od_id desc
              $limit ";
    $result = sql_query($sql);

    $ods = array();

    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $row['goods'] = get_subscription_pay_full_goods($row['od_id']);
        $ods[] = $row;
    }

    $cnt = count($ods);

    if ($is_mobile) {   // 모바일이면
        include_once(G5_MSUBSCRIPTION_PATH . '/mypage_top.php');
    } else {
        include_once(G5_SUBSCRIPTION_PATH . '/mypage_top.php');
    }

    /*
    if () {
        
    }
    */
}
