<?php
include_once('./_common.php');

if (!$is_member) die('');

$as_id = (int)$_REQUEST['as_id'];

$sql = " select as_subject, as_content from {$g4['autosave_table']} where mb_id = '{$member['mb_id']}' and as_id = {$as_id} ";
$row = sql_fetch($sql);
//$subject = stripslashes($row['as_subject']);
//$content = stripslashes($row['as_content']);
$subject = str_replace("\'", "\\\'", addslashes($row['as_subject']));
$content = stripslashes($row['as_content']);
echo "{\"subject\":\"{$subject}\", \"content\":\"{$content}\"}";
?>