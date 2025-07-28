<?php
include_once('./_common.php');

if (!$is_member) {
    goto_url(G5_BBS_URL . "/login.php?url=" . urlencode(G5_SUBSCRIPTION_URL . "/mycard.php"));
}

if ($w == "d")
{
    $ci_id = isset($_REQUEST['ci_id']) ? (int) $_REQUEST['ci_id'] : 0;
    
    if (!$ci_id) {
        alert('잘못된 요청입니다.');
    }
    
    $sql = " select * from {$g5['g5_subscription_mb_cardinfo_table']} where ci_id = '$ci_id' ";
    $row = sql_fetch($sql);

    if (isset($row['mb_id']) && $row['mb_id'] != $member['mb_id']) {
        alert('삭제할 권한이 없습니다.');
    }
    
    $sql = "select od_id from {$g5['g5_subscription_order_table']} where od_enable_status = 1 and ci_id = '{$row['ci_id']}' and mb_id = '{$member['mb_id']}' ";
    
    $result = sql_query($sql);
    
    for ($i = 0; $od = sql_fetch_array($result); $i++) {
        add_subscription_order_history('카드삭제로 인해 비활성화 되었습니다.', array(
            'hs_type' => 'subscription_disable_order',
            'od_id' => $od['od_id'],
            'mb_id' => $member['mb_id']
        ));
    }
    
    // 해당 주문들을 비활성화 합니다.
    $sql = "update {$g5['g5_subscription_order_table']} set od_enable_status = 0 where ci_id = '{$row['ci_id']}' and mb_id = '{$member['mb_id']}' ";
    
    sql_query($sql);
    
    // 카드테이블에서 해당 카드정보를 삭제합니다.
    $sql = " delete from {$g5['g5_subscription_mb_cardinfo_table']}
              where (ci_id = '{$row['ci_id']}' or (pg_service = '{$row['pg_service']}' and card_mask_number = '{$row['card_mask_number']}')) and mb_id = '{$member['mb_id']}' ";
              
   sql_query($sql);
}

goto_url(G5_SUBSCRIPTION_URL . '/mycard.php');