<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$sql = " delete from `{$g4['yc4_option_table']}` where it_id = '$it_id' and opt_id = '$opt_id' ";
sql_query($sql);
?>