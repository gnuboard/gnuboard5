<?php
include_once('./_common.php');

if($is_guest)
    die('회원 로그인 후 이용해 주십시오.');

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$count) {
    alert('수정하실 항목을 하나이상 선택하세요.');
}

if ($is_member && $count) {
    for ($i=0; $i<$count; $i++)
    {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $ad_id = isset($_POST['ad_id'][$k]) ? (int) $_POST['ad_id'][$k] : 0;

        $ad_subject = isset($_POST['ad_subject'][$k]) ? clean_xss_tags($_POST['ad_subject'][$k]) : '';

        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_subject = '".sql_real_escape_string($ad_subject)."' ";

        if(!empty($_POST['ad_default']) && $ad_id === $_POST['ad_default']) {
            sql_query(" update {$g5['g5_shop_order_address_table']} set ad_default = '0' where mb_id = '{$member['mb_id']}' ");

            $sql .= ", ad_default = '1' ";
        }

        $sql .= " where ad_id = '".$ad_id."'
                    and mb_id = '{$member['mb_id']}' ";

        sql_query($sql);
    }
}

goto_url(G5_SHOP_URL.'/orderaddress.php');