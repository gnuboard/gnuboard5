<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="668" border="0" cellspacing="0" cellpadding="0">
<form name="fboardpassword" method=post action="javascript:fboardpassword_submit(document.fboardpassword);">
<input type=hidden name=w           value="<?=$w?>">
<input type=hidden name=bo_table    value="<?=$bo_table?>">
<input type=hidden name=wr_id       value="<?=$wr_id?>">
<input type=hidden name=comment_id  value="<?=$comment_id?>">
<input type=hidden name=sfl         value="<?=$sfl?>">
<input type=hidden name=stx         value="<?=$stx?>">
<input type=hidden name=page        value="<?=$page?>">
<!-- <tr align="center"> 
    <td colspan="3"><img src="<?=$member_skin_path?>/img/secrecy_title.gif" width="624" height="72"></td>
</tr> -->
<tr> 
    <td width="20" height="26"></td>
    <td width="628"></td>
    <td width="20"></td>
</tr>
<tr> 
    <td width="20" height="2"></td>
    <td width="628" bgcolor="#8F8F8F"></td>
    <td width="20"></td>
</tr>
<tr> 
    <td width="20" height="48"></td>
    <td width="628" align="right" background="<?=$member_skin_path?>/img/secrecy_table_bg_top.gif"><img src="<?=$member_skin_path?>/img/secrecy_img.gif" width="344" height="48"></td>
    <td width="20"></td>
</tr>
<tr> 
    <td width="20" height="223"></td>
    <td width="628" align="center" background="<?=$member_skin_path?>/img/secrecy_table_bg.gif">
        <table width="460" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td width="460" height="223" align="center" bgcolor="#FFFFFF">
                <table width="350" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                    <td width="30" align="center"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                    <td width="70" align="left"><b>패스워드</b></td>
                    <td width="150"><INPUT type=password maxLength=20 size=15 name="wr_password" itemname="패스워드" required></td>
                    <td width="100" height="100" valign="middle"><INPUT name="image" type=image src="<?=$member_skin_path?>/img/btn_confirm.gif" width="65" height="52" border=0></td>
                </tr>
                <tr align="center"> 
                    <td height="1" colspan="4" background="<?=$member_skin_path?>/img/dot_line.gif"></td>
                </tr>
                <tr align="center">
                    <td height="60" colspan="4">이 게시물의 패스워드를 입력하십시오.</td>
                </tr>
                </table></td>
        </tr>
        </table></td>
    <td width="20"></td>
</tr>
<tr> 
    <td width="20" height="1"></td>
    <td width="628" bgcolor="#F0F0F0"></td>
    <td width="20"></td>
</tr>
<tr> 
    <td height="20" colspan="3"></td>
</tr>
</form>
</table>

<script language='JavaScript'>
document.fboardpassword.wr_password.focus();

function fboardpassword_submit(f)
{
    f.action = "<?=$action?>";
    f.submit();
}
</script>
