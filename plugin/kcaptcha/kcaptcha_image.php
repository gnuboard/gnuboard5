<?php
include_once("_common.php");
include_once('captcha.lib.php');

$captcha = new KCAPTCHA();
$ss_captcha_key = get_session("ss_captcha_key");
if( $ss_captcha_key && !preg_match('/^[0-9]/', $ss_captcha_key) && function_exists('get_string_decrypt') ){
    $ip = md5(sha1($_SERVER['REMOTE_ADDR']));
    $ss_captcha_key = str_replace($ip, '', get_string_decrypt($ss_captcha_key));
}
$captcha->setKeyString($ss_captcha_key);
$captcha->getKeyString();
$captcha->image();