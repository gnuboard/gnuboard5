<?php
include_once("./_common.php");

if (!$is_member) die("0");

$as_id = (int)$_REQUEST['as_id'];

$sql = " delete from {$g5['autosave_table']} where mb_id = '{$member['mb_id']}' and as_id = {$as_id} ";
$result = sql_query($sql);
if (!$result) {
    echo "-1";
}

echo autosave_count($member['mb_id']);
?>