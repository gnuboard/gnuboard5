<?php
include_once("./_common.php");

define('MAX_CAPTCHA_COUNT', 3);
$captcha_cnt = (int)$_SESSION['ss_captcha_cnt'];

if ($captcha_cnt >= MAX_CAPTCHA_COUNT) {
    $_SESSION['ss_captcha_key'] = '';
    echo false;
} else {
    if ($_POST['captcha_key'] == $_SESSION['ss_captcha_key']) {
        echo true;
    } else {
        $_SESSION['ss_captcha_cnt'] = $captcha_cnt + 1;
        echo false;
    }
}
?>