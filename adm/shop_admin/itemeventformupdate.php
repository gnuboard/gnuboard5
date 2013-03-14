<?
$sub_menu = "400630";
include_once("./_common.php");

if ($w == "u" || $w == "d") 
    check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

@mkdir("$g4[path]/data/event", 0707);
@chmod("$g4[path]/data/event", 0707);

if ($ev_mimg_del)  @unlink("$g4[path]/data/event/{$ev_id}_m");
if ($ev_himg_del)  @unlink("$g4[path]/data/event/{$ev_id}_h");
if ($ev_timg_del)  @unlink("$g4[path]/data/event/{$ev_id}_t");

$sql_common = " set ev_skin       = '$ev_skin',
                    ev_img_width  = '$ev_img_width',
                    ev_img_height = '$ev_img_height',
                    ev_list_mod   = '$ev_list_mod',
                    ev_list_row   = '$ev_list_row',
                    ev_subject    = '$ev_subject',
                    ev_head_html  = '$ev_head_html',
                    ev_tail_html  = '$ev_tail_html',
                    ev_use        = '$ev_use'
                    ";

if ($w == "") 
{
    $ev_id = $g4[server_time];

    $sql = " insert $g4[yc4_event_table]
                    $sql_common 
                  , ev_id = '$ev_id' ";
    sql_query($sql);
} 
else if ($w == "u") 
{
    $sql = " update $g4[yc4_event_table]
                $sql_common
              where ev_id = '$ev_id' ";
    sql_query($sql);
} 
else if ($w == "d") 
{
    @unlink("$g4[path]/data/event/{$ev_id}_m");
    @unlink("$g4[path]/data/event/{$ev_id}_h");
    @unlink("$g4[path]/data/event/{$ev_id}_t");

    $sql = " delete from $g4[yc4_event_table] where ev_id = '$ev_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u") 
{
    if ($_FILES[ev_mimg][name]) upload_file($_FILES[ev_mimg][tmp_name], $ev_id . "_m", "$g4[path]/data/event");
    if ($_FILES[ev_himg][name]) upload_file($_FILES[ev_himg][tmp_name], $ev_id . "_h", "$g4[path]/data/event");
    if ($_FILES[ev_timg][name]) upload_file($_FILES[ev_timg][tmp_name], $ev_id . "_t", "$g4[path]/data/event");

    goto_url("./itemeventform.php?w=u&ev_id=$ev_id");
} 
else 
{
    goto_url("./itemevent.php");
}
?>
