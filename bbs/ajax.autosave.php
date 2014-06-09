<?php
include_once('./_common.php');

if (!$is_member) die('0');

$uid     = trim($_REQUEST['uid']);
$subject = trim($_REQUEST['subject']);
$content = trim($_REQUEST['content']);

if ($subject && $content) {
    $sql = " select count(*) as cnt from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' and as_subject = '$subject' and as_content = '$content' ";
    $row = sql_fetch($sql);
    if (!$row['cnt']) {
        $sql = " insert into {$g5['autosave_table']} set mb_id = '{$member['mb_id']}', as_uid = '{$uid}', as_subject = '$subject', as_content = '$content', as_datetime = '".G5_TIME_YMDHIS."' on duplicate key update as_subject = '$subject', as_content = '$content', as_datetime = '".G5_TIME_YMDHIS."' ";
        $result = sql_query($sql, false);

        echo autosave_count($member['mb_id']);
    }
}
?>