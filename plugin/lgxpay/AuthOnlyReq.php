<?php
include_once('./_common.php');

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'hp');

/*
 * [본인확인 요청페이지]
 *
 * 샘플페이지에서는 기본 파라미터만 예시되어 있으며, 별도로 필요하신 파라미터는 연동메뉴얼을 참고하시어 추가 하시기 바랍니다.
 */

//LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
if($config['cf_cert_use'] == 2)
    $CST_PLATFORM = 'service';
else
    $CST_PLATFORM = 'test';
$CST_MID                    = 'si_'.$config['cf_lg_mid'];       // 상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
$LGD_MID                    = (('test' == $CST_PLATFORM) ? 't':'').$CST_MID;  //상점아이디(자동생성)
$LGD_BUYER                  = '홍길동';                         // 성명 (보안을 위해 DB난 세션에서 가져오세요)
$LGD_BUYERSSN               = '000000';                  // 주민등록번호 (보안을 위해 DB나 세션에서 가져오세요)
                                                                // 휴대폰 본인인증을 사용할 경우 주민번호는 '0' 13자리를 넘기세요. 예)0000000000000
                                                                // 기타 인증도 사용할 경우 실 주민등록번호 (보안을 위해 DB나 세션에 저장처리 권장)
$LGD_MOBILE_SUBAUTH_SITECD  = '123456789abc';                               // 신용평가사에서 부여받은 회원사 고유 코드
                                                                //  (CI값만 필요한 경우 옵션, DI값도 필요한 경우 필수)
$LGD_TIMESTAMP              = date('YmdHis');                   // 타임스탬프 (YYYYMMDDhhmmss)
$LGD_CUSTOM_SKIN            = 'red';                            // 상점정의 인증창 스킨 (red, blue, cyan, green, yellow)

