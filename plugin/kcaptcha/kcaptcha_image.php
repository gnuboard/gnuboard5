<?php
include_once("_common.php");
include_once('captcha.lib.php');

$captcha = new KCAPTCHA();
$ss_captcha_key = get_session("ss_captcha_key");
$ss_captcha_key_decrypt = '';
if( $ss_captcha_key && !preg_match('/^[0-9]/', $ss_captcha_key) && function_exists('get_string_decrypt') ){
    $ip = md5(sha1($_SERVER['REMOTE_ADDR']));
    $ss_captcha_key_decrypt = str_replace($ip, '', get_string_decrypt($ss_captcha_key));
}
# php 5.2 또는 5.3 버전에서 포인터처럼 해당 세션값이 변경되는 버그가 있어서 아래와 같이 조치함
if(! $ss_captcha_key_decrypt) $ss_captcha_key_decrypt = $ss_captcha_key;
$captcha->setKeyString($ss_captcha_key_decrypt);
$captcha->getKeyString();
$captcha->image();