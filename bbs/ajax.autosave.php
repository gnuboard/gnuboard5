<?php
include_once('./_common.php');

if (!$is_member) die('0');

$uid     = escape_trim($_REQUEST['uid']);
$subject = escape_trim($_REQUEST['subject']);
$content = escape_trim($_REQUEST['content']);

/*
$uid = get_session("ss_autosave_uid");
if (!$uid) {
    $uid = get_uniqid();
    set_session("ss_autosave_uid", $uid);
}
*/

if ($content) {
    $sql = " insert into {$g4['autosave_table']} set mb_id = '{$member['mb_id']}', as_uid = '{$uid}', as_subject = '$subject', as_content = '$content', as_datetime = '".G4_TIME_YMDHIS."' on duplicate key update as_subject = '$subject', as_content = '$content', as_datetime = '".G4_TIME_YMDHIS."' ";
    $result = sql_query($sql, false);

    echo autosave_count($member['mb_id']);
}
?>