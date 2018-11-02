<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

put_event('member_login_check', 'g54_userlogin_after', 10, 2);
put_event('memo_list', 'g54_user_memo_insert', 10, 3);

// 5.3 버전에서 
function g54_userlogin_after($mb, $link){
    global $g5;

    if(isset($mb['mb_id']) && $mb['mb_id']){

        $mb_img_paths = array(G5_DATA_PATH.'/member/'.substr($mb['mb_id'], 0, 2).'/', G5_DATA_PATH.'/member_image/'.substr($mb['mb_id'], 0, 2).'/');
        
        $mb_id = $mb['mb_id'];

        foreach( $mb_img_paths as $path ){
            $before_file = $path.$mb_id.'.gif';
            $after_file = $path.get_mb_icon_name($mb_id).'.gif';
            
            // 회원 아이콘 또는 프로필 파일 이름을 변경
            if( ($before_file !== $after_file) && file_exists($before_file) && ! file_exists($after_file) ){
                @rename($before_file, $after_file);
            }
        }
    }
}

function g54_user_memo_insert($kind, $unkind, $page=1){
    global $g5, $is_member, $member;

    if( ! $is_member || $kind !== 'send' ) return;

    $sql = " select count(*) as cnt from {$g5['memo_table']} where me_send_mb_id = '{$member['mb_id']}' and me_type = 'recv' and me_send_ip = '' ";
    $row = sql_fetch($sql);

    if ( !$row['cnt'] ) return;

    $sql = " select count(*) as cnt from {$g5['memo_table']} where me_send_mb_id = '{$member['mb_id']}' and me_type = 'send' ";
    $row2 = sql_fetch($sql);

    if( $row['cnt'] && ! $row2['cnt'] ){
        $sql = " select * from {$g5['memo_table']} where me_send_mb_id = '{$member['mb_id']}' and me_type = 'recv' ";
        $result = sql_query($sql);

        while ($row = sql_fetch_array($result))
        {
            $sql = " insert into {$g5['memo_table']} ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_read_datetime, me_memo, me_send_id, me_type ) values ( '".addslashes($row['me_recv_mb_id'])."', '".addslashes($row['me_send_mb_id'])."', '".addslashes($row['me_send_datetime'])."', '".addslashes($row['me_read_datetime'])."', '".addslashes($row['me_memo'])."', '".$row['me_id']."', 'send' ) ";

            sql_query($sql);
        }

        $sql = " update {$g5['memo_table']} set me_send_ip = '{$_SERVER['REMOTE_ADDR']}' where me_send_mb_id = '{$member['mb_id']}' and me_type = 'recv' and me_send_ip = '' ";

        sql_query($sql);
    }

}
?>