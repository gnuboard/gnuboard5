<?php
include_once('./_common.php');

@header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
@header('Pragma: no-cache'); // HTTP 1.0.
@header('Expires: 0'); // Proxies.

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

	/* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

     include_once(G5_MSHOP_PATH.'/settle_kcp.inc.php');       // 환경설정 파일 include

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */

    /* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보*/
    $req_tx          = isset($_POST["req_tx"]) ? $_POST["req_tx"] : ''; // 요청 종류
    $res_cd          = isset($_POST["res_cd"]) ? $_POST["res_cd"] : ''; // 응답 코드
    $tran_cd         = isset($_POST["tran_cd"]) ? $_POST["tran_cd"] : ''; // 트랜잭션 코드
    $ordr_idxx       = isset($_POST["ordr_idxx"]) ? $_POST["ordr_idxx"] : ''; // 쇼핑몰 주문번호
    $good_name       = isset($_POST["good_name"]) ? $_POST["good_name"] : ''; // 상품명
    $good_mny        = isset($_POST["good_mny"]) ? $_POST["good_mny"] : ''; // 결제 총금액
    $buyr_name       = isset($_POST["buyr_name"]) ? $_POST["buyr_name"] : ''; // 주문자명
    $buyr_tel1       = isset($_POST["buyr_tel1"]) ? $_POST["buyr_tel1"] : ''; // 주문자 전화번호
    $buyr_tel2       = isset($_POST["buyr_tel2"]) ? $_POST["buyr_tel2"] : ''; // 주문자 핸드폰 번호
    $buyr_mail       = isset($_POST["buyr_mail"]) ? $_POST["buyr_mail"] : ''; // 주문자 E-mail 주소
    $use_pay_method  = isset($_POST["use_pay_method"]) ? $_POST["use_pay_method"] : ''; // 결제 방법
    $enc_info        = isset($_POST["enc_info"]) ? $_POST["enc_info"] : ''; // 암호화 정보
    $enc_data        = isset($_POST["enc_data"]) ? $_POST["enc_data"] : ''; // 암호화 데이터
	$rcvr_name		 = isset($_POST["rcvr_name"]) ? $_POST["rcvr_name"] : ''; // 수취인 이름
	$rcvr_tel1		 = isset($_POST["rcvr_tel1"]) ? $_POST["rcvr_tel1"] : ''; // 수취인 전화번호
	$rcvr_tel2		 = isset($_POST["rcvr_tel2"]) ? $_POST["rcvr_tel2"] : ''; // 수취인 휴대폰번호
	$rcvr_mail		 = isset($_POST["rcvr_mail"]) ? $_POST["rcvr_mail"] : ''; // 수취인 E-Mail
	$rcvr_zipx		 = isset($_POST["rcvr_zipx"]) ? $_POST["rcvr_zipx"] : ''; // 수취인 우편번호
	$rcvr_add1		 = isset($_POST["rcvr_add1"]) ? $_POST["rcvr_add1"] : ''; // 수취인 주소
	$rcvr_add2		 = isset($_POST["rcvr_add2"]) ? $_POST["rcvr_add2"] : ''; // 수취인 상세주소

    /* 주문폼에서 전송되는 정보 */
    $ipgm_date       = isset($_POST['ipgm_date']) ? $_POST['ipgm_date'] : ''; // 입금마감일
    $settle_method   = isset($_POST["settle_method"]) ? $_POST["settle_method"] : ''; // 결제방법
    $good_info       = isset($_POST["good_info"]) ? $_POST["good_info"] : ''; // 에스크로 상품정보
    $bask_cntx       = isset($_POST["bask_cntx"]) ? $_POST["bask_cntx"] : ''; // 장바구니 상품수
    $tablet_size     = isset($_POST["tablet_size"]) ? $_POST["tablet_size"] : ''; // 모바일기기 화면비율

    $comm_tax_mny    = isset($_POST["comm_tax_mny"]) ? $_POST["comm_tax_mny"] : ''; // 과세금액
    $comm_vat_mny    = isset($_POST["comm_vat_mny"]) ? $_POST["comm_vat_mny"] : ''; // 부가세
    $comm_free_mny   = isset($_POST["comm_free_mny"]) ? $_POST["comm_free_mny"] : ''; // 비과세금액

    $payco_direct    = isset($_POST["payco_direct"]) ? $_POST["payco_direct"] : ''; // PAYCO 결제창 호출
    $naverpay_direct = isset($_POST["naverpay_direct"]) ? $_POST["naverpay_direct"] : ''; // NAVERPAY 결제창 호출
    $kakaopay_direct = isset($_POST["kakaopay_direct"]) ? $_POST["kakaopay_direct"] : ''; // KAKAOPAY 결제창 호출

	/*
     * 기타 파라메터 추가 부분 - Start -
     */
    $param_opt_1     = isset($_POST["param_opt_1"]) ? $_POST["param_opt_1"] : ''; // 기타 파라메터 추가 부분
    $param_opt_2     = isset($_POST["param_opt_2"]) ? $_POST["param_opt_2"] : ''; // 기타 파라메터 추가 부분
    $param_opt_3     = isset($_POST["param_opt_3"]) ? $_POST["param_opt_3"] : ''; // 기타 파라메터 추가 부분
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
    
    // 에스크로 변수 ( 간편결제의 경우 N 으로 변경 )
    $escw_used = 'Y';

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
        case '간편결제':
            $pay_method = 'CARD';
            $ActionResult = 'card';
            $escw_used = 'N';
            break;
        default:
            $pay_method = '';
            $ActionResult = '';
            break;
    }

    if(get_session('ss_personalpay_id') && get_session('ss_personalpay_hash')) {
        $js_return_url = G5_SHOP_URL.'/personalpayform.php?pp_id='.get_session('ss_personalpay_id');
    } else {
        $js_return_url = G5_SHOP_URL.'/orderform.php';
        if(get_session('ss_direct'))
            $js_return_url .= '?sw_direct=1';
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
<title>스마트폰 웹 결제창</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-Control" content="No-Cache">
<meta http-equiv="Pragma" content="No-Cache">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10">
<meta name="HandheldFriendly" content="true">
<meta name="format-detection" content="telephone=no">

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

        // 네이버페이면 반드시 페이지전환 방식이어야 하며, 그 외에는 iframe 방식으로 한다.
        if(typeof v_frm.naverpay_direct !== "undefined" && v_frm.naverpay_direct.value == "Y") {
            v_frm.target = "";
        }

        v_frm.action = PayUrl;

		if(v_frm.Ret_URL.value == "")
		{
			/* Ret_URL값은 현 페이지의 URL 입니다. */
			alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");
            document.location.href = "<?php echo $js_return_url; ?>";
			return false;
		}

        v_frm.submit();
    }


	/* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청*/
    function chk_pay()
    {
        /*kcp 결제서버에서 가맹점 주문페이지로 폼값을 보내기위한 설정(변경불가)*/
        self.name = "tar_opener";

        var sm_form = document.sm_form;

        if (sm_form.res_cd.value == "3001" )
        {
            alert("사용자가 취소하였습니다.");
            document.location.href = "<?php echo $js_return_url; ?>";
            return false;
        }
        else if (sm_form.res_cd.value == "3000" )
        {
            alert("30만원 이상 결제 할수 없습니다.");
            document.location.href = "<?php echo $js_return_url; ?>";
            return false;
        }

        if (sm_form.enc_data.value != "" && sm_form.enc_info.value != "" && sm_form.tran_cd.value !="" )
        {
            document.getElementById("pay_fail").style.display = "none";
            document.getElementById("show_progress").style.display = "block";
            setTimeout( function() {
                document.forderform.submit();
            }, 300);
        } else {
            kcp_AJAX();
        }
    }

