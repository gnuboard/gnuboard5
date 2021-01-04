<?php
// 이모티콘 그룹 이동
$sub_menu = "900500";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "w");

$fg_no = isset($_REQUEST['fg_no']) ? (int) $_REQUEST['fg_no'] : 0;
$move_no = isset($_REQUEST['move_no']) ? (int) $_REQUEST['move_no'] : 0;

if ($fg_no) 
{
    $res = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");
    if ($res)
        $fg_count = $res['fg_count'];
    else
        $fg_count = 0;
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + $fg_count where fg_no = '$move_no'");
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = 0 where fg_no='$fg_no'");
}
else
{
    $fg_count = sql_fetch("select count(*) as cnt from {$g5['sms5_form_table']} where fg_no = 0");
    $fg_count = $fg_count['cnt'];
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + $fg_count where fg_no = '$move_no'");
}

$group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$move_no'");

sql_query("update {$g5['sms5_form_table']} set fg_no = '$move_no', fg_member = '{$group['fg_member']}' where fg_no = '$fg_no'");

goto_url('./form_group.php');