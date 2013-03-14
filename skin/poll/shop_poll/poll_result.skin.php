<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=600 cellpadding=0 cellspacing=0>
<tr><td height=59 background='<?=$poll_skin_path?>/img/poll_bg.gif' style='padding-left:20px'>
    <img src='<?=$poll_skin_path?>/img/poll_q.gif' align=absmiddle> <b><?=$po_subject?></b> (전체 <?=$nf_total_po_cnt?>표)</td></tr>
<tr>
    <td align=center><br>
        <table width=95% cellpadding=5 cellspacing=0>

        <? 
        for ($i=1; $i<=count($list); $i++) 
        { 
            if ($i>1)
                echo "<tr><td height=1 colspan=3 background='$poll_skin_path/img/dot_line.gif'></td></tr>";
        ?>
        <tr>
            <td><?=$list[$i][num]?>. <?=$list[$i][content]?></td>
            <td><img src='<?=$poll_skin_path?>/img/poll_graph_y.gif' width="<?=(int)$list[$i][bar]?>" height=7></td>
            <td style='color:#5f4fbe'><?=$list[$i][cnt]?>표 (<?=number_format($list[$i][rate], 1)?>%)</td></tr>
        <? } ?>

        </table>
    </td>
</tr>

<? if ($is_etc) { ?>

    <? if ($member[mb_level] >= $po[po_level]) { ?>
        <tr>
            <td align=center><br>
                <table width=95% cellpadding=0 cellspacing=0>
                <form name="fpollresult" method="post" action="javascript:fpollresult_submit(document.fpollresult);" autocomplete="off">
                <input type=hidden name=po_id value="<?=$po_id?>">
                <input type=hidden name=w value="">
                <input type=hidden name=skin_dir value="<?=$skin_dir?>">
                <tr><td width=11><img src='<?=$poll_skin_path?>/img/ca_box01.gif'></td><td background='<?=$poll_skin_path?>/img/ca_bg01.gif'></td><td width=11><img src='<?=$poll_skin_path?>/img/ca_box02.gif'></td></tr>
                <tr height=25><td background='<?=$poll_skin_path?>/img/ca_bg02.gif'></td><td>다른 의견이 있다면 말씀해 주세요.</td><td background='<?=$poll_skin_path?>/img/ca_bg03.gif'></td></tr>
                <tr height=25>
                    <td background='<?=$poll_skin_path?>/img/ca_bg02.gif'></td>
                    <td>
                        
                        <? if ($member[mb_id]) { ?>
                            <input type=hidden name=pc_name value="<?=cut_str($member[mb_nick],255)?>">
                            <b><?=$member[mb_name]?></b> &nbsp;
                        <? } else { ?>
                            이름 <input type='text' name='pc_name' size=10 class=ed required itemname='이름'> &nbsp;
                        <? } ?>

                        의견 <input type='text' name='pc_idea' size=55 class=ed required itemname='의견' maxlength="100">
                            <input type=image src='<?=$poll_skin_path?>/img/poll_ok.gif' align=absmiddle border=0></td><td background='<?=$poll_skin_path?>/img/ca_bg03.gif'></td></tr>
                <tr><td><img src='<?=$poll_skin_path?>/img/ca_box03.gif'></td><td background='<?=$poll_skin_path?>/img/ca_bg04.gif'></td><td><img src='<?=$poll_skin_path?>/img/ca_box04.gif'></td></tr>
                </form>
                </table><br>

                <script language="JavaScript">
                function fpollresult_submit(f)
                {
                    f.action = "./poll_etc_update.php";
                    f.submit();
                }
                </script>
            </td>
        </tr>
    <? } ?>

    <? for ($i=0; $i<count($list2); $i++) { ?>
        <tr>
            <td align=center>
                <table width=95% cellpadding=4 cellspacing=0>
                <tr bgcolor="#F7F7F7"><td><b><?=$list2[$i][name]?></b></td><td align=right><?=$list2[$i][datetime]?> <? if ($list2[$i][del]) { echo $list2[$i][del] . "<img src='$g4[bbs_img_path]/btn_comment_delete.gif' width=45 height=14 border=0></a>"; } ?></td></tr>
                <tr><td colspan=2><font color=#5f4fbe><?=$list2[$i][idea]?></font></td></tr>
                <tr><td colspan=2 background='dot_line.gif'></td></tr>
                </table><br>
            </td>
        </tr>

    <? } ?>

<? } ?>

