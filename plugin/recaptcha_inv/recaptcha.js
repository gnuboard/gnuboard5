function chk_captcha()
{
	if ( ! jQuery('#g-recaptcha-response').val()) {
		grecaptcha.execute();
		return false;
	}

	return true;
}

function recaptcha_validate(token) {
    var $form = jQuery("#g-recaptcha-response").closest("form"),
        form_id = $form.attr("id");


    if( $form.length ){
        $form.submit();
    }

}
