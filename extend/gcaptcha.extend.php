<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// _CAPTCHA_ 의 값에 상관없이 선언 되었는지의 여부만 따짐
// if (defined('_CAPTCHA_')) true;
define('_CAPTCHA_', 1);

if (defined('_CAPTCHA_')) {
    $gcaptcha = new stdClass;
    $gcaptcha->url   = $g4['url']."/extend/gcaptcha";
    $gcaptcha->path  = $g4['path']."/extend/gcaptcha";
    $gcaptcha->fonts = $gcaptcha->path."/fonts";
    $gcaptcha->wavs  = $gcaptcha->path."/wavs";

    include_once($gcaptcha->path."/gcaptcha.lib.php");

    $g4['js_code'][] = "var g4_gcaptcha_path = \"{$gcaptcha->path}\";";
    $g4['js_file'][] = $gcaptcha->url."/gcaptcha.js";
}
?>