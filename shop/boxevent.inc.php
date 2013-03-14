<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table bgcolor=#FFFFFF width=100% cellpadding=0 cellspacing=0>
<tr><td align=center valign=top height=50><img src='<?=$g4[shop_img_path]?>/bar_event.gif'></td></tr>
<?
$hsql = " select ev_id, ev_subject from $g4[yc4_event_table] where ev_use = '1' order by ev_id desc ";
$hresult = sql_query($hsql);
for ($i=0; $row=sql_fetch_array($hresult); $i++) 
{
    if ($i > 0)
        echo "<tr><td align=center><img src='$g4[shop_img_path]/dot_line.gif'></td></tr>\n";

    $href = "$g4[shop_path]/event.php?ev_id=$row[ev_id]";

    // 이벤트 메뉴이미지가 있다면
    $event_img = "$g4[path]/data/event/$row[ev_id]_m";
    if (file_exists($event_img)) {
        echo "<tr><td><a href='$href'><img src='$event_img' border=0 align=absmiddle></a></td></tr>";
    } else {
        echo "<tr><td height=22>&nbsp;&nbsp;· <a href='$href'>$row[ev_subject]</a></td></tr>\n";;
    }

}

if ($i==0)
    echo "<tr><td height=50 align=center>등록된 자료가 없습니다.</td></tr>\n";
?>
</table>
