<?php
include_once("_common.php");
include_once('captcha.lib.php');

$captcha = new KCAPTCHA();
$captcha->setKeyString(get_session("ss_captcha_key"));
$captcha->getKeyString();
$captcha->image();
?>