<?
// 설문조사 기타의견 입력시 관리자께 보내는 메일을 수정하고 싶으시다면 이 파일을 수정하십시오. 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title>설문조사 기타의견 메일</title>
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
                    <td height="25" bgcolor=#F7F1D8>제목 : <?=$subject?></td>
                </tr>
                <tr> 
                    <td height="25" bgcolor=#F7F1D8>게시자 : <?=$name?> <?=$mb_id?></td>
                </tr>
                </table>
                <p>

                <table width="500" border="0" align="center" cellpadding="4" cellspacing="0">
                <tr><td height="150"><?=$content?></td></tr>
                </table>
                <p>
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
