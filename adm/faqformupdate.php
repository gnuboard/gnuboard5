<?php
$sub_menu = '300700';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$sql_common = " fa_subject = '$fa_subject',
                fa_content = '$fa_content',
                fa_order = '$fa_order' ";

if ($w == "")
{
    $sql = " insert {$g5['faq_table']}
                set fm_id ='$fm_id',
                    $sql_common ";
    sql_query($sql);

    $fa_id = mysql_insert_id();
}
else if ($w == "u")
{
    $sql = " update {$g5['faq_table']}
                set $sql_common
              where fa_id = '$fa_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
	$sql = " delete from {$g5['faq_table']} where fa_id = '$fa_id' ";
    sql_query($sql);
}

if ($w == 'd')
    goto_url("./faqlist.php?fm_id=$fm_id");
else
    goto_url("./faqform.php?w=u&amp;fm_id=$fm_id&amp;fa_id=$fa_id");
?>
