<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- PC payment window only (not required for mobile payment window)-->
<script src="https://web.nicepay.co.kr/v3/webstd/js/nicepay-3.0.js" type="text/javascript"></script>
<script type="text/javascript">
//It is executed when call payment window.
function nicepayStart(f){
	if(checkPlatform(window.navigator.userAgent) == "mobile"){
		document.sm_form.action = "https://web.nicepay.co.kr/v3/v3Payment.jsp";
		document.sm_form.acceptCharset="euc-kr";
		document.sm_form.submit();
	}else{
		goPay(document.sm_form);
	}
}

//[PC Only]When pc payment window is closed, nicepay-3.0.js call back nicepaySubmit() function <<'nicepaySubmit()' DO NOT CHANGE>>
function nicepaySubmit(){
	document.sm_form.submit();
}

//[PC Only]payment window close function <<'nicepayClose()' DO NOT CHANGE>>
function nicepayClose(){
	alert("payment window is closed");
}

//pc, mobile chack script (sample code)
function checkPlatform(ua) {
	if(ua === undefined) {
		ua = window.navigator.userAgent;
	}
	
	ua = ua.toLowerCase();
	var platform = {};
	var matched = {};
	var userPlatform = "pc";
	var platform_match = /(ipad)/.exec(ua) || /(ipod)/.exec(ua) 
		|| /(windows phone)/.exec(ua) || /(iphone)/.exec(ua) 
		|| /(kindle)/.exec(ua) || /(silk)/.exec(ua) || /(android)/.exec(ua) 
		|| /(win)/.exec(ua) || /(mac)/.exec(ua) || /(linux)/.exec(ua)
		|| /(cros)/.exec(ua) || /(playbook)/.exec(ua)
		|| /(bb)/.exec(ua) || /(blackberry)/.exec(ua)
		|| [];
	
	matched.platform = platform_match[0] || "";
	
	if(matched.platform) {
		platform[matched.platform] = true;
	}
	
	if(platform.android || platform.bb || platform.blackberry
			|| platform.ipad || platform.iphone 
			|| platform.ipod || platform.kindle 
			|| platform.playbook || platform.silk
			|| platform["windows phone"]) {
		userPlatform = "mobile";
	}
	
	if(platform.cros || platform.mac || platform.linux || platform.win) {
		userPlatform = "pc";
	}
	
	return userPlatform;
}

function nicepay_create_signdata(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/shop/nicepay/createsigndata.php",
        type: "POST",
        data: {
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.EdiDate.value = data.ediDate;
                frm.SignData.value = data.SignData;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}
</script>

<form name="sm_form" method="post" action="" accept-charset="euc-kr">
<input type="hidden" name="PayMethod" value="CARD">
<input type="hidden" name="GoodsName" value="<?php echo get_text($goods); ?>">
<input type="hidden" name="Amt" value="<?php echo $tot_price; ?>">
<input type="hidden" name="MID" value="<?php echo $default['de_nicepay_mid']; ?>">
<input type="hidden" name="Moid" value="<?php echo $od_id; ?>">
<input type="hidden" name="BuyerName" value="">
<input type="hidden" name="BuyerEmail" value="">
<input type="hidden" name="BuyerTel" value="">
<input type="hidden" name="ReturnURL" value="<?php echo $nicepay_returnURL; ?>">
<input type="hidden" name="VbankExpDate" value="">
<input type="hidden" name="NpLang" value="KO"> <!-- EN:English, CN:Chinese, KO:Korean -->
<input type="hidden" name="GoodsCl" value="1">	<!-- products(1), contents(0)) -->
<input type="hidden" name="TransType" value="<?php echo $default['de_escrow_use'] ? '1' : '0';?>">	<!-- USE escrow false(0)/true(1) --> 
<input type="hidden" name="CharSet" value="utf-8">	<!-- Return CharSet -->
<input type="hidden" name="ReqReserved" value="">	<!-- mall custom field -->
<input type="hidden" name="EdiDate" value=""> <!-- YYYYMMDDHHMISS -->
<input type="hidden" name="SignData" value="">	<!-- EncryptData -->

<input type="hidden" name="DirectShowOpt" value=""> 
<input type="hidden" name="SelectCardCode" value=""> <!-- 카드사 노출 제한, 카드코드 값(ex 비씨:01, 삼성:04) -->
<input type="hidden" name="NicepayReserved" value="">   <!-- 간편결제 (카카오페이에 사용됨) -->
<input type="hidden" name="DirectEasyPay"> <!-- 간편결제 요청 값 (네이버페이에 사용됨) -->
<input type="hidden" name="EasyPayMethod">  <!-- 간편결제 (네이버페이에 사용됨) -->
<input type="hidden" name="EasyPayCardCode"> <!-- 간편결제 카드 코드 -->
<input type="hidden" name="EasyPayQuota"> <!-- 간편결제 할부개월 (3개월일 경우 03 으로 설정) -->
<input type="hidden" name="MultiEasyPayQuota"> <!-- 간편결제 할부개월 다중 설정 옵션 PAYCO와 네이버페이만 가능 -->

<input type="hidden" name="good_mny"     value="<?php echo $tot_price; ?>" >

<?php if ($default['de_tax_flag_use']) { ?>
<!-- 필드명:SupplyAmt / 사이즈:12 / 설명:공급가 액 -->
<input type="hidden" name="SupplyAmt" value="<?php echo $comm_tax_mny; ?>"> <!-- 과세금액    -->
<!-- 필드명:GoodsVat / 사이즈:12 / 설명:부가가 치세 -->
<input type="hidden" name="GoodsVat" value="<?php echo $comm_vat_mny; ?>">  <!-- 부가세	    -->
<!-- 필드명:ServiceAmt / 사이즈:12 / 설명:봉사료 -->
<input type="hidden" name="ServiceAmt" value="0">
<!-- 필드명:TaxFreeAmt / 사이즈:12 / 설명:면세 금액 -->
<input type="hidden" name="TaxFreeAmt" value="<?php echo $comm_free_mny; ?>">

<input type="hidden" name="comm_tax_mny"	  value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
<input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php } ?>
</form>