<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원 전용 서비스 입니다.', G5_BBS_URL.'/login.php?url='.urlencode($url));

if ($w == "d")
{
    $wi_id = trim($_GET['wi_id']);
    $sql = " delete from {$g5['g5_shop_wish_table']}
              where wi_id = '$wi_id'
                and mb_id = '{$member['mb_id']}' ";
    sql_query($sql);
}
else
{
    if(is_array($it_id))
        $it_id = $_POST['it_id'][0];

    $sql_common = " set mb_id = '{$member['mb_id']}',
                        it_id = '$it_id',
                        wi_time = '".G5_TIME_YMDHIS."',
                        wi_ip = '$REMOTE_ADDR' ";

    $sql = " select wi_id from {$g5['g5_shop_wish_table']}
              where mb_id = '{$member['mb_id']}' and it_id = '$it_id' ";
    $row = sql_fetch($sql);
    if ($row['wi_id']) { // 이미 있다면 삭제함
        $sql = " delete from {$g5['g5_shop_wish_table']} where wi_id = '{$row['wi_id']}' ";
        sql_query($sql);
    }

    $sql = " insert {$g5['g5_shop_wish_table']}
                set mb_id = '{$member['mb_id']}',
                    it_id = '$it_id',
                    wi_time = '".G5_TIME_YMDHIS."',
                    wi_ip = '$REMOTE_ADDR' ";
    sql_query($sql);
}

goto_url('./wishlist.php');
?>