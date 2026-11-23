<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

// 삭제는 스크랩 목록에서 발급한 1회용 토큰을 실은 POST로만 처리한다(교차 사이트 GET 삭제 차단).
$scrap_delete_token = isset($_POST['token']) ? $_POST['token'] : '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !g5_check_form_token('scrap_delete', $member['mb_id'], $scrap_delete_token, 3600))
    alert('올바른 방법으로 이용해 주십시오.', './scrap.php?page='.$page);

$ms_id = isset($_POST['ms_id']) ? (int) $_POST['ms_id'] : 0;

$sql = " delete from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' and ms_id = '$ms_id' ";
sql_query($sql);

$sql = " update `{$g5['member_table']}` set mb_scrap_cnt = '".get_scrap_totals($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
sql_query($sql);

goto_url('./scrap.php?page='.$page);