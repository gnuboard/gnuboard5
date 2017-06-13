<?php
include_once('./_common.php');

if($is_guest)
    die('회원 로그인 후 이용해 주십시오.');

$count = count($_POST['chk']);

if (!$count) {
    alert('수정하실 항목을 하나이상 선택하세요.');
}

if ($is_member && $count) {
    for ($i=0; $i<$count; $i++)
    {
        // 실제 번호를 넘김
        $k = (int) $_POST['chk'][$i];
        $ad_id = (int) $_POST['ad_id'][$k];

        $ad_subject = clean_xss_tags($_POST['ad_subject'][$k]);

        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_subject = '$ad_subject' ";

        if(!empty($_POST['ad_default']) && $ad_id == $_POST['ad_default']) {
            sql_query(" update {$g5['g5_shop_order_address_table']} set ad_default = '0' where mb_id = '{$member['mb_id']}' ");

            $sql .= ", ad_default = '1' ";
        }

        $sql .= " where ad_id = '".$ad_id."'
                    and mb_id = '{$member['mb_id']}' ";

        sql_query($sql);
    }
}

goto_url(G5_SHOP_URL.'/orderaddress.php');
?>