</script>
</head>
<body onload="chk_pay();">

<div id="content">

<?php
if($enc_data != '' && $enc_info != '' && $tran_cd != '') {
    // 제외할 필드
    $exclude = array('req_tx', 'res_cd', 'tran_cd', 'ordr_idxx', 'good_mny', 'good_name', 'buyr_name', 'buyr_tel1', 'buyr_tel2', 'buyr_mail', 'enc_info', 'enc_data', 'use_pay_method', 'rcvr_name', 'rcvr_tel1', 'rcvr_tel2', 'rcvr_mail', 'rcvr_zipx', 'rcvr_add1', 'rcvr_add2', 'param_opt_1', 'param_opt_2', 'param_opt_3');

    $sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$ordr_idxx' ";
    $row = sql_fetch($sql);

    $data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

    if(isset($data['pp_id']) && $data['pp_id']) {
        $order_action_url = G5_HTTPS_MSHOP_URL.'/personalpayformupdate.php';
    } else {
        $order_action_url = G5_HTTPS_MSHOP_URL.'/orderformupdate.php';
    }

    echo '<form name="forderform" method="post" action="'.$order_action_url.'" autocomplete="off">'.PHP_EOL;

    echo make_order_field($data, $exclude);

    foreach($_POST as $key=>$value) {
        echo '<input type="hidden" name="'.$key.'" value="'.$value.'">'.PHP_EOL;
    }

    echo '</form>'.PHP_EOL;
}
?>

