<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=600 cellspacing=0 cellspacing=0 align=center><tr><td>

<table width="100%" cellspacing="0" cellpadding="0">
<form name="fregister" method="POST" onsubmit="return fregister_submit(this);" autocomplete="off">
<tr> 
    <td align=center><img src="<?=$member_skin_path?>/img/join_title.gif" width="624" height="72"></td>
</tr>
</table>

<? if ($config[cf_use_jumin]) { // 주민등록번호를 사용한다면 ?>
<!-- 2012년 8월 부터 주민등록번호 수집과 이용이 제한됨 (사실상 수집 금지)
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height=25></td>
</tr>
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100% border=0>
        <TR bgcolor="#FFFFFF"> 
            <TD width="140" height=30>&nbsp;&nbsp;&nbsp;<b>이름</b></TD>
            <TD width="">&nbsp;&nbsp;&nbsp;<INPUT name=mb_name itemname="이름" required minlength="2" nospace hangul></TD>
        </TR>
        <TR bgcolor="#FFFFFF"> 
            <TD height=30>&nbsp;&nbsp;&nbsp;<b>주민등록번호</b></TD>
            <TD>&nbsp;&nbsp;&nbsp;<INPUT name=mb_jumin itemname="주민등록번호" required jumin minlength="13" maxLength=13><font style="font-family:돋움; font-size:9pt; color:#66A2C8">&nbsp;&nbsp;※ 숫자 13자리 중간에 - 없이 입력하세요.</font></TD>
        </TR>
        </TABLE></td>
</tr>
</table>
-->
<? } ?>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td height="20"></td>
</tr>
<tr> 
    <td height="48" background="<?=$member_skin_path?>/img/login_table_bg_top.gif">&nbsp; <b>회원가입약관</b></td>
</tr>
<tr> 
    <td height="223" align="center" valign="top" background="<?=$member_skin_path?>/img/login_table_bg.gif"><TEXTAREA style="WIDTH: 100%" rows=15 readOnly><?=get_text($config[cf_stipulation])?></TEXTAREA></td>
</tr>
<tr> 
    <td>
        &nbsp; <input type=radio value=1 name=agree id=agree11>&nbsp;<label for=agree11>동의합니다.</label>
        &nbsp; <input type=radio value=0 name=agree id=agree10>&nbsp;<label for=agree10>동의하지 않습니다.</label>
    </td>
</tr>
<tr> 
    <td height="35" background="<?=$member_skin_path?>/img/line.gif"></td>
</tr>

<tr>
    <td height="20"></td>
</tr>
<tr> 
    <td height="48" background="<?=$member_skin_path?>/img/login_table_bg_top.gif">&nbsp; <b>개인정보취급방침</b></td>
</tr>
<tr> 
    <td height="223" align="center" valign="top" background="<?=$member_skin_path?>/img/login_table_bg.gif"><TEXTAREA style="WIDTH: 100%" rows=15 readOnly><?=get_text($config[cf_privacy])?></TEXTAREA></td>
</tr>
<tr> 
    <td>
        &nbsp; <input type=radio value=1 name=agree2 id=agree21>&nbsp;<label for=agree21>동의합니다.</label>
        &nbsp; <input type=radio value=0 name=agree2 id=agree20>&nbsp;<label for=agree20>동의하지 않습니다.</label>
    </td>
</tr>
<tr> 
    <td height="35" background="<?=$member_skin_path?>/img/line.gif"></td>
</tr>

<tr> 
    <td align="center"><INPUT type=image width="66" height="20" src="<?=$member_skin_path?>/img/join_ok_btn.gif" border=0></td>
</tr>
</form>
</table>

<script language="javascript">
function fregister_submit(f)
{
    var agree1 = document.getElementsByName("agree");
    if (!agree1[0].checked) {
        alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        agree1[0].focus();
        return false;
    }

    var agree2 = document.getElementsByName("agree2");
    if (!agree2[0].checked) {
        alert("개인정보취급방침의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        agree2[0].focus();
        return false;
    }

    f.action = "./register_form.php";
    f.submit();
}

if (typeof(document.fregister.mb_name) != "undefined")
    document.fregister.mb_name.focus();
</script>

</td></tr></table>