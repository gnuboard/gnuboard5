<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="600" height="50" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" bgcolor="#EBEBEB">
        <table width="590" height="40" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$member_skin_path?>/img/icon_01.gif" width="5" height="5"></td>
            <td width="75" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$g4[title]?></b></font></td>
            <td width="490" bgcolor="#FFFFFF" ></td>
        </tr>
        </table></td>
</tr>
</table>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td width="600" height="20" colspan="14"></td>
</tr>
<tr> 
    <td width="30" height="24"></td>
    <td width="99" align="center" valign="middle"><a href="./memo.php?kind=recv"><img src="<?=$member_skin_path?>/img/btn_recv_paper_off.gif" width="99" height="24" border="0"></a></td>
    <td width="2"  align="center" valign="middle">&nbsp;</td>
    <td width="99" align="center" valign="middle"><a href="./memo.php?kind=send"><img src="<?=$member_skin_path?>/img/btn_send_paper_off.gif" width="99" height="24" border="0"></a></td>
    <td width="2"  align="center" valign="middle">&nbsp;</td>
    <td width="99" align="center" valign="middle"><a href="./memo_form.php"><img src="<?=$member_skin_path?>/img/btn_write_paper_on.gif" width="99" height="24" border="0"></a></td>
    <td width="2"  valign="middle">&nbsp;</td>
    <td width="60" bgcolor="#EFEFEF">&nbsp;</td>
    <td width="4"  bgcolor="#EFEFEF"">&nbsp;</td>
    <td width="18" bgcolor="#EFEFEF">&nbsp;</td>
    <td width="148" bgcolor="#EFEFEF">&nbsp;</td>
    <td width="4" bgcolor="#EFEFEF">&nbsp;</td>
    <td width="3" bgcolor="#EFEFEF"></td>
    <td width="30" height="24"></td>
</tr>
</table>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<form name=fmemoform method=post onsubmit="return fmemoform_submit(this);" autocomplete="off">
<tr> 
    <td height="300" align="center" valign="top">
        <table width="540" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="20"></td>
        </tr>
        <tr> 
            <td height="2" bgcolor="#808080"></td>
        </tr>
        <tr> 
            <td width="540" height="2" align="center" valign="top" bgcolor="#FFFFFF">
                <table width=100% cellpadding=1 cellspacing=1 border=0>
                <tr bgcolor=#E1E1E1 align=center> 
                    <td width="30%" height="24" rowspan="2"><b>받는 회원아이디</b></td>
                    <td width="70%" align="center"><input type=text name="me_recv_mb_id" required itemname="받는 회원아이디" value="<?=$me_recv_mb_id?>" style="width:95%;"></td>
                </tr>
                <tr bgcolor=#E1E1E1 align=center> 
                    <td>※ 여러 회원에게 보낼때는 컴마(,)로 구분하세요.</td>
                </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="180" align="center" valign="middle" bgcolor="#F6F6F6">
                <textarea name=me_memo rows=10 style='width:95%;' required itemname='내용'><?=$content?></textarea></td>
        </tr>
        <tr> 
            <td>
                <table width=100% cellpadding=1 cellspacing=1 border=0>
                <tr align=center> 
                    <td width="30%" height="24" rowspan="2"><img id='kcaptcha_image' /></td>
                    <td width="70%" align="left">
                        <input type=input size=10 name=wr_key itemname="자동등록방지" required>&nbsp;&nbsp;왼쪽의 글자를 입력하세요.
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        </table></td>
</tr>
<tr> 
    <td height="2" align="center" valign="top" bgcolor="#D5D5D5"></td>
</tr>
<tr>
    <td height="2" align="center" valign="top" bgcolor="#E6E6E6"></td>
</tr>
<tr>
    <td height="40" align="center" valign="bottom">
        <input id=btn_submit type=image src="<?=$member_skin_path?>/img/btn_paper_send.gif" border=0>&nbsp;&nbsp;
        <a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" width="48" height="20" border="0"></a></td>
</tr>
</form>
</table>

<script type="text/javascript" src="<?=$g4[path]?>/js/md5.js"></script>
<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
with (document.fmemoform) {
    if (me_recv_mb_id.value == "")
        me_recv_mb_id.focus();
    else
        me_memo.focus();
}

function fmemoform_submit(f)
{
    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    document.getElementById("btn_submit").disabled = true;

    f.action = "./memo_form_update.php";
    return true;
}
</script>
