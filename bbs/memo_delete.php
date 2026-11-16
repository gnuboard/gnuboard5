<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

// 삭제 토큰은 쪽지함·쪽지 보기 화면에서 회원별로 발급하고 한 번 쓰면 폐기한다. (KVE-2026-3203)
$token = isset($_REQUEST['token']) && is_string($_REQUEST['token']) ? $_REQUEST['token'] : '';
if (!g5_check_form_token('memo_delete', $member['mb_id'], $token, 3600))
    alert('토큰 에러로 삭제 불가합니다.');

$kind = isset($_REQUEST['kind']) && is_string($_REQUEST['kind']) ? $_REQUEST['kind'] : '';
if ($kind !== 'recv' && $kind !== 'send')
    alert('kind 변수 값이 올바르지 않습니다.');

$me_id = isset($_REQUEST['me_id']) ? (int) $_REQUEST['me_id'] : 0;
$sql_mb_id = sql_escape_string($member['mb_id']);

// 요청한 보관함의 자기 행만 삭제한다. 수신함은 recv 행의 수신자, 발신함은 send 행의 발신자만 지울 수 있다.
$sql_owner = " me_id = '{$me_id}' and me_type = '{$kind}' and me_{$kind}_mb_id = '{$sql_mb_id}' ";
$row = sql_fetch(" select * from {$g5['memo_table']} where {$sql_owner} ");
if (empty($row['me_id']))
    alert('쪽지가 존재하지 않거나 삭제할 수 없습니다.', './memo.php?kind='.$kind);

sql_query(" delete from {$g5['memo_table']} where {$sql_owner} ");
$left = sql_fetch(" select me_id from {$g5['memo_table']} where me_id = '{$me_id}' ");
if (!empty($left['me_id']))
    alert('쪽지를 삭제하지 못했습니다.', './memo.php?kind='.$kind);

// 자기 미열람 수신 쪽지를 지운 경우에만 자신의 새 쪽지 알림과 미열람 수를 다시 맞춘다.
// 발신함 기록을 지울 때는 상대 수신자의 상태를 바꾸지 않는다.
if ($kind === 'recv' && !$row['me_read_datetime'][0]) // 메모 받기전이면
{
    $sql = " update {$g5['member_table']}
                set mb_memo_call = ''
                where mb_id = '{$sql_mb_id}'
                and mb_memo_call = '".sql_escape_string($row['me_send_mb_id'])."' ";
    sql_query($sql);

    $sql = " update `{$g5['member_table']}` set mb_memo_cnt = '".get_memo_not_read($member['mb_id'])."' where mb_id = '{$sql_mb_id}' ";
    sql_query($sql);
}

run_event('memo_delete', $me_id, $row);

goto_url('./memo.php?kind='.$kind);