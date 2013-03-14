<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? for ($i=0; $i<count($list); $i++) { ?>
<tr><td height=22><nobr style='display:block; overflow:hidden; width:160;'>
    &nbsp;&nbsp;&nbsp;
    <img src='<?=$latest_skin_path?>/img/blot.gif' align=absmiddle width=2 height=4> 
    <a href='<?=$list[$i][href]?>'><?=$list[$i][subject]?></a>
    <span style='font-family:돋움; font-size:8pt; color:#9A9A9A;'><?=$list[$i][comment_cnt]?></span></a></td>
    </nobr>
</tr>
<? if ($i < $rows-1) { echo "<tr><td align=center><img src='$latest_skin_path/img/dot_line.gif'></td></tr>"; } ?>
<? } ?>

<? if (count($list) == 0) { ?>
<tr><td align=center height=30 background="<?=$latest_skin_path?>/img/board_bg_line.gif">게시물이 없습니다.</td></tr>
<? } ?>
</table>
