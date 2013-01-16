<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

global $is_admin;
?>

<table width="220" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td colspan="3"><img src="<?=$visit_skin_path?>/img/visit_top.gif" width="220" height="11"></td>
</tr>
<tr> 
    <td width="15" height="88" rowspan="4" bgcolor="#F4F4F4"></td>
    <td width="73" height="22"><img src="<?=$visit_skin_path?>/img/visit_1.gif" width="73" height="22"></td>
    <td width="132" height="22" bgcolor="#F4F4F4"><font color="#4B4B4B"><?=number_format($visit[1])?></font>
        <? if ($is_admin == "super") { ?><a href="<?=$g4['admin_path']?>/visit_list.php"><img src="<?=$visit_skin_path?>/img/admin.gif" width="33" height="15" border="0" align="absmiddle"></a><?}?></td>
</tr>
<tr> 
    <td width="73" height="22"><img src="<?=$visit_skin_path?>/img/visit_2.gif" width="73" height="22"></td>
    <td width="132" height="22" bgcolor="#F4F4F4"><font color="#4B4B4B"><?=number_format($visit[2])?></font></td>
</tr>
<tr> 
    <td width="73" height="22"><img src="<?=$visit_skin_path?>/img/visit_3.gif" width="73" height="22"></td>
    <td width="132" height="22" bgcolor="#F4F4F4"><font color="#4B4B4B"><?=number_format($visit[3])?></font></td>
</tr>
<tr> 
    <td width="73" height="22"><img src="<?=$visit_skin_path?>/img/visit_4.gif" width="73" height="22"></td>
    <td width="132" height="22" bgcolor="#F4F4F4"><font color="#4B4B4B"><?=number_format($visit[4])?></font></td>
</tr>
<tr> 
    <td colspan="3"><img src="<?=$visit_skin_path?>/img/visit_down.gif" width="220" height="11"></td>
</tr>
</table>
