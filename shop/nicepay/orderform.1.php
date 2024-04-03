<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// kcp 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
?>
<!-- PC payment window only (not required for mobile payment window)-->
<script src="https://web.nicepay.co.kr/v3/webstd/js/nicepay-3.0.js" type="text/javascript"></script>
<script type="text/javascript">
//It is executed when call payment window.
function nicepayStart(){
	if(checkPlatform(window.navigator.userAgent) == "mobile"){
		document.forderform.action = "https://web.nicepay.co.kr/v3/v3Payment.jsp";
		document.forderform.acceptCharset="euc-kr";
		document.forderform.submit();
	}else{
		goPay(document.forderform);
	}
}

//[PC Only]When pc payment window is closed, nicepay-3.0.js call back nicepaySubmit() function <<'nicepaySubmit()' DO NOT CHANGE>>
function nicepaySubmit(){
	document.forderform.submit();
}

//[PC Only]payment window close function <<'nicepayClose()' DO NOT CHANGE>>
function nicepayClose(){
	// alert("payment window is closed");
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
<?php }