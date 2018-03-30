function chk_captcha()
{
	if ( ! jQuery('#g-recaptcha-response').val()) {
		alert("자동등록방지를 반드시 체크해 주세요.");
		return false;
	}

	return true;
}