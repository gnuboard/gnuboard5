<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

$me_id = (int)$_REQUEST['me_id'];

$sql = " select * from {$g5['memo_table']} where me_id = '{$me_id}' ";
$row = sql_fetch($sql);
if (!$row['mb_read_datetime'][0]) // 메모 받기전이면
{
    $sql = " update {$g5['member_table']}
                set mb_memo_call = ''
                where mb_id = '{$row['me_recv_mb_id']}'
                and mb_memo_call = '{$row['me_send_mb_id']}' ";
    sql_query($sql);
}

$sql = " delete from {$g5['memo_table']}
            where me_id = '{$me_id}'
            and (me_recv_mb_id = '{$member['mb_id']}' or me_send_mb_id = '{$member['mb_id']}') ";
sql_query($sql);

goto_url('./memo.php?kind='.$kind);
?>
