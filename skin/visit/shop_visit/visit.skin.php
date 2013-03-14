<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

global $is_admin;
?>

<table background='<?=$visit_skin_path?>/img/bg_visit.gif' width=100%>
<tr><td style='padding:4px' align=center><img src='<?=$visit_skin_path?>/img/bar_visit.gif'></td></tr>
<tr><td align=center style='padding-bottom:4px'>
    <table bgcolor=#FFFFFF width=165 cellpadding=3 cellspacing=0>
    <tr><td height=4></td></tr>
    <tr><td>&nbsp;· 오늘 : <?=number_format($visit[1])?> <? if ($is_admin == "super") { ?><a href="<?=$g4[admin_path]?>/visit_list.php"><img src="<?=$visit_skin_path?>/img/admin.gif" width="33" height="15" border="0" align="absmiddle"></a><?}?></td></tr>
    <tr><td>&nbsp;· 어제 : <?=number_format($visit[2])?></td></tr>
    <tr><td>&nbsp;· 최대 : <?=number_format($visit[3])?></td></tr>
    <tr><td>&nbsp;· 전체 : <?=number_format($visit[4])?></td></tr>
    </table>
    </td></tr>
</table>
