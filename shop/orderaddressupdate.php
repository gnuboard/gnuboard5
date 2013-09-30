<?php
include_once('./_common.php');

if (!count($_POST['chk'])) {
    alert('수정하실 항목을 하나이상 선택하세요.');
    exit;
}

if ($is_member && count($_POST['chk'])) {

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        // 기본배송지 값이 안넘어올때 (기본배송지를 설정 안했을시)
        $sql = " update `{$g5['g5_shop_order_address_table']}` ";
        // 기본배송지 값이 있지만 선택한 배송지와 기존의 기본배송지가 다를때
        if ($_POST['ad_id'][$k] != $_POST['ad_default']){
            $sql .= " set ad_subject  = '{$_POST['ad_subject'][$k]}'
                          where ad_id = '{$_POST['ad_id'][$k]}'
                          and mb_id   = '{$member['mb_id']}' ";
            sql_query($sql);
        }
        // 체크한 값이 기본배송지로 선택 되었다면
        else if ($_POST['ad_id'][$k] == $_POST['ad_default']){
            $sql .= " set ad_default  = '0'
                          where mb_id   = '{$member['mb_id']}' ";
            sql_query($sql);

            $sql = " update `{$g5['g5_shop_order_address_table']}`
                         set ad_subject  = '{$_POST['ad_subject'][$k]}',
                         ad_default = '1'
                         where ad_id = '{$_POST['ad_id'][$k]}' 
                         and mb_id   = '{$member['mb_id']}' ";
            sql_query($sql);
        }else{
            $sql .= " set ad_subject  = '{$_POST['ad_subject'][$k]}'
                          where ad_id = '{$_POST['ad_id'][$k]}' 
                          and mb_id   = '{$member['mb_id']}' ";
            sql_query($sql);
        }
    }

}

goto_url(G5_SHOP_URL.'/orderaddress.php?od_id='.$od_id.'&amp;uid='.$uid);
?>