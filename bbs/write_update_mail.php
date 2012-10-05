<?
// 게시물 입력시 게시자, 관리자에게 드리는 메일을 수정하고 싶으시다면 이 파일을 수정하십시오.
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title><?=$wr_subject?> 메일</title>
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
                                <td width="10%" height="25" bgcolor=#F7F1D8>제목</td>
                                <td width="90%" bgcolor=#FBF7E7><?=$wr_subject?></td>
                            </tr>
                            <tr bgcolor="#FFFFFF"> 
                                <td height="2" colspan="2"></td>
                            </tr>
                            <tr> 
                                <td height="25" bgcolor=#F7F1D8>게시자</td>
                                <td bgcolor=#FBF7E7><?=$wr_name?></td>
                            </tr>
                        </table>
                <p>

                <table width="500" border="0" align="center" cellpadding="4" cellspacing="0">
                <tr><td height="150" style="word-break:break-all;"><?=$wr_content?></td></tr>
                </table>
                <p>

                        <table width="500" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed">
                            <tr>
                                <td height="2" bgcolor="#E0E0E0" align="center"></td>
                            </tr>
                            <tr> 
                                <td height="25" bgcolor="#EDEDED" align="center">홈페이지에서도 게시물을 확인하실 수 있습니다.[<a href='<?=$link_url?>'>바로가기</a>]</td>
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
