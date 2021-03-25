<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 캡챠 HTML 코드 출력
function captcha_html($class="captcha")
{

    global $config;

    /*
    #hl=ko 표시는 언어지정가능
    */
    $html = '<fieldset id="captcha" class="captcha recaptcha">';
    $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=ko"></script>';
    $html .= '<script src="'.G5_CAPTCHA_URL.'/recaptcha.js"></script>';
    $html .= '<div class="g-recaptcha" data-sitekey="'.$config['cf_recaptcha_site_key'].'"></div>';
    $html .= '</fieldset>';

	return $html;
}

// 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함
function chk_captcha_js()
{
	return "if (!chk_captcha()) return false;\n";
}

function chk_captcha(){

    global $config;

    $resp = null;

    if ( isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ) {

        $reCaptcha = new ReCaptcha_GNU( $config['cf_recaptcha_secret_key'] );

        $resp = $reCaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
    }

    if( ! $resp ){
        return false;
    }

    if ($resp != null && $resp->success) {
        return true;
    }

    return false;
}