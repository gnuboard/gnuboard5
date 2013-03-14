<?
// 회원가입 메일 (관리자 메일로 발송)
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title>회원가입 메일</title>
</head>

<style>
body, th, td, form, input, select, text, textarea, caption { font-size: 12px; font-family:굴림;}
.line {border: 1px solid #868F98;}
</style>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="25" height="25"></td>
    <td height="25"></td>
    <td width="25" height="25"></td>
</tr>
<tr>
    <td width="25" valign="top"></td>
    <td align="center" class="line" >
        <br>
        <table width="500" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table width="500" border="0" cellspacing="0" cellpadding="4">
                <tr> 
                    <td width="100%" height="25" bgcolor=#F7F1D8>회원가입 메일</td>
                </tr>
                </table>
                <p>

                <table width="500" border="0" align="center" cellpadding="4" cellspacing="0">
                <tr><td height="150"><b><?=$mb_name?></b> 님께서 회원가입 하셨습니다.
                    <p>회원 아이디 : <b><?=$mb_id?></b>
                    <p>회원 이름 : <?=$mb_name?>
                    <p>회원 별명 : <?=$mb_nick?>
                    <p>추천인아이디 : <?=$mb_recommend?></td></tr>
                </table>
                <p>

                <table width="500" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed">
                <tr>
                    <td height="2" bgcolor="#E0E0E0" align="center"></td>
                </tr>
                <tr> 
                    <td height="25" bgcolor="#EDEDED" align="center">관리자화면에서 자세한 내용을 확인하실 수 있습니다.[<a href="<?=$g4[url]?>/<?=$g4[admin]?>/member_form.php?w=u&mb_id=<?=$mb_id?>">바로가기</a>]</td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
        <br>
    </td>
    <td width="25" valign="top"></td>
</tr>
</table>

</body>
</html>
