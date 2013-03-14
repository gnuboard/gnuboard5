<?
$sub_menu = "300300";
include_once("./_common.php");

if ($w == "u" || $w == "d") 
    check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$sql_common = " di_ui_theme = '$di_ui_theme',
                di_begin_time = '$di_begin_time',
                di_end_time = '$di_end_time',
                di_subject = '".addslashes($di_subject)."',
                di_content = '".addslashes($di_content)."',
                di_speeds = '$di_speeds',
                di_disable_hours = '$di_disable_hours',
                di_position = '$di_position',
                di_draggable = '$di_draggable',
                di_height = '$di_height',
                di_width = '$di_width',
                di_modal = '$di_modal',
                di_resizable = '$di_resizable',
                di_show = '$di_show',
                di_hide = '$di_hide', 
                di_escape = '$di_escape',
                di_zindex = '$di_zindex'
                ";

if($w == "") 
{
    $sql = " alter table $g4[dialog_table] auto_increment=1 ";
    sql_query($sql);

    $sql = " insert $g4[dialog_table] set $sql_common ";
    sql_query($sql);

    $di_id = mysql_insert_id();
} 
else if ($w == "u") 
{
    $sql = " update $g4[dialog_table] set $sql_common where di_id = '$di_id' ";
    sql_query($sql);
} 
else if ($w == "d") 
{
    $sql = " delete from $g4[dialog_table] where di_id = '$di_id' ";
    sql_query($sql);
}

if ($w == "d") 
{
    goto_url("./dialog_list.php");
} 
else 
{
    goto_url("./dialog_form.php?w=u&di_id=$di_id");
}
?>
