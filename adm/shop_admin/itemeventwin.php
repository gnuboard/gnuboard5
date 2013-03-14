<?
$sub_menu = "400630";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$sql = " select ev_subject from $g4[yc4_event_table] where ev_id = '$ev_id' ";
$ev = sql_fetch($sql);

$g4[title] = "[$ev[ev_subject]] 이벤트상품";
include_once("$g4[path]/head.sub.php");
?>

<link rel="stylesheet" href="./admin.style.css" type="text/css">

<table width=100% cellpadding=8><tr><td>

<?=subtitle($g4[title]);?>
<table cellpadding=4 cellspacing=1 width=100%>
<tr><td colspan=20 height=3 bgcolor=0E87F9></td></tr>
<tr align=center>
    <td colspan=2>상품명</td>
    <td width=60>사용구분</td>
    <td width=60>삭제</td>
</tr>
<tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
<tr><td colspan=20 height=3 bgcolor=#F8F8F8></td></tr>

<?
$sql = " select b.it_id, b.it_name, b.it_use from $g4[yc4_event_item_table] a
           left join $g4[yc4_item_table] b on (a.it_id=b.it_id)
          where a.ev_id = '$ev_id' 
          order by b.it_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    echo "
    <tr align=center>
        <td width=50><a href='$href' target=_blank>".get_it_image("$row[it_id]_s", 40, 40)."</a></td>
        <td align=left><a href='$href' target=_blank>".cut_str(stripslashes($row[it_name]), 60, "&#133")."</a></td> 
        <td>".($row[it_use]?"사용":"미사용")."</td>
        <td>".icon("삭제", "javascript:del('./itemeventwindel.php?ev_id=$ev_id&it_id=$row[it_id]');")."</td>
    <tr>";
}

if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#ffffff class=point>자료가 한건도 없습니다.</td></tr>";
?>

<tr><td colspan=20 height=1 bgcolor=CCCCCC></td></tr>
</table>

</td></tr></table>

<?
include_once("$g4[path]/tail.sub.php");
?>
