<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="600" height="50" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td height="50" align="center" valign="middle" bgcolor="#EBEBEB"><table width="590" height="40" border="0" cellspacing="0" cellpadding="0">
            <tr> 
                <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$member_skin_path?>/img/icon_01.gif" width="5" height="5"></td>
                <td width="175" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$g4[title]?></b></font></td>
                <td width="390" align="right" bgcolor="#FFFFFF" ></td>
            </tr>
            </table></td>
    </tr>
</table>

<form name="fpasswordlost" method="post" onsubmit="return fpasswordlost_submit(this);" autocomplete="off">
<table width="540" border="0" cellspacing="0" cellpadding="0" align="center">
<tr> 
    <td height="30"></td>
</tr>
<tr> 
    <td height="170" align="center" valign="middle" background="<?=$member_skin_path?>/img/gray_bg_img.gif" bgcolor="#FFFFFF">
        <table width="400" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td width="130" height="14"><b>이메일주소</b></td>
            <td width="" height="14">
                <input type="text" name="mb_email" class="ed" required email itemname="이메일주소" size="45" />
                <br />회원가입시 등록하신 이메일주소 입력
            </td>
        </tr>
        <tr> 
            <td height="20" colspan="2"></td>
        </tr>
        <tr> 
            <td><img id='kcaptcha_image' /></td>
            <td>
                <input type=text name='wr_key' class="ed" size=10 required itemname='자동등록방지'>
                <br />왼쪽의 숫자를 입력하세요.
            </td>
        </tr>
        </table>
    </td>
</tr>
<tr> 
    <td height="10"></td>
</tr>
<tr> 
    <td height="40" align="center" valign="bottom"><input type="image" src="<?=$member_skin_path?>/img/btn_next_01.gif">&nbsp;&nbsp;<a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" width="48" height="20" border="0"></a></td>
</tr>
</table>
</form>



<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
function fpasswordlost_submit(f)
{
    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/password_lost2.php';";
    else
        echo "f.action = './password_lost2.php';";
    ?>

    return true;
}

self.focus();
document.fpasswordlost.mb_email.focus();

$(function() {
    var sw = screen.width;
    var sh = screen.height;
    var cw = document.body.clientWidth;
    var ch = document.body.clientHeight;
    var top  = sh / 2 - ch / 2 - 100;
    var left = sw / 2 - cw / 2;
    moveTo(left, top);
});
</script>
