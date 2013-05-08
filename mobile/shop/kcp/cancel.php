<?
    /* ============================================================================== */
    /* =   PAGE : 취소 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   아래의 ※ 주의 ※ 부분을 꼭 참고하시여 연동을 진행하시기 바랍니다.       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.02   KCP Inc.   All Rights Reserverd.                = */
    /* ============================================================================== */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>*** KCP [AX-HUB Version] ***</title>
    <link href="css/sample.css" rel="stylesheet" type="text/css">

    <script type="text/javascript">
	// 취소 버튼을 눌렀을 때 호출
    function  jsf__go_cancel( form )
    {
        var RetVal = false ;
        if ( form.tno.value.length < 14 )
        {
            alert( "KCP 거래 번호를 입력하세요" );
            form.tno.focus();
            form.tno.select();
        }
        else
        {
            openwin = window.open( "proc_win.html", "proc_win", "width=449, height=209, top=300, left=300" );
            RetVal = true ;
        }
        return RetVal ;
    }

    </script>
</head>

<body>

<div align="center">
<?
    /* ============================================================================== */
    /* =    1. 취소 요청 정보 입력 폼(cancel_info)                                  = */
    /* = -------------------------------------------------------------------------- = */
    /* =   취소 요청에 필요한 정보를 설정합니다.                                    = */
    /* = -------------------------------------------------------------------------- = */
?>
    <form name="cancel_info" method="post" action="pp_ax_hub.php">

    <table width="589" cellspacing="0" cellpadding="0">
        <tr style="height:14px"><td style="background-image:url('./img/boxtop589.gif')"></td></tr>
        <tr>
            <td style="background-image:url('./img/boxbg589.gif')" align="center">

                <!-- 상단 테이블 Start -->
                <table width="551" cellspacing="0" cellpadding="16">
                    <tr style="height:17px">
                        <td style="background-image:url('./img/ttbg551.gif');border:0px" class="white">
                            <span class="bold big">[취소요청]</span> 이 페이지는 결제건에 대해 취소를 요청하는 샘플(예시) 페이지입니다.
                        </td>
                    </tr>
                    <tr>
                        <td style="background-image:url('./img/boxbg551.gif') ;">
                            <p class="align_left">소스 수정시 소스 안에 <span class="red bold">※ 주의 ※</span>표시가 포함된 문장은
                            가맹점의 상황에 맞게 적절히 수정 적용하시기 바랍니다.</p>
                            <p class="align_left">이 페이지는 결제된 건에 대한 취소를 요청하는 페이지 입니다.</p>
                            <p class="align_left">
                            결제가 승인되면 결과값으로 KCP 거래번호(tno)값을 받으실 수 있습니다..<br/>
                            가맹점에서는 이 KCP 거래번호(tno)값으로 취소요청을 하실 수 있습니다.</p>
                        </td>
                    </tr>
                    <tr style="height:11px"><td style="background:url('./img/boxbtm551.gif') no-repeat;"></td></tr>
                </table>
                <!-- 상단 테이블 End -->

                <!-- 취소 요청 정보 입력 테이블 Start -->
                <table width="527" cellspacing="0" cellpadding="0" class="margin_top_20">
                    <tr><td colspan="2" class="title">취소 요청 정보</td></tr>
                    <!-- 요청 구분 : 취소 -->
                    <tr>
                        <td class="sub_title1">요청 구분</td>
                        <td class="sub_content1 bold">취소 요청</td>
                    </tr>
                    <!-- Input : 결제된 건의 거래번호(14 byte) 입력 -->
                    <tr>
                        <td class="sub_title1">KCP 거래번호</td>
                        <td class="sub_input1"><input type="text" name="tno" value=""  class="frminput" size="20" maxlength="14"/></td>
                    </tr>
                    <!-- Input : 변경 사유(mod_desc) 입력 -->
                    <tr>
                        <td class="sub_title1">변경 사유</td>
                        <td class="sub_input1"><input type="text" name="mod_desc" value="" class="frminput" size="30" maxlength="50"/></td>
                    </tr>
                </table>
                <!-- 취소 요청 정보 입력 테이블 End -->

                <!-- 요청 버튼 테이블 Start -->
                <table width="527" cellspacing="0" cellpadding="0" class="margin_top_20">
                    <!-- 취소 요청/처음으로 이미지 버튼 -->
                    <tr>
                        <td colspan="2" align="center">
                            <input type="image" src="./img/btn_cancel.gif" onclick="return jsf__go_cancel(this.form);" width="108" height="37" alt="취소를 요청합니다" /></a>
                            <a href="index.html"><img src="./img/btn_home.gif" width="108" height="37" alt="처음으로 이동합니다" /></a>
                        </td>
                    </tr>
                </table>
                <!-- 요청 버튼 테이블 End -->
            </td>
        </tr>
        <tr><td><img src="./img/boxbtm589.gif" alt="Copyright(c) KCP Inc. All rights reserved."/></td></tr>
    </table>

<?
    /* ============================================================================== */
    /* =   1-1. 취소 요청 필수 정보 설정                                            = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수 - 반드시 필요한 정보입니다.                                      = */
    /* = -------------------------------------------------------------------------- = */
?>
        <input type="hidden" name="req_tx"   value="mod"  />
        <input type="hidden" name="mod_type" value="STSC" />
<?
    /* = -------------------------------------------------------------------------- = */
    /* =   1. 취소 요청 필수 정보 설정 End                                          = */
    /* ============================================================================== */
?>
    </form>
</div>
</body>
</html>
