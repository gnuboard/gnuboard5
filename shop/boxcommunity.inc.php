<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table bgcolor=#FFFFFF width=100% cellpadding=0 cellspacing=0>
<tr><td align=center valign=top height=50><img src='<?=$g4[shop_img_path]?>/bar_community.gif'></td></tr>
<?
$hsql = " select bo_table, bo_subject from $g4[board_table] order by gr_id, bo_table ";
$hresult = sql_query($hsql);
for ($i=0; $row=sql_fetch_array($hresult); $i++)
{
    if ($i > 0)
        echo "<tr><td align=center><img src='$g4[shop_img_path]/dot_line.gif'></td></tr>\n";

    echo "<tr><td height=22>&nbsp;&nbsp;· <a href='$g4[path]/bbs/board.php?bo_table=$row[bo_table]'>$row[bo_subject]</a></td></tr>\n";
}

if ($i==0)
    echo "<tr><td height=50 align=center>등록된 게시판이 없습니다.</td></tr>\n";
?>
</table>
