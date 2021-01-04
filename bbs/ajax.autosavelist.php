<?php
include_once('./_common.php');

if (!$is_member) die('');

$sql = " select as_id, as_uid, as_subject, as_datetime from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' order by as_id desc ";
$result = sql_query($sql);
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<list>\n";
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $subject  = htmlspecialchars(utf8_strcut($row['as_subject'], 25), ENT_QUOTES);
    $datetime = substr($row['as_datetime'],2,14);
    echo "<item>\n";
    echo "<id>{$row['as_id']}</id>\n";
    echo "<uid>{$row['as_uid']}</uid>\n";
    echo "<subject><![CDATA[{$subject}]]></subject>\n";
    echo "<datetime>{$datetime}</datetime>\n";
    echo "</item>\n";
}
echo "</list>";