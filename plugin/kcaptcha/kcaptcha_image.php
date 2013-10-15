<?php
include_once("_common.php");
//error_reporting (E_ALL);
include('captcha.lib.php');

//session_start();
$captcha = new KCAPTCHA();
$captcha->setKeyString(get_session("ss_captcha_key"));
$captcha->getKeyString();
$captcha->image();
?>