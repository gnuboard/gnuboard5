<?
$sub_menu = "400730";
include_once("./_common.php");

check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

@mkdir("$g4[path]/data/banner", 0707);
@chmod("$g4[path]/data/banner", 0707);

$bn_bimg      = $_FILES["bn_bimg"]["tmp_name"];
$bn_bimg_name = $_FILES["bn_bimg"]["name"];

if ($bn_bimg_del)  @unlink("$g4[path]/data/banner/$bn_id");

if ($w=="")
{
    if (!$bn_bimg_name) alert("배너 이미지를 업로드 하세요.");

    sql_query(" alter table $g4[yc4_banner_table] auto_increment=1 ");

    $sql = " insert into $g4[yc4_banner_table]
                set bn_alt        = '$bn_alt',
                    bn_url        = '$bn_url',
                    bn_position   = '$bn_position',
                    bn_border     = '$bn_border',
                    bn_new_win    = '$bn_new_win',
                    bn_begin_time = '$bn_begin_time',
                    bn_end_time   = '$bn_end_time',
                    bn_time       = '$now',
                    bn_hit        = '0',
                    bn_order      = '$bn_order' ";
    sql_query($sql);          

    $bn_id = mysql_insert_id();
} 
else if ($w=="u")
{
    $sql = " update $g4[yc4_banner_table]
                set bn_alt        = '$bn_alt',
                    bn_url        = '$bn_url',
                    bn_position   = '$bn_position',
                    bn_border     = '$bn_border',
                    bn_new_win    = '$bn_new_win',
                    bn_begin_time = '$bn_begin_time',
                    bn_end_time   = '$bn_end_time', 
                    bn_order      = '$bn_order'
              where bn_id = '$bn_id' ";
    sql_query($sql);          
}
else if ($w=="d") 
{
    @unlink("$g4[path]/data/banner/$bn_id");

    $sql = " delete from $g4[yc4_banner_table] where bn_id = $bn_id ";
    $result = sql_query($sql);
}


if ($w == "" || $w == "u") 
{
    if ($_FILES[bn_bimg][name]) upload_file($_FILES[bn_bimg][tmp_name], $bn_id, "$g4[path]/data/banner");

    goto_url("./bannerform.php?w=u&bn_id=$bn_id");
} else {
    goto_url("./bannerlist.php");        
}
?>