<tr><td height=35 style='padding-left:20px'><img src='<?=$poll_skin_path?>/img/poll_blot.gif' align=absmiddle> <b>다른 투표 결과 보기</b></td></tr>
<form name=fpolletc>
<input type=hidden name=skin_dir value="<?=$skin_dir?>">
<tr><td style='padding-left:35px'><select name=po_id onchange="select_po_id(this)"><? for ($i=0; $i<count($list3); $i++) { ?><option value='<?=$list3[$i][po_id]?>'>[<?=$list3[$i][date]?>] <?=$list3[$i][subject]?><? } ?></select><script>document.fpolletc.po_id.value='<?=$po_id?>';</script></td></tr>  
</form>
<tr><td align=center height=50><a href="javascript:window.close();"><img src='<?=$poll_skin_path?>/img/btn_close.gif' border=0></a></td></tr>
</table>



<script language='JavaScript'>
function select_po_id(fld) 
{
    document.location.href = "./poll_result.php?po_id="+fld.options[fld.selectedIndex].value+"&skin_dir="+document.fpolletc.skin_dir.value;
}
</script>



<?/*?>
<table width="600" border="0" cellspacing="0" cellpadding="0" align=center>
<tr>
    <td align="center">
        <table width="600" height="70" border="0" cellpadding="0" cellspacing="0">
        <tr> 
            <td align="center" valign="middle" bgcolor="#EBEBEB">
                <table width="590" height="60" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                    <td width="30" align="center" bgcolor="#FFFFFF" ><img src="'<?=$poll_skin_path?>/img/icon_01.gif" width="5" height="5"></td>
                    <td width="40" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b>질 문</b></font></td>
                    <td width="20" align="left" bgcolor="#FFFFFF" >l</td>
                    <td width="500" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$po_subject?></b> (전체 <?=$nf_total_po_cnt?>표)</font></td>
                </tr>
                </table></td>
        </tr>
        </table>

        <table width="600" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="10"></td>
        </tr>
        </table>

        <table width="600" bgcolor=#ffffff cellpadding=1 cellspacing=0>
        <tr> 
            <td> 
                <table width=100% cellpadding=6 cellspacing=1>
                <? for ($i=1; $i<=count($list); $i++) { ?>
                <tr bgcolor=#FFFFFF>
                    <td width="258" bgcolor="#EBF1F5"><?=$list[$i][num]?>. <?=$list[$i][content]?></td>
                    <td width="175" bgcolor="#EBF1F5"><img src="'<?=$poll_skin_path?>/img/poll_graph_y.gif" width="<?=(int)$list[$i][bar]?>" height="7"></td>
                    <td width="121" bgcolor="#A1C9E4"><font color="#ffffff"><?=$list[$i][cnt]?>표 (<?=number_format($list[$i][rate], 1)?>%)</font></td>
                </tr>
                <? } ?>
                </table></td>
        </tr>
        </table>

        <table width="570" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td height="15"></td>
            </tr>
        </table>

<? if ($is_etc) { ?>

    <? if ($member[mb_level] >= $po[po_level]) { ?>
        <table width=570 bgcolor=#D4D4D4 cellpadding=1 cellspacing=0>
        <form name="fpollresult" method="post" action="javascript:fpollresult_submit(document.fpollresult);" autocomplete="off">
        <input type=hidden name=po_id value="<?=$po_id?>">
        <input type=hidden name=w value="">
        <tr> 
            <td> 
                <table width=100% cellpadding=0 cellspacing=0 bgcolor=#FFFFFF>
                <tr> 
                    <td height=50 colspan=2>
                        <table width=100% cellpadding=4 bgcolor=#FFFFFF>    
                        <tr><td><?=$po_etc?></td></tr>
                        </table></td>
                </tr>
                <tr> 
                    <td height=35 width=150 align="center">
                        <? if ($member[mb_id]) { ?>
                            <input type=hidden name=pc_name value="<?=cut_str($member[mb_nick],255)?>">
                            <b><?=$member[mb_name]?></b> &nbsp;
                        <? } else { ?>
                            이름 <input type='text' name='pc_name' size=10 class=input required itemname='이름'> &nbsp;
                        <? } ?>
                    </td>
                    <td>
                        의견 <input type='text' name='pc_idea' size=55 class=input required itemname='의견' maxlength="100"> &nbsp;
                        <input name="image" type=image src='<?=$poll_skin_path?>/img/ok_btn.gif' align=absmiddle border=0></td>
                </tr>
                </table>
            </td>
        </tr>
        </form>
        </table>

        <script language="JavaScript">
        function fpollresult_submit(f)
        {
            f.action = "./poll_etc_update.php";
            f.submit();
        }
        </script>
    <? } ?>

    <? for ($i=0; $i<count($list2); $i++) { ?>
        <table width="570" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="10" colspan="4"></td>
        </tr>
        <tr> 
            <td width="20" height="25" align="center" bgcolor="#FAFAFA"><img src="'<?=$poll_skin_path?>/img/icon_03.gif" width="3" height="5"></td>
            <td width="350" bgcolor="#FAFAFA"><?=$list2[$i][name]?></td>
            <td width="70" align="center" bgcolor="#FAFAFA"><? if ($list2[$i][del]) { echo $list2[$i][del] . "<img src='$g4[bbs_img_path]/btn_comment_delete.gif' width=45 height=14 border=0></a>"; } ?></td>
            <td width="150" align="center" bgcolor="#FAFAFA"><?=$list2[$i][datetime]?></td>
        </tr>
        <tr> 
            <td height="1" colspan="4" background="'<?=$poll_skin_path?>/img/dot_bg.gif"></td>
        </tr>
        <tr> 
            <td width="20" height="25" bgcolor="#FAFAFA">&nbsp;</td>
            <td width="550" height="25" colspan="3" bgcolor="#FAFAFA"><?=$list2[$i][idea]?></td>
        </tr>
        </table>
    <? } ?>

<? } ?>

        <table width="600" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="30" colspan="3" align="center" valign="middle"><table width="595" border="0" cellspacing="0" cellpadding="0">
                    <tr> 
                        <td width="595" height="1" background="'<?=$poll_skin_path?>/img/dot_bg.gif"></td>
                    </tr>
                </table></td>
        </tr>
        <tr valign="top"> 
            <td height="30" colspan="3" align="left"><img src="'<?=$poll_skin_path?>/img/title_01.gif" width="180" height="19"></td>
        </tr>
        <form name=fpolletc>
        <tr> 
            <td width="15" align="center">&nbsp;</td>
            <td width="35" align="center"><img src="'<?=$poll_skin_path?>/img/icon_1.gif" width="15" height="8"></td>
            <td width="560"><select name=po_id onchange="select_po_id(this)"><? for ($i=0; $i<count($list3); $i++) { ?><option value='<?=$list3[$i][po_id]?>'>[<?=$list3[$i][date]?>] <?=$list3[$i][subject]?><? } ?></select><script>document.fpolletc.po_id.value='<?=$po_id?>';</script></td>
        </tr>
        </form>
        <tr> 
            <td height="10" colspan="3">&nbsp;</td>
        </tr>
        <tr> 
            <td height="5" colspan="3" background="'<?=$poll_skin_path?>/img/down_line.gif"></td>
        </tr>
        <tr align="center" valign="bottom"> 
            <td height="38" colspan="3"><a href="javascript:window.close();"><img src="'<?=$poll_skin_path?>/img/close.gif" width="66" height="20" border="0"></a></td>
        </tr>
        </table></td>
</tr>
</table>
<br>
<?*/?>
