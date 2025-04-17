<?php
include_once('./_common.php');

if (!$is_member) die('0');

$uid     = isset($_REQUEST['uid']) ? preg_replace('/[^0-9]/', '', $_REQUEST['uid']) : 0;
$subject = isset($_REQUEST['subject']) ? preg_replace("#[\\\]+$#", "", substr(trim($_POST['subject']),0,255)) : '';
$content = isset($_REQUEST['content']) ? preg_replace("#[\\\]+$#", "", substr(trim($_POST['content']),0,65536)) : '';

if ($subject && $content) {
    $sql = " select count(*) as cnt from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' and as_subject = '$subject' and as_content = '$content' ";
    $row = sql_fetch($sql);
    if (!$row['cnt']) {
        $sql = " insert into {$g5['autosave_table']} set mb_id = '{$member['mb_id']}', as_uid = '{$uid}', as_subject = '$subject', as_content = '$content', as_datetime = '".G5_TIME_YMDHIS."' on duplicate key update as_subject = '$subject', as_content = '$content', as_datetime = '".G5_TIME_YMDHIS."' ";
        $result = sql_query($sql, false);

        echo autosave_count($member['mb_id']);
    }
}