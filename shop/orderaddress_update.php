<?php
include_once('./_common.php');

//print_r2($_POST); exit;

if ($is_member && count($_POST['chk'])) {

    // 해당 회원에 대한 기본배송지 클리어
    if (isset($_POST['ad_default'])) {
        $sql = " update `{$g5['g5_shop_order_address_table']}` 
                    set ad_default  = 0
                  where mb_id       = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " update `{$g5['g5_shop_order_address_table']}`
                    set ad_subject  = '{$_POST['ad_subject'][$k]}' ";
        // 기본배송지로 선택 되었다면
        if ($_POST['ad_id'][$k] == $_POST['ad_default'])
            $sql .= " , ad_default  = '1' ";
        $sql .= " where ad_id       = '{$_POST['ad_id'][$k]}' 
                    and mb_id       = '{$member['mb_id']}' ";
        sql_query($sql);
    }
}

goto_url(G5_SHOP_URL.'/orderaddress.php?od_id='.$od_id.'&amp;uid='.$uid);
?>