<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$sql = " delete from `{$g4['yc4_supplement_table']}` where it_id = '$it_id' and sp_id = '$sp_id' ";
sql_query($sql);
?>