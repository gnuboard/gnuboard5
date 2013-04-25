<?php
$sub_menu = '400630';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

@mkdir(G4_DATA_PATH."/event", 0707);
@chmod(G4_DATA_PATH."/event", 0707);

if ($ev_mimg_del)  @unlink(G4_DATA_PATH."/event/{$ev_id}_m");
if ($ev_himg_del)  @unlink(G4_DATA_PATH."/event/{$ev_id}_h");
if ($ev_timg_del)  @unlink(G4_DATA_PATH."/event/{$ev_id}_t");

$sql_common = " set ev_skin             = '$ev_skin',
                    ev_img_width        = '$ev_img_width',
                    ev_img_height       = '$ev_img_height',
                    ev_list_mod         = '$ev_list_mod',
                    ev_list_row         = '$ev_list_row',
                    ev_subject          = '$ev_subject',
                    ev_head_html        = '$ev_head_html',
                    ev_tail_html        = '$ev_tail_html',
                    ev_use              = '$ev_use',
                    ev_subject_strong   = '$ev_subject_strong'
                    ";

if ($w == "")
{
    $ev_id = G4_SERVER_TIME;

    $sql = " insert {$g4['shop_event_table']}
                    $sql_common
                  , ev_id = '$ev_id' ";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql = " update {$g4['shop_event_table']}
                $sql_common
              where ev_id = '$ev_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    @unlink(G4_DATA_PATH."/event/{$ev_id}_m");
    @unlink(G4_DATA_PATH."/event/{$ev_id}_h");
    @unlink(G4_DATA_PATH."/event/{$ev_id}_t");

    $sql = " delete from {$g4['shop_event_table']} where ev_id = '$ev_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u")
{
    if ($_FILES['ev_mimg']['name']) upload_file($_FILES['ev_mimg']['tmp_name'], $ev_id."_m", G4_DATA_PATH."/event");
    if ($_FILES['ev_himg']['name']) upload_file($_FILES['ev_himg']['tmp_name'], $ev_id."_h", G4_DATA_PATH."/event");
    if ($_FILES['ev_timg']['name']) upload_file($_FILES['ev_timg']['tmp_name'], $ev_id."_t", G4_DATA_PATH."/event");

    goto_url("./itemeventform.php?w=u&amp;ev_id=$ev_id");
}
else
{
    goto_url("./itemevent.php");
}
?>
