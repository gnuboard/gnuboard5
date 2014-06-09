<?php
$sub_menu = '300700';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

@mkdir(G5_DATA_PATH."/faq", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/faq", G5_DIR_PERMISSION);

if ($fm_himg_del)  @unlink(G5_DATA_PATH."/faq/{$fm_id}_h");
if ($fm_timg_del)  @unlink(G5_DATA_PATH."/faq/{$fm_id}_t");

$sql_common = " set fm_subject = '$fm_subject',
                    fm_head_html = '$fm_head_html',
                    fm_tail_html = '$fm_tail_html',
                    fm_mobile_head_html = '$fm_mobile_head_html',
                    fm_mobile_tail_html = '$fm_mobile_tail_html',
                    fm_order = '$fm_order' ";

if ($w == "")
{
    $sql = " alter table {$g5['faq_master_table']} auto_increment=1 ";
    sql_query($sql);

    $sql = " insert {$g5['faq_master_table']} $sql_common ";
    sql_query($sql);

    $fm_id = mysql_insert_id();
}
else if ($w == "u")
{
    $sql = " update {$g5['faq_master_table']} $sql_common where fm_id = '$fm_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    @unlink(G5_DATA_PATH."/faq/{$fm_id}_h");
    @unlink(G5_DATA_PATH."/faq/{$fm_id}_t");

    // FAQ삭제
	$sql = " delete from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
    sql_query($sql);

    // FAQ상세삭제
	$sql = " delete from {$g5['faq_table']} where fm_id = '$fm_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u")
{
    if ($_FILES['fm_himg']['name']){
        $dest_path = G5_DATA_PATH."/faq/".$fm_id."_h";
        @move_uploaded_file($_FILES['fm_himg']['tmp_name'], $dest_path);
        @chmod($dest_path, G5_FILE_PERMISSION);
    }
    if ($_FILES['fm_timg']['name']){
        $dest_path = G5_DATA_PATH."/faq/".$fm_id."_t";
        @move_uploaded_file($_FILES['fm_timg']['tmp_name'], $dest_path);
        @chmod($dest_path, G5_FILE_PERMISSION);
    }

    goto_url("./faqmasterform.php?w=u&amp;fm_id=$fm_id");
}
else
    goto_url("./faqmasterlist.php");
?>