<form name="sm_form" method="POST" accept-charset="euc-kr">

<input type="hidden" name="good_name" value="<?php echo $good_name; ?>">
<input type="hidden" name="good_mny"  value="<?php echo $good_mny; ?>" >
<input type="hidden" name='buyr_name' value="<?php echo $buyr_name; ?>">
<input type="hidden" name="buyr_tel1" value="<?php echo $buyr_tel1; ?>">
<input type="hidden" name="buyr_tel2" value="<?php echo $buyr_tel2; ?>">
<input type="hidden" name="buyr_mail" value="<?php echo $buyr_mail; ?>">
<?php
// 가상계좌 입금 마감일을 설정하려면 아래 주석을 풀어서 사용해 주세요.
//$ipgm_date = date("Ymd", (G5_SERVER_TIME + 86400 * 5));
//echo '<input type="hidden" name="ipgm_date" value="'.$ipgm_date.'">';
?>

<?php if($payco_direct){ ?>
<input type="hidden" name="payco_direct"   value="<?php echo $payco_direct; ?>">      <!-- PAYCO 결제창 호출 -->
<?php } ?>
<?php if($naverpay_direct){ ?>
<input type="hidden" name="naverpay_direct"   value="<?php echo $naverpay_direct; ?>">      <!-- 네이버페이 결제창 호출 -->
    <?php if(isset($default['de_easy_pay_services']) && in_array('used_nhnkcp_naverpay_point', explode(',', $default['de_easy_pay_services'])) ){     // 네이버페이 포인트 결제 옵션 ?>
    <input type="hidden" name="naverpay_point_direct" value="Y">    <!-- 네이버페이 포인트 결제를 하려면 naverpay_point_direct 를 Y  -->
    <?php } ?>
<?php } ?>
<?php if($kakaopay_direct){ ?>
<input type="hidden" name="kakaopay_direct"   value="<?php echo $kakaopay_direct; ?>">      <!-- 카카오페이 결제창 호출 -->
<?php } ?>

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
<input type="hidden" name="escw_used"  value="<?php echo $escw_used; ?>">
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
<input type="hidden" name="param_opt_1"	 value="<?php echo get_text($param_opt_1); ?>"/>
<input type="hidden" name="param_opt_2"	 value="<?php echo get_text($param_opt_2); ?>"/>
<input type="hidden" name="param_opt_3"	 value="<?php echo get_text($param_opt_3); ?>"/>
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

    <div id="pay_fail">
        <p>결제가 실패한 경우 아래 돌아가기 버튼을 클릭해주세요.</p>
        <a href="<?php echo $js_return_url; ?>">돌아가기</a>
    </div>
    <div id="show_progress" style="display:none;">
        <span style="display:block; text-align:center;margin-top:120px"><img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt="" ></span>
        <span style="display:block; text-align:center;margin-top:10px; font-size:14px">주문완료 중입니다. 잠시만 기다려 주십시오.</span>
    </div>
</div>

<!-- 스마트폰에서 KCP 결제창을 레이어 형태로 구현-->
<div id="layer_receipt" style="position:absolute; left:1px; top:1px; width:100%;height:100%; z-index:1; display:none;">
    <table width="100%" height="100%" border="-" cellspacing="0" cellpadding="0" style="text-align:center">
        <tr height="100%" width="100%">
            <td>
                <iframe name="frm_receipt" frameborder="0" border="0" width="100%" height="100%" scrolling="auto"></iframe>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
