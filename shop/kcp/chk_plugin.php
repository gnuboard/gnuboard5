<?
include "./_common.php";

if (strtolower($g4['charset']) == 'utf-8')
    $js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}_un.js";
else
    $js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}.js";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>한국사이버결제 플러그인 설치 페이지</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=$g4['charset']?>"/>
    <meta http-equiv="Cache-Control" content="no-cache"/>
    <meta http-equiv="Expires" content="0"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <link rel="stylesheet" href="css/sample.css" type="text/css" />

    <script type="text/javascript" src="<?=$js_url?>"></script>

    <script type="text/javascript">
	StartSmartUpdate();

	function  jsf__chk_plugin()
	{
	    if ( document.Payplus.object != null )
	    {
	        //window.location = "order.php";
            self.close();
	    }
	}
    </script>

    <script type="text/javascript">
    	setTimeout("jsf__chk_plugin()","1000");
    </script>
</head>

<body>
<div align="center">
    <table width="600" cellspacing="0" cellpadding="0">
	    <tr>
	        <td><img src="img/kcp_plug_img01.gif" width="600" height="84" alt=""/></td>
	    </tr>
	    <tr>
	        <td style="background-image:url('img/kcp_plug_bg01.gif');">
	            <table style="width:550;text-align:center">
	            <tr>
	                <td height="50" colspan="2" align="center" >고객님의 안전한 결제를 위해 결제 정보를 암호화하는 프로그램을 확인(설치)하는 중입니다.</td>
	            </tr>
	            <tr>
	                <td height="60" colspan="2" align="center" valign="top"><img src="img/kcp_ing.gif" style="width:481px;height:31px;" alt=""/></td>
	            </tr>
	            <tr>
	                <td colspan="2" style="padding-left:50px;text-align:left;">
	                    1. 페이지 상단의 알림표시줄이 나타났을 경우 알림표시줄에서 마우스 오른쪽 버튼을 눌러 <br />
	                    <span class="red bold">"ActiveX 컨트롤 설치"</span>를 선택하여 주십시오. <br />
	                    2. <u>보안경고 창이 나타나면 <span class="red bold">"설치"</span> 또는 <span class="red bold">"예"</span> 버튼을 눌러
	                    설치를 진행하여 주십시오.</u> <br />
	                    &nbsp;&nbsp;&nbsp;통신 환경에 따라 2-3초에서 수분이 걸릴수도 있습니다. <br />
	                </td>
	            </tr>
	            <tr>
	                <td height="30" colspan="2" align="center"><img src="img/kcp_plug_img02.gif" style="width:530px;height:11px;" alt="" /></td>
	            </tr>
	            <tr>
	                <td width="153" rowspan="2" align="center"><img src="img/kcp_plug_img03.gif" style="width:103px;height:97px;" alt="" /></td>
	                <td style="width:397px;height:30px;text-align:left;"><img src="img/kcp_plug_img04.gif" style="width:14px;height:14px" alt="" />
	                    <span class="bold">프로그램이 자동으로 설치가 되지 않는 경우</span>
	                </td>
	            </tr>
	            <tr>
    	            <td style="text-align:left;">
	                    <!-- 1. <a href="http://pay.kcp.co.kr/plugin/file_vista/PayplusWizard.exe"><span class="red bold">이곳</span></a>을 눌러 -->
	                    1. <a href="http://pay.kcp.co.kr/plugin_new/file/KCPUXWizard.exe"><span class="red bold">이곳</span></a>을 눌러
	                    설치 파일을 다운로드하여 주십시오.<br />
	                    2. 다운받은 프로그램을 실행하시면 설치가 진행됩니다.<br />
	                    3. 설치가 완료된 후 키보드의 &quot;F5&quot; 버튼을 눌러 새로고침을 하면<br />
	                    &nbsp;&nbsp;&nbsp;다음 결제 페이지로 이동합니다.
	                </td>
	            </tr>
	            </table>
    	    </td>
	    </tr>
	    <tr>
	        <td><img src="img/kcp_plug_img05.gif" width="600" height="20" alt="" /></td>
	    </tr>
	</table>
</div>
</body>
</html>
