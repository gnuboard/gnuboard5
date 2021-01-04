<?php
include_once('./_common.php');

$ms_id = isset($_REQUEST['ms_id']) ? (int) $_REQUEST['ms_id'] : 0;

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

$sql = " delete from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' and ms_id = '$ms_id' ";
sql_query($sql);

$sql = " update `{$g5['member_table']}` set mb_scrap_cnt = '".get_scrap_totals($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
sql_query($sql);

goto_url('./scrap.php?page='.$page);