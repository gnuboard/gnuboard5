<?
include_once ("../config.php");
include_once ("./install.inc.php");
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title>그누보드4 설치 (1/3) - 라이센스(License)</title>
<style type="text/css">
<!--
.body {
    font-family: 굴림;
	font-size: 12px;
}
.box {
	background-color: #D6D3CE;
    font-family:굴림;
	font-size: 12px;
}
-->
</style>
</head>

<body background="img/all_bg.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <table width="587" border="0" cellspacing="0" cellpadding="0" align=center>
    <tr> 
        <td colspan="3"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="587" height="22">
                <param name="movie" value="img/top.swf">
                <param name="quality" value="high">
                <embed src="img/top.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="587" height="22"></embed></object></td>
    </tr>
    <tr> 
      <td width="3"><img src="img/box_left.gif" width="3" height="340"></td>
      <td width="581" valign="top" bgcolor="#FCFCFC">
	  <table width="581" border="0" cellspacing="0" cellpadding="0">
          <tr> 
                    <td><img src="img/box_title.gif" width="581" height="56"></td>
          </tr>
      </table>
      <table width="541" border="0" align="center" cellpadding="0" cellspacing="0" class="body">
          <tr> 
            <td height="10"></td>
          </tr>
          <tr> 
            <td>라이센스(License) 내용을 반드시 확인하십시오.</td>
          </tr>
          <tr> 
            <td height="10"></td>
          </tr>
          <tr> 
            <td align="center">
			
<textarea name="textarea" style='width:99%' rows="9" class="box" readonly>
<?=implode("", file("../LICENSE"));?>
</textarea> 

            </td>
          </tr>
          <tr>
            <td height=10></td>
          </tr>
          <tr> 
            <td>설치를 원하시면 위 내용에 동의하셔야 합니다.<br>
              동의를 원하시면 &lt;예, 동의합니다&gt; 버튼을 클릭해 주세요.</td>
          </tr>
        </table>
        <table width="562" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td height=20><img src="img/box_line.gif" width="562" height="2"></td>
          </tr>
        </table>
        <table width="551" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr> 
            <td align="right"> 
                <form name=frm method=post onsubmit="return frm_submit(document.frm);">
                <input type="hidden" name="agree" value="동의함">
                <input type="submit" name="btn_submit" value="예, 동의합니다 ">
                </form>
            </td>
          </tr>
        </table>
		</td>
      <td width="3"><img src="img/box_right.gif" width="3" height="340"></td>
    </tr>
    <tr> 
      <td colspan="3"><img src="img/box_bottom.gif" width="587" height="3"></td>
    </tr>
  </table>

<script language="JavaScript">
function frm_submit(f)
{
    f.action = "./install_config.php";
    f.submit();
}

document.frm.btn_submit.focus();
</script>

</body>
</html>