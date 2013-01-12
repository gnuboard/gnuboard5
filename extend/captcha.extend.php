<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (defined('_CAPTCHA_')) {
    $captcha = (object)Array(
        'lib'   => $g4['path']."/plugin/captcha/captcha.lib.php",
        'js'    => $g4['path']."/plugin/captcha/captcha.js",
        'fonts' => $g4['path']."/plugin/captcha/fonts"
    );

    include_once($captcha->lib);
    $g4['js_file'][] = $captcha->js;

    $captcha_obj = new captcha();
    $captcha_obj->run();
} else {
    unset($_SESSION['ss_captcha_use']);
}
?>