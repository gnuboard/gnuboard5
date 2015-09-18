<?php
$sub_menu = '500300';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "d");

$sql = " delete from {$g5['g5_shop_event_item_table']} where ev_id = '$ev_id' and it_id = '$it_id' ";
sql_query($sql);

goto_url("./itemeventwin.php?ev_id=$ev_id");
?>
