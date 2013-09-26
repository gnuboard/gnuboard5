<?php
include_once('./_common.php');

if(get_magic_quotes_gpc())
{
    $_GET  = array_add_callback("stripslashes", $_GET);
    $_POST = array_add_callback("stripslashes", $_POST);
}

$_GET  = array_add_callback("mysql_real_escape_string", $_GET);
$_POST = array_add_callback("mysql_real_escape_string", $_POST);

// orderview 에서 사용하기 위해 session에 넣고
$uid = md5($od_id.G5_TIME_YMDHIS.$REMOTE_ADDR);
set_session('ss_orderview_uid', $uid);

// 배송지처리
if($is_member && $ad_default) {

    $sql = " select ad_id
                from {$g5['g5_shop_order_address_table']}
                where mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);

    if($ad_default) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_default = '0'
                    where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    if($row['ad_id']) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ";
        if($ad_default)
            $sql .= " ad_default = '$ad_default' ";
        if($ad_subject)
            $sql .= " , ad_subject = '$ad_subject' ";
        $sql .= " where ad_id = '{$row['ad_id']}'
                    and mb_id = '{$member['mb_id']}' ";
        sql_query($sql);

    if(!$ad_default && $add_subject) {
        $sql = " insert into {$g5['g5_shop_order_address_table']}
                    set mb_id       = '{$member['mb_id']}',
                        ad_subject  = '$ad_subject',
                        ad_default  = '$ad_default',
                        ad_name     = '$od_b_name',
                        ad_tel      = '$od_b_tel',
                        ad_hp       = '$od_b_hp',
                        ad_zip1     = '$od_b_zip1',
                        ad_zip2     = '$od_b_zip2',
                        ad_addr1    = '$od_b_addr1',
                        ad_addr2    = '$od_b_addr2' ";
        sql_query($sql);
    }

    }
}
print_r ($row);
echo $member['mb_id']."멤버아이디<br>";
echo $ad_default."디폴트값<br>";
echo $ad_subject. "제목<br>";
echo $row['ad_id']. "고유 아이디<br>";
//goto_url(G5_SHOP_URL.'/orderaddress.php?od_id='.$od_id.'&amp;uid='.$uid);
?>