<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($g4['https_url']) {
    $login_url = $_GET['url'];
    if ($login_url) {
        if (preg_match("/^\.\.\//", $url)) {
            $login_url = urlencode($g4[url]."/".preg_replace("/^\.\.\//", "", $login_url));
        }
        else {
            $purl = parse_url($g4[url]);
            if ($purl[path]) {
                $path = urlencode($purl[path]);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $login_url = $g4[url].$urlencode;
        }
    }
    else {
        $login_url = $g4[url];
    }
}
else {
    $login_url = $urlencode;
}
?>

<script type="text/javascript" src="<?=$g4[path]?>/js/capslock.js"></script>

<form name="flogin" method="post" onsubmit="return flogin_submit(this);" autocomplete="off">
<input type="hidden" name="url" value='<?=$login_url?>'>

<table width="668" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height="26"></td>
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
    <td width="628" align="right" background="<?=$member_skin_path?>/img/login_table_bg_top.gif"><img src="<?=$member_skin_path?>/img/login_img.gif" width="344" height="48"></td>
    <td width="20"></td>
</tr>
<tr>
    <td width="20" height="223"></td>
    <td width="628" align="center" background="<?=$member_skin_path?>/img/login_table_bg.gif">
        <table width="460" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td width="460" height="223" align="center" bgcolor="#FFFFFF">
                <table width="350" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="250">
                        <table width="250" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="10"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                            <td width="90" height="26"><b>아이디</b></td>
                            <td width="150"><INPUT type=text class=ed maxLength=20 size=15 name=mb_id itemname="아이디" required minlength="2"></td>
                        </tr>
                        <tr>
                            <td><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                            <td height="26"><b>패스워드</b></td>
                            <td><INPUT type=password class=ed maxLength=20 size=15 name=mb_password id="login_mb_password" itemname="패스워드" required onkeypress="check_capslock(event, 'login_mb_password');"></td>
                        </tr>
                        <tr>
                            <td><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                            <td height="26"><b>자동로그인</b></td>
                            <td><INPUT onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false;} }" type=checkbox name=auto_login>
                                <b>사용</b></td>
                        </tr>
                        </table>
                    </td>
                    <td width="100" valign="top"><INPUT type=image width="65" height="52" src="<?=$member_skin_path?>/img/btn_login.gif" border=0></td>
                </tr>
                <tr>
                    <td height="5" colspan="2"></td>
                </tr>
                <tr>
                    <td height="1" background="<?=$member_skin_path?>/img/dot_line.gif" colspan="2"></td>
                </tr>
                <tr>
                    <td height="5" colspan="2"></td>
                </tr>
                <tr>
                    <td height="26" colspan="2"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"> 아직 회원이 아니십니까?&nbsp;&nbsp;&nbsp;&nbsp;<a href="./register.php"><img width="72" height="20" src="<?=$member_skin_path?>/img/btn_register.gif" border=0 align="absmiddle"></a></td>
                </tr>
                <tr>
                    <!-- <td height="26" colspan="2"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"> 아이디/패스워드를 잊으셨습니까?&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="win_password_forget('./password_forget.php');"><img src="<?=$member_skin_path?>/img/btn_password_forget.gif" width="108" height="20" border=0 align="absmiddle"></td> -->
                    <td height="26" colspan="2"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"> 아이디/패스워드를 잊으셨습니까?&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="win_password_lost();"><img src="<?=$member_skin_path?>/img/btn_password_forget.gif" width="108" height="20" border=0 align="absmiddle"></td>
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
</table>

</form>

<script type='text/javascript'>
document.flogin.mb_id.focus();

function flogin_submit(f)
{
    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/login_check.php';";
    else
        echo "f.action = '$g4[bbs_path]/login_check.php';";
    ?>

    return true;
}
</script>
