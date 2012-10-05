<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<form name="fzip" method="get" autocomplete="off">
<input type=hidden name=frm_name  value='<?=$frm_name?>'>
<input type=hidden name=frm_zip1  value='<?=$frm_zip1?>'>
<input type=hidden name=frm_zip2  value='<?=$frm_zip2?>'>
<input type=hidden name=frm_addr1 value='<?=$frm_addr1?>'>
<input type=hidden name=frm_addr2 value='<?=$frm_addr2?>'>
<tr> 
    <td colspan="2">
        <table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
        <tr> 
            <td align="center" valign="middle" bgcolor="#EBEBEB">
                <table width="98%" height="40" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                    <td width="5%" align="center" bgcolor="#FFFFFF" ><img src="<?=$g4[bbs_img_path]?>/icon_01.gif" width="5" height="5"></td>
                    <td width="35%" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$g4[title]?></b></font></td>
                    <td width="60%" bgcolor="#FFFFFF" ></td>
                </tr>
                </table></td>
        </tr>
        </table></td>
</tr>
<tr> 
    <td height="70" colspan="2" valign="bottom"><img src="<?=$g4[bbs_img_path]?>/zip_img_01.gif" width="273" height="40"></td>
</tr>
<tr> 
    <td height="20" colspan="2"></td>
</tr>
<tr> 
    <td width=130><img src="<?=$g4[bbs_img_path]?>/zip_img_02.gif" width="125" height="14"></td>
    <td><input type=text name=addr1 value='<?=$addr1?>' required minlength=2 itemname='동(읍/면/리)' size=35> <input type=image src='<?=$g4[bbs_img_path]?>/btn_post_search.gif' border=0 align=absmiddle></td>
</tr>
<tr> 
    <td height="20" colspan="2"></td>
</tr>
</table>
<!-- 검색결과 여기서부터 -->

<script type='text/javascript'>
document.fzip.addr1.focus();
</script>


<? if ($search_count > 0) { ?>
<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td height="1" colspan="2" background="<?=$g4[bbs_img_path]?>/post_dot_bg.gif"></td>
</tr>
<tr> 
    <td height="50" colspan="2"><img src="<?=$g4[bbs_img_path]?>/zip_img_03.gif" width="99" height="13"></td>
</tr>
<tr> 
    <td width="10%"></td>
    <td width="90%">
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td height=23 valign=top>총 <?=$search_count?>건 가나다순</td>
        </tr>
        <?
        for ($i=0; $i<count($list); $i++) 
        {
            echo "<tr><td height=19><a href='javascript:;' onclick=\"find_zip('{$list[$i][zip1]}', '{$list[$i][zip2]}', '{$list[$i][addr]}');\">{$list[$i][zip1]}-{$list[$i][zip2]} : {$list[$i][addr]} {$list[$i][bunji]}</a></td></tr>\n";
        }
        ?>
        <tr>
            <td height=23>[끝]</td>
        </tr>
        </table>
</tr>
</table>

<script type="text/javascript">
function find_zip(zip1, zip2, addr1)
{
    var of = opener.document.<?=$frm_name?>;

    of.<?=$frm_zip1?>.value  = zip1;
    of.<?=$frm_zip2?>.value  = zip2;

    of.<?=$frm_addr1?>.value = addr1;

    of.<?=$frm_addr2?>.focus();
    window.close();
    return false;
}
</script>
<? } ?>
