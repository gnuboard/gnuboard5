<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$tmp_mb_id = $tmp_mb_password = "";
if (isset($is_demo))
{
    $f = @file("$g4[path]/DEMO");
    if (is_array($f))
    {
        $tmp_mb_id = $f[0];
        $tmp_mb_password = $f[1];
    }
}

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

<br>
<br>
<br>
<form name="flogin" method="post" onsubmit="flogin_submit(this);" autocomplete="off" style="padding:0px;">
<input type="hidden" name="url" value='<?=$login_url?>'>
<table cellpadding=2 bgcolor=#F6F6F6 align=center>
<tr><td>
    <table width=480 bgcolor=#FFFFFF cellpadding=0 border=0>
    <tr><td align=center height=60><img src='<?=$member_skin_path?>/img/title_login.gif'></td></tr>
    <tr>
        <td>
            <table>
            <tr>
                <td>
                    <table>
                    <tr>
                        <td width=120 align=right>아이디</td>
                        <td>&nbsp;&nbsp;<input class=ed maxlength=20 size=15 id='login_mb_id' name=mb_id itemname="아이디" required minlength="2" value="<?=$tmp_mb_id?>"></td>
                    </tr>
                    <tr>
                        <td width=120 align=right>패스워드</td>
                        <td>&nbsp;&nbsp;<input type=password class=ed maxlength=20 size=15 name=mb_password itemname="패스워드" required value="<?=$tmp_mb_password?>"></td>
                    </tr>
                    </table>
                </td>
                <td><input type=image src='<?=$member_skin_path?>/img/btn_confirm.gif' border=0 align=absmiddle></td>
            </tr>
            </table>
        </td>
    </tr>
    <tr><td height=30 align=center><a href="./register.php"><img src='<?=$member_skin_path?>/img/btn_member_join.gif' border=0></a>
            <a href="javascript:;" onclick="win_password_lost();"><img src='<?=$member_skin_path?>/img/btn_passfind.gif' border=0></a></td></tr>
    <tr><td background='<?=$member_skin_path?>/img/dot_line.gif'></td></tr>
    <tr><td height=60 style='padding-left:70px; line-height:150%'>
        · 회원이 아니실 경우에는 '무료 회원가입'을 하십시오.<br>
        · 패스워드를 잊으셨다면 '아이디/패스워드 찾기'로 찾으시면 됩니다.</td></tr>
    </table></td></tr>
</table>
</form>

<script language='Javascript'>
document.getElementById('login_mb_id').focus();

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




<? // 쇼핑몰 사용시 여기부터 ?>
<? if ($default[de_level_sell] == 1) { // 상품구입 권한 ?>

    <!-- 주문하기, 신청하기 -->
    <? if (preg_match("/orderform.php/", $url)) { ?>

        <br>
        <table width=480 cellpadding=3 cellspacing=0 align=center bgcolor=#F6F6F6>
        <tr><td align=center>
            <table  width=480 bgcolor=#FFFFFF cellpadding=0>
            <tr><td  height=60 align=center><img src='<?=$member_skin_path?>/img/title_guest.gif'></td></tr>
            <tr><td  style='padding-left:70px;'>
                <div style='overflow:auto; width:400px; height:150px; border:1px solid #000; padding:10px;'>
                    <?=$default[de_guest_privacy]?>
                </div>
                <div style='margin-bottom:10px;'>
                    <input type='checkbox' id='agree' value='1'>
                    개인정보수집에 대한 내용을 읽었으며 이에 동의합니다.
                </div>
                <div>· 비회원으로 주문하시는 경우 <font color=#2E84B4>포인트는 지급하지 않습니다.</font></div>
            </td></tr>
            <tr><td align=center height=100><a href="javascript:guest_submit(document.flogin);"><img src='<?=$member_skin_path?>/img/btn_guest.gif' border=0></a></td></tr>
            </table></td></tr>
        </table>

        <script language="javascript">
        function guest_submit(f)
        {
            if (document.getElementById('agree')) {
                if (!document.getElementById('agree').checked) {
                    alert("개인정보수집에 대한 내용을 읽고 이에 동의하셔야 합니다.");
                    return;
                }
            }

            //f.url.value = "<?=$g4[shop_path]?>/orderform.php";
            //f.action = "<?=$g4[shop_path]?>/orderform.php";
            f.url.value = "<?=$url?>";
            f.action = "<?=$url?>";
            f.submit();
        }
        </script>

    <? } else if (preg_match("/orderinquiry.php$/", $url)) { ?>

        <br>
        <!-- <form name=forderinquiry method=post action="<?=$url?>" autocomplete="off" style="padding:0px;"> -->
        <form name=forderinquiry method=post action="<?=urldecode($url)?>" autocomplete="off" style="padding:0px;">
        <table cellpadding=2 bgcolor=#F6F6F6 align=center>
        <tr><td>
            <table width=480 bgcolor=#FFFFFF cellpadding=0>
            <tr><td align=center height=60><img src='<?=$member_skin_path?>/img/title_order.gif'></td></tr>
            <tr>
                <td>
                    <table>
                    <tr>
                        <td>
                            <table>
                            <tr>
                                <td width=120 align=right>주문서번호</td>
                                <td>&nbsp;&nbsp;<input type=text name=od_id size=18 class=ed required itemname="주문서번호" value="<? echo $od_id ?>"></td>
                            </tr>
                            <tr>
                                <td width=120 align=right>패스워드</td>
                                <td>&nbsp;&nbsp;<input type=password name=od_pwd size=18 class=ed required itemname="패스워드"></td>
                            </tr>
                            </table>
                        </td>
                        <td><input type=image src='<?=$member_skin_path?>/img/btn_confirm.gif' border=0 align=absmiddle></td>
                    </tr>
                    </table>
                </td>
            </tr>
            <tr><td background='<?=$member_skin_path?>/img/dot_line.gif'></td></tr>
            <tr><td height=60 style='padding-left:70px; line-height:150%'>
                · 메일로 발송한 주문서에 있는 '주문서번호'를 입력하십시오.<br>
                · 주문서 작성시 입력한 '패스워드'를 입력하십시오.</td></tr>
            </table></td>
        </tr>
        </table>
        </form>

    <? } ?>

<? } ?>
<? // 쇼핑몰 사용시 여기까지 반드시 복사해 넣으세요 ?>