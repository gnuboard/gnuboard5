<?
$sub_menu = "400720";
include_once("./_common.php");

if ($w == "u" || $w == "d") 
    check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$sql_common = " nw_begin_time = '$nw_begin_time',
                nw_end_time = '$nw_end_time',
                nw_disable_hours = '$nw_disable_hours',
                nw_left = '$nw_left',
                nw_top = '$nw_top',
                nw_height = '$nw_height',
                nw_width = '$nw_width',
                nw_subject = '$nw_subject',
                nw_content = '$nw_content',
                nw_content_html = '$nw_content_html' ";

if($w == "") 
{
    $sql = " alter table $g4[yc4_new_win_table] auto_increment=1 ";
    sql_query($sql);

    $sql = " insert $g4[yc4_new_win_table] set $sql_common ";
    sql_query($sql);

    $nw_id = mysql_insert_id();
} 
else if ($w == "u") 
{
    $sql = " update $g4[yc4_new_win_table] set $sql_common where nw_id = '$nw_id' ";
    sql_query($sql);
} 
else if ($w == "d") 
{
    $sql = " delete from $g4[yc4_new_win_table] where nw_id = '$nw_id' ";
    sql_query($sql);
}

if ($w == "d") 
{
    goto_url("./newwinlist.php");
} 
else 
{
    goto_url("./newwinform.php?w=u&nw_id=$nw_id");
}
?>
