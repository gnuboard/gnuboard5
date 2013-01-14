<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//===================================================================================
// g4s 디렉토리 경로
//-----------------------------------------------------------------------------------
$g4['bbs_url']        = $g4['url'].'/'.$g4['bbs'];
$g4['admin_url']      = $g4['url'].'/'.$g4['admin'];

$g4['data_dir']       = 'data';
$g4['data_url']       = $g4['url'].'/'.$g4['data_dir'];
$g4['data_path']      = $g4['path'].'/'.$g4['data_dir'];

$g4['cache_dir']      = 'cache';
$g4['cache_path']     = $g4['data_path'].'/'.$g4['cache_dir'];

$g4['captcha_dir']    = 'captcha';

// g4s 기본 DHTML EDITOR
if (!defined('_EDITOR_')) define('_EDITOR_', 0);
if (_EDITOR_) {
    $g4['ckeditor_dir']  = 'ckeditor';
    $g4['ckeditor_url']  = $g4['bbs_url'].'/'.$g4['ckeditor_dir'];
    $g4['ckeditor_path'] = $g4['bbs_path'].'/'.$g4['ckeditor_dir'];
    $g4['ckeditor_data'] = $g4['bbs_path'].'/'.$g4['data_dir'].'/editor';

    include_once($g4['ckeditor_path']."/ckeditor.lib.php");

    $g4['js_code'][] = "var g4_ckeditor_path = \"{$g4['ckeditor_path']}\";";
    $g4['js_file'][] = $g4['ckeditor_url']."/ckeditor.js";
    $g4['js_file'][] = $g4['ckeditor_url']."/config.js";
}

// g4s 기본 CAPTCHA
if (!defined('_CAPTCHA_')) define('_CAPTCHA_', 0);
if (_CAPTCHA_) {
    $g4['gcaptcha_dir']   = 'gcaptcha';
    $g4['gcaptcha_url']   = $g4['bbs_url'].'/'.$g4['gcaptcha_dir'];
    $g4['gcaptcha_path']  = $g4['bbs_path'].'/'.$g4['gcaptcha_dir'];
    $g4['gcaptcha_fonts'] = $g4['gcaptcha_path'].'/fonts';
    $g4['gcaptcha_wavs']  = $g4['gcaptcha_path'].'/wavs';

    include_once($g4['gcaptcha_path']."/gcaptcha.lib.php");

    $g4['js_code'][] = "var g4_gcaptcha_path = \"{$g4['gcaptcha_path']}\";";
    $g4['js_file'][] = $g4['gcaptcha_url']."/gcaptcha.js";

}
//===================================================================================
?>