<?php
$sub_menu = '400650';
include_once('./_common.php');

check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$iv = sql_fetch(" select * from {$g4['shop_item_ps_table']} where is_id = '$is_id' ");
if (!$iv['is_id'])
    alert('등록된 자료가 없습니다.');

$qstr = "page=$page&amp;sort1=$sort1&amp;sort2=$sort2";

if ($w == "u")
{
    if($_POST['btn_no_display'])
        $is_confirm = 0;
    else
        $is_confirm = 1;

    $sql = "update {$g4['shop_item_ps_table']}
               set is_subject = '$is_subject',
                   is_content = '$is_content',
                   is_confirm = '$is_confirm'
             where is_id = '$is_id' ";
    sql_query($sql);

    goto_url("./itempsform.php?w=$w&amp;is_id=$is_id&amp;$qstr");
}
else if ($w == "d")
{
    $sql = "delete from {$g4['shop_item_ps_table']} where is_id = '$is_id' ";
    sql_query($sql);

    goto_url("./itempslist.php?$qstr");
}
else
{
    alert();
}
?>
