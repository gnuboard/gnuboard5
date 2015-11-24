<?php
$sub_menu = '100310';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

check_admin_token();

$sql_common = " nw_device = '{$_POST['nw_device']}',
                nw_begin_time = '{$_POST['nw_begin_time']}',
                nw_end_time = '{$_POST['nw_end_time']}',
                nw_disable_hours = '{$_POST['nw_disable_hours']}',
                nw_left = '{$_POST['nw_left']}',
                nw_top = '{$_POST['nw_top']}',
                nw_height = '{$_POST['nw_height']}',
                nw_width = '{$_POST['nw_width']}',
                nw_subject = '{$_POST['nw_subject']}',
                nw_content = '{$_POST['nw_content']}',
                nw_content_html = '{$_POST['nw_content_html']}' ";

if($w == "")
{
    $sql = " insert {$g5['new_win_table']} set $sql_common ";
    sql_query($sql);

    $nw_id = sql_insert_id();
}
else if ($w == "u")
{
    $sql = " update {$g5['new_win_table']} set $sql_common where nw_id = '$nw_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    $sql = " delete from {$g5['new_win_table']} where nw_id = '$nw_id' ";
    sql_query($sql);
}

if ($w == "d")
{
    goto_url('./newwinlist.php');
}
else
{
    goto_url("./newwinform.php?w=u&amp;nw_id=$nw_id");
}
?>
