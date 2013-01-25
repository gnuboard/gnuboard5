<?php
$sub_menu = "400750";
include_once("./_common.php");

auth_check($auth[$sub_menu], "d");

$sql = " delete from {$g4['yc4_sendcost_table']} where sc_no = '$sc_no' ";
sql_query($sql);

goto_url("./sendcostlist.php");
?>