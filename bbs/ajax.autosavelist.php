<?php
include_once('./_common.php');

if (!$is_member) die('');

$sql = " select as_id, as_subject, as_datetime from {$g4['autosave_table']} where mb_id = '{$member['mb_id']}' order by as_id desc ";
$result = sql_query($sql);
$arr = array();
for ($i=0; $row=sql_fetch_array($result); $i++) {
    //$subject  = utf8_strcut(stripslashes($row['as_subject']), 25);
    $subject  = htmlspecialchars(utf8_strcut($row['as_subject'], 25), ENT_QUOTES);
    $datetime = substr($row['as_datetime'],2,14);
    $arr[] = "{\"id\": \"{$row['as_id']}\", \"subject\": \"{$subject}\", \"datetime\": \"{$datetime}\"}";
}
echo "{\"autosave\":[".implode(", ", $arr)."]}";
?>