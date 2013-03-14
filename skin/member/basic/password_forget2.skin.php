<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="600" height="50" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td height="50" align="center" valign="middle" bgcolor="#EBEBEB"><table width="590" height="40" border="0" cellspacing="0" cellpadding="0">
            <tr> 
                <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$member_skin_path?>/img/icon_01.gif" width="5" height="5"></td>
                <td width="175" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b>회원아이디/패스워드 찾기</b></font></td>
                <td width="390" align="right" bgcolor="#FFFFFF" ><img src="<?=$member_skin_path?>/img/step_02.gif" width="110" height="16"></td>
            </tr>
        </table></td>
</tr>
</table>

<form name=fpasswordforget2 method=post onsubmit="return fpasswordforget2_submit(this);" autocomplete=off>
<input type=hidden name=bo_table   value='<?=$bo_table?>'>
<input type=hidden name=pass_mb_id value='<?=$mb[mb_id]?>'>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td width="600" height="300" align="center" valign="middle" background="<?=$member_skin_path?>/img/dot_bg_img_01.gif">
        <table width="400" border="0" cellspacing="0" cellpadding="0">
            <tr> 
                <td width="5%" height="40" align="center"><img src="<?=$member_skin_path?>/img/icon_02.gif" width="6" height="6"></td>
                <td width="20%"><b>회원아이디</b></td>
                <td width="75%"><b><?=$mb[mb_id]?></b></td>
            </tr>
            <tr> 
                <td height="40" align="center"><img src="<?=$member_skin_path?>/img/icon_02.gif" width="6" height="6"></td>
                <td colspan="2"><b>패스워드 분실시 질문</b></td>
            </tr>
            <tr> 
                <td height="30" align="center"></td>
                <td colspan="2" valign="top"><?=$mb[mb_password_q]?></td>
            </tr>
            <tr> 
                <td height="40" align="center"><img src="<?=$member_skin_path?>/img/icon_02.gif" width="6" height="6"></td>
                <td colspan="2"><b>패스워드 분실시 답변</b></td>
            </tr>
            <tr> 
                <td height="30"></td>
                <td colspan="2" valign="top">
                    <input type=text name='mb_password_a' class=ed size=55 required itemname='패스워드 분실시 답변' value=''>
                </td>
            </tr>
            <tr> 
                <td height="40" align="center"><img src="<?=$member_skin_path?>/img/icon_02.gif" width="6" height="6"></td>
                <td colspan="2">
                    <img id='kcaptcha_image' />
                    <input type=text name='wr_key' class=ed size=10 required itemname='자동등록방지'>&nbsp;&nbsp;왼쪽의 글자를 입력하세요.
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
    <td height="40" align="center" valign="bottom"><input type="image" src="<?=$member_skin_path?>/img/btn_next_01.gif">&nbsp;&nbsp;<a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" width="48" height="20" border="0"></a></td>
</tr>
</table>

</form>

<script type="text/javascript" src="<?="$g4[path]/js/md5.js"?>"></script>
<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
function fpasswordforget2_submit(f)
{
    if (hex_md5(f.wr_key.value) != md5_norobot_key) {
        alert("자동등록방지용 글자가 제대로 입력되지 않았습니다.");
        f.wr_key.select();
        return false;
    }

    f.action = "./password_forget3.php";
    return true;
}

document.fpasswordforget2.mb_password_a.focus();
</script>
