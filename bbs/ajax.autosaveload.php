<?php
include_once('./_common.php');

if (!$is_member) die('');

$as_id = (int)$_REQUEST['as_id'];

$sql = " select as_subject, as_content from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' and as_id = {$as_id} ";
$row = sql_fetch($sql);
$subject = $row['as_subject'];
$content = $row['as_content'];

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<item>\n";
echo "<subject><![CDATA[{$subject}]]></subject>\n";
echo "<content><![CDATA[{$content}]]></content>\n";
echo "</item>\n";
?>