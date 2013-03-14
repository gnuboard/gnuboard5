<?
$sub_menu = "400700";
include_once("./_common.php");

if ($w == "u" || $w == "d") 
    check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

@mkdir("$g4[path]/data/content", 0707);
@chmod("$g4[path]/data/content", 0707);

if ($co_himg_del)  @unlink("$g4[path]/data/content/{$co_id}_h");
if ($co_timg_del)  @unlink("$g4[path]/data/content/{$co_id}_t");

$sql_common = " co_include_head = '$co_include_head',
                co_include_tail = '$co_include_tail',
                co_html         = '$co_html',
                co_subject      = '$co_subject',
                co_content      = '$co_content' ";

if ($w == "") 
{
    //if(eregi("[^a-z0-9_]", $co_id)) alert("ID 는 영문자, 숫자, _ 만 가능합니다.");
    if(preg_match("/[^a-z0-9_]/i", $co_id)) alert("ID 는 영문자, 숫자, _ 만 가능합니다.");

    $sql = " select co_id from $g4[yc4_content_table] where co_id = '$co_id' ";
    $row = sql_fetch($sql);
    if ($row[co_id]) 
        alert("이미 같은 ID로 등록된 내용이 있습니다.");

    $sql = " insert $g4[yc4_content_table]
                set co_id = '$co_id',
                    $sql_common ";
    sql_query($sql);
} 
else if ($w == "u") 
{
    $sql = " update $g4[yc4_content_table]
                set $sql_common
              where co_id = '$co_id' ";
    sql_query($sql);
} 
else if ($w == "d") 
{
    @unlink("$g4[path]/data/content/{$co_id}_h");
    @unlink("$g4[path]/data/content/{$co_id}_t");

    $sql = " delete from $g4[yc4_content_table] where co_id = '$co_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u") 
{
    if ($_FILES[co_himg][name])  upload_file($_FILES[co_himg][tmp_name], $co_id . "_h", "$g4[path]/data/content");
    if ($_FILES[co_timg][name])  upload_file($_FILES[co_timg][tmp_name], $co_id . "_t", "$g4[path]/data/content");

    goto_url("./contentform.php?w=u&co_id=$co_id");
} 
else 
{
    goto_url("./contentlist.php");
}
?>
