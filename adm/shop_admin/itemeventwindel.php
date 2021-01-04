<?php
$sub_menu = '500300';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "d");

$ev_id = isset($_REQUEST['ev_id']) ? (int) $_REQUEST['ev_id'] : 0;
$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

$sql = " delete from {$g5['g5_shop_event_item_table']} where ev_id = '$ev_id' and it_id = '$it_id' ";
sql_query($sql);

goto_url("./itemeventwin.php?ev_id=$ev_id");