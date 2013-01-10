<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (defined('_CAPTCHA_')) {
    $captcha = (object)array(
        'lib' => $g4['path']."/plugin/captcha/captcha.lib.php",
        'js'  => $g4['path']."/plugin/captcha/captcha.js"
    );

    include_once($captcha->lib);
} else {
    unset($_SESSION['ss_captcha_use']);
}
?>