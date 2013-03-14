<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($g4['https_url']) {
    $outlogin_url = $_GET['url'];
    if ($outlogin_url) {
        if (preg_match("/^\.\.\//", $outlogin_url)) {
            $outlogin_url = urlencode($g4[url]."/".preg_replace("/^\.\.\//", "", $outlogin_url));
        }
        else {
            $purl = parse_url($g4[url]);
            if ($purl[path]) {
                $path = urlencode($purl[path]);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $outlogin_url = $g4[url].$urlencode;
        }
    }
    else {
        $outlogin_url = $g4[url];
    }
}
else {
    $outlogin_url = $urlencode;
}
?>

<script type="text/javascript" src="<?=$g4[path]?>/js/capslock.js"></script>
<script type="text/javascript" language=JavaScript>
// 엠파스 로긴 참고
var bReset = true;
function chkReset(f)
{
    if (bReset) { if ( f.mb_id.value == '아이디' ) f.mb_id.value = ''; bReset = false; }
    document.getElementById("pw1").style.display = "none";
    document.getElementById("pw2").style.display = "";
}
</script>

<table bgcolor=#CCCCCC width=187 cellpadding=1 cellspacing=0>
<form name="fhead" method="post" onsubmit="return fhead_submit(this);" autocomplete="off">
<input type="hidden" name="url" value="<?=$outlogin_url?>">
<tr>
    <td>
        <table width=100% bgcolor=#FFFFFF cellpadding=0 cellspacing=0 border=0 height=97>
        <tr><td colspan=2 height=5></td></tr>
        <tr>
            <td>
                <table width=100% bgcolor=#FFFFFF cellpadding=0 cellspacing=0 border=0>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;<img src='<?=$outlogin_skin_path?>/img/icon_id.gif'>&nbsp;</td>
                    <td><input class=ed name="mb_id" type="text" size="14" maxlength="20"  value='아이디' onMouseOver='chkReset(this.form);' onFocus='chkReset(this.form);'></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;<img src='<?=$outlogin_skin_path?>/img/icon_pw.gif'>&nbsp;</td>
                    <td id=pw1><input class=ed type="text" size="14" maxlength="20"  value='패스워드' onMouseOver='chkReset(this.form);' onfocus='chkReset(this.form);'>
                    <td id=pw2 style='display:none;'><input class=ed name="mb_password" id="outlogin_mb_password" type="password" size="14" maxlength="20"  onMouseOver='chkReset(this.form);' onfocus='chkReset(this.form);' onKeyPress="check_capslock(event, 'outlogin_mb_password');"></td>
                </tr>
                </table></td>
            <td><input type=image src='<?=$outlogin_skin_path?>/img/btn_login.gif' border=0></td>
        </tr>
        <tr>
            <td colspan=2 align=center>
                <input type="checkbox" name="auto_login" value="1" onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false; } }"><span style='font-size:11px; font-family:돋움;'>자동로그인</span>
                <? if ($g4[https_url]) { ?>
                <input type="checkbox" name="ssl_login" value="1" checked><span style='font-size:11px; font-family:돋움;'>보안로그인</span>
                <? } ?>
            </td>
        </tr>
        <tr>
            <td colspan=2 align=center>
                <a href="javascript:win_password_lost();"><img src='<?=$outlogin_skin_path?>/img/btn_find.gif' border=0></a>
                <a href="<?=$g4[bbs_path]?>/register.php"><img src='<?=$outlogin_skin_path?>/img/btn_join.gif' border=0></a>
            </td>
        </tr>
        </table>
    </td>
</tr>
</form>
</table>

<script language="JavaScript">
function fhead_submit(f)
{
    if (!f.mb_id.value)
    {
        alert("회원아이디를 입력하십시오.");
        f.mb_id.focus();
        return false;
    }

    if (document.getElementById('pw2').style.display!='none' && !f.mb_password.value)
    {
        alert("패스워드를 입력하십시오.");
        f.mb_password.focus();
        return false;
    }

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/login_check.php';";
    else
        echo "f.action = '$g4[bbs_path]/login_check.php';";
    ?>

    f.submit();
}
</script>