/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - BEGIN
 *
 * MD5 해쉬암호화는 거래 위변조를 막기위한 방법입니다.
 *************************************************
 *
 * 해쉬 암호화 적용( LGD_MID + LGD_BUYERSSN + LGD_TIMESTAMP + LGD_MERTKEY )
 * LGD_MID          : 상점아이디
 * LGD_BUYERSSN     : 주민등록번호
 * LGD_TIMESTAMP    : 타임스탬프
 * LGD_MERTKEY      : 상점MertKey (mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
 *
 * MD5 해쉬데이터 암호화 검증을 위해
 * LG유플러스에서 발급한 상점키(MertKey)를 환경설정 파일(lgdacom/conf/mall.conf)에 반드시 입력하여 주시기 바랍니다.
 */

$LGD_MERTKEY    = $config['cf_lg_mert_key'];
$LGD_HASHDATA   = md5($LGD_MID.$LGD_BUYERSSN.$LGD_TIMESTAMP.$LGD_MERTKEY);
$LGD_RETURNURL  = G5_PLUGIN_URL.'/lgxpay/returnurl.php';
if( G5_IS_MOBILE ){
    $LGD_WINDOW_TYPE = 'submit';
} else {
    $LGD_WINDOW_TYPE = 'iframe';
}

$LGD_NAMECHECKYN = 'N';
$LGD_HOLDCHECKYN = 'Y';
$LGD_CUSTOM_USABLEPAY = 'ASC007';

$payReqMap = array();

$payReqMap['CST_PLATFORM']              = $CST_PLATFORM;           				// 테스트, 서비스 구분
$payReqMap['CST_MID']                   = $CST_MID;                				// 상점아이디
$payReqMap['LGD_MID']                   = $LGD_MID;                				// 상점아이디
$payReqMap['LGD_HASHDATA'] 				= $LGD_HASHDATA;      	           		// MD5 해쉬암호값
$payReqMap['LGD_BUYER']              	= $LGD_BUYER;							// 요청자 성명
$payReqMap['LGD_BUYERSSN']              = $LGD_BUYERSSN;           				// 요청자 생년월일 / 사업자번호

$payReqMap['LGD_NAMECHECKYN']           = $LGD_NAMECHECKYN;           			// 계좌실명확인여부
$payReqMap['LGD_HOLDCHECKYN']           = $LGD_HOLDCHECKYN;           			// 휴대폰본인확인 SMS발송 여부
$payReqMap['LGD_MOBILE_SUBAUTH_SITECD'] = $LGD_MOBILE_SUBAUTH_SITECD;           // 신용평가사에서 부여받은 회원사 고유 코드

$payReqMap['LGD_CUSTOM_SKIN'] 			= $LGD_CUSTOM_SKIN;                		// 본인확인창 SKIN
$payReqMap['LGD_TIMESTAMP'] 			= $LGD_TIMESTAMP;                  		// 타임스탬프
$payReqMap['LGD_CUSTOM_USABLEPAY']      = $LGD_CUSTOM_USABLEPAY;        		// [반드시 설정]상점정의 이용가능 인증수단으로 한 개의 값만 설정 (예:"ASC007")
$payReqMap['LGD_WINDOW_TYPE']           = $LGD_WINDOW_TYPE;        				// 호출방식 (수정불가)
$payReqMap['LGD_RETURNURL'] 			= $LGD_RETURNURL;      			   		// 응답수신페이지
$payReqMap['LGD_VERSION'] 				= "PHP_Non-ActiveX_AuthOnly";			// 사용타입 정보(수정 및 삭제 금지): 이 정보를 근거로 어떤 서비스를 사용하는지 판단할 수 있습니다.
 

/*Return URL에서 인증 결과 수신 시 셋팅될 파라미터 입니다.*/
$payReqMap['LGD_RESPCODE'] 				= "";
$payReqMap['LGD_RESPMSG'] 				= "";
$payReqMap['LGD_AUTHONLYKEY'] 			= "";
$payReqMap['LGD_PAYTYPE'] 				= "";

$_SESSION['lgd_certify'] = $payReqMap;

/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - END
 *************************************************
 */
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>LG유플러스 전자결제 본인확인서비스</title>
<!-- 고객사 사이트가 https인 경우는 아래 http://을 https:// 으로 변경하시면 됩니다. -->
<link rel="stylesheet" href="<?php echo G5_CSS_URL;?>/default.css">
<script language="javascript" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https' : 'http'; ?>://xpay.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>

<script type="text/javascript">

	/*
	* 수정불가.
	*/
	var LGD_window_type = "<?php echo $LGD_WINDOW_TYPE;?>";
	var lgd_form = "LGD_PAYINFO";
	/*
	* 수정불가.
	*/
	function launchCrossPlatform(){
		
        <?php if( G5_IS_MOBILE ){   //모바일이면 ?>
            lgdwin = open_paymentwindow(document.getElementById(lgd_form), '<?php echo $CST_PLATFORM ?>', LGD_window_type);
        <?php } else {  //PC 이면 ?>
		    lgdwin = openAuthonly( document.getElementById(lgd_form), "<?php echo $CST_PLATFORM; ?>", LGD_window_type, null );
        <?php } ?>

	}
	
	/*
	* FORM 명만  수정 가능
	*/
	function getFormObject() {
	        return document.getElementById(lgd_form);
	}
	
	function  payment_return() {
		
		var fDoc = lgdwin.contentWindow || lgdwin.contentDocument;
	
		if (fDoc.document.getElementById('LGD_RESPCODE').value == "0000") {
			document.getElementById("LGD_AUTHONLYKEY").value = fDoc.document.getElementById('LGD_AUTHONLYKEY').value;
			document.getElementById("LGD_PAYTYPE").value = fDoc.document.getElementById('LGD_PAYTYPE').value;
			
			document.getElementById(lgd_form).target = "_self";
            document.getElementById("LGD_PAYINFO").action = "AuthOnlyRes.php";
			document.getElementById(lgd_form).submit();
		} else {
			alert("LGD_RESPCODE (결과코드2) : " + fDoc.document.getElementById('LGD_RESPCODE').value + "\n" + "LGD_RESPMSG (결과메시지): " + fDoc.document.getElementById('LGD_RESPMSG').value);
			closeIframe();
            window.close();
		}//end if
	}//end payment_return
</script>

<style>
#uplus_win {}
.up_cmt {text-align:center; font-size:14px;}
.up_cmt img {display:block; margin:0 auto 20px}
.up_info {background:#eee;padding:13px;margin:28px 25px 20px;}
.up_info a {float:left; margin-right:10px}
.up_info p {padding:10px 0 0; line-height:18px;}
.up_info:after {clear:both; display:block; content:"";}
.win_btn {clear:both;}
</style>
</head>
<body>

<form method="post" name ="LGD_PAYINFO" id="LGD_PAYINFO" action="<?php echo G5_LGXPAY_URL; ?>/AuthOnlyRes.php">
<input type="hidden" name="LGD_ENCODING" value="UTF-8"/>
<?php
foreach ($payReqMap as $key => $value) {
    echo "<input type='hidden' name='$key' id='$key' value='$value'/>".PHP_EOL;
}
?>

</form>

<div id="uplus_win" class="new_win mbskin">
    <h1 id="win_title">휴대폰 본인확인</h1>
    <p class="up_cmt"><img src="<?php echo G5_LGXPAY_URL; ?>/img/upluslogo.jpg" alt="">LG유플러스에 휴대폰 본인확인 요청 중입니다.</p>
    <div class="up_info">
        <a href="http://pgweb.uplus.co.kr:8080/pg/wmp/Home2009/skill/payment_error_center01.jsp" target="_blank"><img src="<?php echo G5_LGXPAY_URL; ?>/img/btn_gouplus.jpg" alt="U+ 오류 해결방법 바로가기"></a>
        <p>본인확인이 진행되지 않는다면<br /> 왼쪽의 링크로 이동하여보세요.</p>
        <!--[If lte IE 7]><span style="clear:both; display:block; content:'';"></span><![endif]-->
    </div>
    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>
<script>
setTimeout("launchCrossPlatform();", 1);
</script>
</body>
</html>