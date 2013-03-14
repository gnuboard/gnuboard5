<?
include_once ("../config.php");

// 퍼미션을 다음과 같은 형식으로 얻는다. drwxrwxrwx
function get_perms($mode)
{
    /* Determine Type */
    if( $mode & 0x1000 )
        $perms["type"] = 'p'; /* FIFO pipe */
    else if( $mode & 0x2000 )
        $perms["type"] = 'c'; /* Character special */
    else if( $mode & 0x4000 )
        $perms["type"] = 'd'; /* Directory */
    else if( $mode & 0x6000 )
        $perms["type"] = 'b'; /* Block special */
    else if( $mode & 0x8000 )
        $perms["type"] = '-'; /* Regular */
    else if( $mode & 0xA000 )
        $perms["type"] = 'l'; /* Symbolic Link */
    else if( $mode & 0xC000 )
        $perms["type"] = 's'; /* Socket */
    else
        $perms["type"] = 'u'; /* UNKNOWN */

    /* Determine permissions */
    $perms["owner_read"]    = ($mode & 00400) ? 'r' : '-';
    $perms["owner_write"]   = ($mode & 00200) ? 'w' : '-';
    $perms["owner_execute"] = ($mode & 00100) ? 'x' : '-';
    $perms["group_read"]    = ($mode & 00040) ? 'r' : '-';
    $perms["group_write"]   = ($mode & 00020) ? 'w' : '-';
    $perms["group_execute"] = ($mode & 00010) ? 'x' : '-';
    $perms["world_read"]    = ($mode & 00004) ? 'r' : '-';
    $perms["world_write"]   = ($mode & 00002) ? 'w' : '-';
    $perms["world_execute"] = ($mode & 00001) ? 'x' : '-';

    /* Adjust for SUID, SGID and sticky bit */
    if( $mode & 0x800 )
        $perms["owner_execute"] = ($perms["owner_execute"]=='x') ? 's' : 'S';
    if( $mode & 0x400 )
        $perms["group_execute"] = ($perms["group_execute"]=='x') ? 's' : 'S';
    if( $mode & 0x200 )
        $perms["world_execute"] = ($perms["world_execute"]=='x') ? 't' : 'T';

    return $perms;
}

// 파일이 존재한다면 설치할 수 없다.
if (file_exists("../dbconfig.php")) {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo <<<HEREDOC
    <script language="JavaScript">
    alert("설치하실 수 없습니다.");
    location.href="../";
    </script>
HEREDOC;
    exit;
}


// 루트 디렉토리에 파일, 디렉토리 생성 가능한지 검사.
$perms = get_perms(fileperms("../"));
if ($perms["world_read"].$perms["world_write"].$perms["world_execute"] != "rwx") {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo <<<HEREDOC
    <script language="JavaScript">
    alert("루트 디렉토리의 퍼미션을 707로 변경하여 주십시오.\\n\\ncommon.php 파일이 있는곳이 루트 디렉토리 입니다.\\n\\n$> chmod 707 . \\n\\n그 다음 설치하여 주십시오.");
    </script>
HEREDOC;
    exit;
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>영카트4 설치 (1/3) - 라이센스(License)</title>
<style type="text/css">
.body {
    font-family: 굴림;
	font-size: 12px;
}
.box {
	background-color: #D6D3CE;
    font-family:굴림;
	font-size: 12px;
}
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
			
<textarea name="textarea" cols="88" rows="10" class="box" readonly>
<?=implode("", file("../LICENSE_YC4"));?>
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
                <form name=frm method=post action="javascript:frm_submit(document.frm);">
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