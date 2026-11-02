<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_event('memo_list', 'g54_user_memo_insert', 10, 3);
add_event('password_is_wrong', 'g54_check_bbs_password', 10, 3);
add_replace('invalid_password', 'g54_return_invalid_password', 10, 3);

function g54_return_invalid_password($bool, $type, $wr){
    if($type === 'write' && $bool === false && $wr['wr_password'] && isset($_POST['wr_password'])) {
        if(G5_STRING_ENCRYPT_FUNCTION === 'create_hash' && (strlen($wr['wr_password']) === G5_MYSQL_PASSWORD_LENGTH || strlen($wr['wr_password']) === 16)) {
            if( sql_password($_POST['wr_password']) === $wr['wr_password'] ){
                $bool = true;
            }
        }
    }

    return $bool;
}

function g54_check_bbs_password($type, $wr, $qstr=''){
    if($type === 'bbs' && (isset($wr['wr_password']) && $wr['wr_password']) && isset($_POST['wr_password'])) {

        global $bo_table, $w, $g5;

        if(G5_STRING_ENCRYPT_FUNCTION === 'create_hash' && (strlen($wr['wr_password']) === G5_MYSQL_PASSWORD_LENGTH || strlen($wr['wr_password']) === 16)) {
            if( sql_password($_POST['wr_password']) === $wr['wr_password'] ){
                // password_check.php에서 대상 유형과 부모 접근권한을 확인한 뒤 호출한다.
                // 구 비밀번호 형식도 게시물별 인증만 부여한다.
                if (($w === 's' && empty($wr['wr_is_comment'])) ||
                    ($w === 'sc' && !empty($wr['wr_is_comment']))) {
                    $auth_write = get_write($g5['write_prefix'].$bo_table, $wr['wr_id']);
                    g5_grant_secret_auth($bo_table, $auth_write);
                    $return_id = $w === 'sc' ? (int)$wr['wr_parent'] : (int)$wr['wr_id'];
                    goto_url(get_pretty_url($bo_table, $return_id));
                }
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
