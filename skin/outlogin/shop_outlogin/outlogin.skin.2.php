<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=187 cellpadding=1 cellspacing=0 bgcolor=#cccccc>
<tr>
    <td>
        <table width=100% bgcolor=#FFFFFF cellpadding=0 cellspacing=0 height=97>
        <tr><td height=6></td></tr>
        <tr><td align=center height=20><FONT COLOR="#0083B9"><?=$nick?></FONT>님 
            <? if ($is_admin == "super" || $is_auth) { ?><a href="<?=$g4[admin_path]?>/"><img src="<?=$outlogin_skin_path?>/img/admin.gif" width="33" height="15" border="0" align="absmiddle"></a><? } ?></td></tr>
        <tr><td align=center height=20><a href="javascript:win_point();"><font color="#737373">포인트 : <b><?=$point?></b> 점</font></a></td></tr>
        <tr><td align=center height=25>
            <a href="<?=$g4[bbs_path]?>/logout.php"><img src='<?=$outlogin_skin_path?>/img/btn_logout.gif' border=0></a>
            <a href="<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php"><img src='<?=$outlogin_skin_path?>/img/btn_modify.gif' border=0></a>
            </td></tr>
        <tr><td align=center>&nbsp;
                <a href="javascript:win_memo();"><FONT COLOR="#BB3D3E">쪽지 (<?=$memo_not_read?>)</FONT></a>&nbsp;&nbsp;
                <a href="javascript:win_scrap();"><img src='<?=$outlogin_skin_path?>/img/btn_scrap.gif' border=0 align=absmiddle></a>
            </td></tr>
        <tr><td height=7 colspan=3></td></tr>
        </table>
    </td>
</tr>
</table>
