<?php
    include_once('./_common.php');
    /* ============================================================================== */
    /* =   PAGE : 결제 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   이 페이지는 주문 페이지를 통해서 결제자가 결제 요청을 하는 페이지        = */
    /* =   입니다. 아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을        = */
    /* =   진행하여 주시기 바랍니다.                                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.05   KCP Inc.   All Rights Reserved.                 = */
    /* ============================================================================== */
?>
<?php
	/* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

     include G5_MSHOP_PATH.'/settle_kcp.inc.php';       // 환경설정 파일 include
?>
<?php
    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
?>
<?php
    /* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보*/
    $req_tx          = $_POST[ "req_tx"         ]; // 요청 종류
    $res_cd          = $_POST[ "res_cd"         ]; // 응답 코드
    $tran_cd         = $_POST[ "tran_cd"        ]; // 트랜잭션 코드
    $ordr_idxx       = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호
    $good_name       = $_POST[ "good_name"      ]; // 상품명
    $good_mny        = $_POST[ "good_mny"       ]; // 결제 총금액
    $buyr_name       = $_POST[ "buyr_name"      ]; // 주문자명
    $buyr_tel1       = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호
    $buyr_tel2       = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
    $buyr_mail       = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
    $use_pay_method  = $_POST[ "use_pay_method" ]; // 결제 방법
    $enc_info        = $_POST[ "enc_info"       ]; // 암호화 정보
    $enc_data        = $_POST[ "enc_data"       ]; // 암호화 데이터
	$rcvr_name		 = $_POST[ "rcvr_name"		]; // 수취인 이름
	$rcvr_tel1		 = $_POST[ "rcvr_tel1"		]; // 수취인 전화번호
	$rcvr_tel2		 = $_POST[ "rcvr_tel2"		]; // 수취인 휴대폰번호
	$rcvr_mail		 = $_POST[ "rcvr_mail"		]; // 수취인 E-Mail
	$rcvr_zipx		 = $_POST[ "rcvr_zipx"		]; // 수취인 우편번호
	$rcvr_add1		 = $_POST[ "rcvr_add1"		]; // 수취인 주소
	$rcvr_add2		 = $_POST[ "rcvr_add2"		]; // 수취인 상세주소

    /* 주문폼에서 전송되는 정보 */
    $ipgm_date       = $_POST[ "ipgm_date"      ]; // 입금마감일
    $settle_method   = $_POST[ "settle_method"  ]; // 결제방법
    $good_info       = $_POST[ "good_info"      ]; // 에스크로 상품정보
    $bask_cntx       = $_POST[ "bask_cntx"      ]; // 장바구니 상품수
    $tablet_size     = $_POST[ "tablet_size"    ]; // 모바일기기 화면비율

    $comm_tax_mny    = $_POST[ "comm_tax_mny"   ]; // 과세금액
    $comm_vat_mny    = $_POST[ "comm_vat_mny"   ]; // 부가세
    $comm_free_mny   = $_POST["comm_free_mny"   ]; // 비과세금액

	/*
     * 기타 파라메터 추가 부분 - Start -
     */
    $param_opt_1     = $_POST[ "param_opt_1"    ]; // 기타 파라메터 추가 부분
    $param_opt_2     = $_POST[ "param_opt_2"    ]; // 기타 파라메터 추가 부분
    $param_opt_3     = $_POST[ "param_opt_3"    ]; // 기타 파라메터 추가 부분
    /*
     * 기타 파라메터 추가 부분 - End -
     */

	/* kcp 데이터 캐릭터셋 변환 */
    if($res_cd != '') {
        $good_name = iconv('euc-kr', 'utf-8', $good_name);
        $buyr_name = iconv('euc-kr', 'utf-8', $buyr_name);
        $rcvr_name = iconv('euc-kr', 'utf-8', $rcvr_name);
        $rcvr_add1 = iconv('euc-kr', 'utf-8', $rcvr_add1);
        $rcvr_add2 = iconv('euc-kr', 'utf-8', $rcvr_add2);
    }

    switch($settle_method)
    {
        case '신용카드':
            $pay_method = 'CARD';
            $ActionResult = 'card';
            break;
        case '계좌이체':
            $pay_method = 'BANK';
            $ActionResult = 'acnt';
            break;
        case '휴대폰':
            $pay_method = 'MOBX';
            $ActionResult = 'mobx';
            break;
        case '가상계좌':
            $pay_method = 'VCNT';
            $ActionResult = 'vcnt';
            break;
        default:
            $pay_method = '';
            $ActionResult = '';
            break;
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
<title>스마트폰 웹 결제창</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-Control" content="No-Cache">
<meta http-equiv="Pragma" content="No-Cache">
<meta name="viewport" content="width=device-width; user-scalable=<?php echo $tablet_size; ?>; initial-scale=<?php echo $tablet_size; ?>; maximum-scale=<?php echo $tablet_size; ?>; minimum-scale=<?php echo $tablet_size; ?>">

<style type="text/css">
	.LINE { background-color:#afc3ff }
	.HEAD { font-family:"굴림","굴림체"; font-size:9pt; color:#065491; background-color:#eff5ff; text-align:left; padding:3px; }
	.TEXT { font-family:"굴림","굴림체"; font-size:9pt; color:#000000; background-color:#FFFFFF; text-align:left; padding:3px; }
	    B { font-family:"굴림","굴림체"; font-size:13pt; color:#065491;}
	INPUT { font-family:"굴림","굴림체"; font-size:9pt; }
	SELECT{font-size:9pt;}
	.COMMENT { font-family:"굴림","굴림체"; font-size:9pt; line-height:160% }
</style>
<!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
<script src="<?php echo G5_MSHOP_URL; ?>/kcp/approval_key.js"></script>


<script language="javascript">
	/* kcp web 결제창 호출 (변경불가)*/
    function call_pay_form()
    {

       var v_frm = document.sm_form;

        layer_cont_obj   = document.getElementById("content");
        layer_receipt_obj = document.getElementById("layer_receipt");

        layer_cont_obj.style.display = "none";
        layer_receipt_obj.style.display = "block";

        v_frm.target = "frm_receipt";
        v_frm.action = PayUrl;

		if(v_frm.Ret_URL.value == "")
		{
			/* Ret_URL값은 현 페이지의 URL 입니다. */
			alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");
			return false;
		}

        v_frm.submit();
    }


	/* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청*/
    function chk_pay()
    {
        /*kcp 결제서버에서 가맹점 주문페이지로 폼값을 보내기위한 설정(변경불가)*/

        var sm_form = document.sm_form;

        if (sm_form.res_cd.value == "3001" )
        {
            alert("사용자가 취소하였습니다.");
            window.close();
            return false;
        }
        else if (sm_form.res_cd.value == "3000" )
        {
            alert("30만원 이상 결제 할수 없습니다.");
            window.close();
            return false;
        }

        if (sm_form.enc_data.value != "" && sm_form.enc_info.value != "" && sm_form.tran_cd.value !="" )
        {
            var of = window.opener.document.forderform;

            of.req_tx.value         = "<?php echo $req_tx; ?>";
            of.res_cd.value         = "<?php echo $res_cd; ?>";
            of.tran_cd.value        = "<?php echo $tran_cd; ?>";
            of.ordr_idxx.value      = "<?php echo $ordr_idxx; ?>";
            of.good_mny.value       = "<?php echo $good_mny; ?>";
            of.good_name.value      = "<?php echo $good_name; ?>";
            of.buyr_name.value      = "<?php echo $buyr_name; ?>";
            of.buyr_tel1.value      = "<?php echo $buyr_tel1; ?>";
            of.buyr_tel2.value      = "<?php echo $buyr_tel2; ?>";
            of.buyr_mail.value      = "<?php echo $buyr_mail; ?>";
            of.enc_info.value       = "<?php echo $enc_info; ?>";
            of.enc_data.value       = "<?php echo $enc_data; ?>";
            of.use_pay_method.value = "<?php echo $use_pay_method; ?>";
            of.rcvr_name.value      = "<?php echo $rcvr_name; ?>";
            of.rcvr_tel1.value      = "<?php echo $rcvr_tel1; ?>";
            of.rcvr_tel2.value      = "<?php echo $rcvr_tel2; ?>";
            of.rcvr_mail.value      = "<?php echo $rcvr_mail; ?>";
            of.rcvr_zipx.value      = "<?php echo $rcvr_zipx; ?>";
            of.rcvr_add1.value      = "<?php echo $rcvr_add1; ?>";
            of.rcvr_add2.value      = "<?php echo $rcvr_add2; ?>";
            of.param_opt_1.value    = "<?php echo $param_opt_1; ?>";
            of.param_opt_2.value    = "<?php echo $param_opt_2; ?>";
            of.param_opt_3.value    = "<?php echo $param_opt_3; ?>";

            //alert("주문하기를 클릭하셔야 주문이 완료됩니다.");
            window.opener.forderform_check();
            window.close();
        } else {
            kcp_AJAX();
        }
    }

</script>
</head>
<body onload="chk_pay();">

<div id="content">

<form name="sm_form" method="POST" accept-charset="euc-kr">

<input type="hidden" name="good_name" value="<?php echo $good_name; ?>">
<input type="hidden" name="good_mny"  value="<?php echo $good_mny; ?>" >
<input type="hidden" name='buyr_name' value="<?php echo $buyr_name; ?>">
<input type="hidden" name="buyr_tel1" value="<?php echo $buyr_tel1; ?>">
<input type="hidden" name="buyr_tel2" value="<?php echo $buyr_tel2; ?>">
<input type="hidden" name="buyr_mail" value="<?php echo $buyr_mail; ?>">
<input type="hidden" name="ipgm_date" value="<?php echo $ipgm_date; ?>">

<!-- 필수 사항 -->

<!-- 요청 구분 -->
<input type="hidden" name="req_tx"       value="pay">
<!-- 사이트 코드 -->
<input type="hidden" name="site_cd"      value="<?php echo $g_conf_site_cd; ?>">
 <!-- 사이트 이름 -->
<input type="hidden" name="shop_name"    value="<?php echo $g_conf_site_name; ?>">
<!-- 결제수단-->
<input type="hidden" name="pay_method"   value="<?php echo $pay_method; ?>">
<!-- 주문번호 -->
<input type="hidden"   name="ordr_idxx"    value="<?php echo $ordr_idxx; ?>">
<!-- 최대 할부개월수 -->
<input type="hidden" name="quotaopt"     value="12">
<!-- 통화 코드 -->
<input type="hidden" name="currency"     value="410">
<!-- 결제등록 키 -->
<input type="hidden" name="approval_key" id="approval">
<!-- 리턴 URL (kcp와 통신후 결제를 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
<!-- 반드시 가맹점 주문페이지의 URL을 입력 해주시기 바랍니다. -->
<input type="hidden" name="Ret_URL"      value="<?php echo G5_MSHOP_URL; ?>/kcp/order_approval_form.php">
<!-- 인증시 필요한 파라미터(변경불가)-->
<input type="hidden" name="ActionResult" value="<?php echo $ActionResult; ?>">
<!-- 에스크로 사용유무 에스크로 사용 업체(가상계좌만 해당)는 Y로 세팅 해주시기 바랍니다.-->
<input type="hidden" name="escw_used"  value="Y">
<!-- 에스크로 결제처리모드 -->
<input type="hidden" name="pay_mod"   value="<?php echo ($default['de_escrow_use']?'O':'N'); ?>">
<!-- 수취인이름 -->
<input type="hidden" name="rcvr_name" value="<?php echo $rcvr_name; ?>">
<!-- 수취인 연락처 -->
<input type="hidden" name="rcvr_tel1" value="<?php echo $rcvr_tel1; ?>">
<!-- 수취인 휴대폰 번호 -->
<input type="hidden" name="rcvr_tel2" value="<?php echo $rcvr_tel2; ?>">
<!-- 수취인 E-MAIL -->
<input type="hidden" name="rcvr_add1" value="<?php echo $rcvr_add1; ?>">
<!-- 수취인 우편번호 -->
<input type="hidden" name="rcvr_add2" value="<?php echo $rcvr_add2; ?>">
<!-- 수취인 주소 -->
<input type="hidden" name="rcvr_mail" value="<?php echo $rcvr_mail; ?>">
<!-- 수취인 상세 주소 -->
<input type="hidden" name="rcvr_zipx" value="<?php echo $rcvr_zipx; ?>">
<!-- 장바구니 상품 개수 -->
<input type="hidden" name="bask_cntx" value="<?php echo $bask_cntx; ?>">
<!-- 장바구니 정보(상단 스크립트 참조) -->
<input type="hidden" name="good_info" value="<?php echo $good_info; ?>">
<!-- 배송소요기간 -->
<input type="hidden" name="deli_term" value="03">
<!-- 기타 파라메터 추가 부분 - Start - -->
<input type="hidden" name="param_opt_1"	 value="<?php echo $param_opt_1; ?>"/>
<input type="hidden" name="param_opt_2"	 value="<?php echo $param_opt_2; ?>"/>
<input type="hidden" name="param_opt_3"	 value="<?php echo $param_opt_3; ?>"/>
<input type="hidden" name="disp_tax_yn"  value="N">
<!-- 기타 파라메터 추가 부분 - End - -->
<!-- 화면 크기조정 부분 - Start - -->
<input type="hidden" name="tablet_size"	 value="<?php echo $tablet_size; ?>"/>
<!-- 화면 크기조정 부분 - End - -->
<!--
	사용 카드 설정
	<input type="hidden" name="used_card"    value="CClg:ccDI">
    /*  무이자 옵션
            ※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
            ※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
            ※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
    <input type="hidden" name="kcp_noint"       value=""/> */

    /*  무이자 설정
            ※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
            ※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
            예) 전 카드 2,3,6개월 무이자(국민,비씨,엘지,삼성,신한,현대,롯데,외환) : ALL-02:03:04
            BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
    <input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"/> */
-->
<input type="hidden" name="kcp_noint"       value="<?php echo ($default['de_card_noint_use'] ? '' : 'N'); ?>">

<?php
if($default['de_tax_flag_use']) {
    /* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자,
       복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다

       복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다

       상품별이 아니라 금액으로 구분하여 요청하셔야 합니다

       총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다.
       (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny) */
?>
<input type="hidden" name="tax_flag"          value="TG03">     <!-- 변경불가    -->
<input type="hidden" name="comm_tax_mny"	  value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
<input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php
}
?>

<input type="hidden" name="res_cd"         value="<?php echo $res_cd; ?>">      <!-- 결과 코드          -->
<input type="hidden" name="tran_cd"        value="<?php echo $tran_cd; ?>">     <!-- 트랜잭션 코드      -->
<input type="hidden" name="enc_info"       value="<?php echo $enc_info; ?>">    <!-- 암호화 정보        -->
<input type="hidden" name="enc_data"       value="<?php echo $enc_data; ?>">    <!-- 암호화 데이터      -->
</form>
</div>

<!-- 스마트폰에서 KCP 결제창을 레이어 형태로 구현-->
<div id="layer_receipt" style="position:absolute; left:1px; top:1px; width:310;height:400; z-index:1; display:none;">
    <table width="310" border="-" cellspacing="0" cellpadding="0" style="text-align:center">
        <tr>
            <td>
                <iframe name="frm_receipt" frameborder="0" border="0" width="310" height="400" scrolling="auto"></iframe>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
