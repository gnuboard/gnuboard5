<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//===================================================================================
// g4s 디렉토리 경로
//-----------------------------------------------------------------------------------
$g4['bbs_url']        = $g4['url'].'/'.$g4['bbs'];
$g4['admin_url']      = $g4['url'].'/'.$g4['admin'];

$g4['https_bbs_url']  = $g4['bbs_url'];
if ($g4['https_url']) {
    $g4['https_bbs_url'] = $g4['https_url'].'/'.$g4['bbs'];
}

$g4['extend_dir']   = 'extend';
$g4['extend_path']  = $g4['path'].'/'.$g4['extend_dir'];

$g4['data_dir']     = 'data';
$g4['data_url']     = $g4['url'].'/'.$g4['data_dir'];
$g4['data_path']    = $g4['path'].'/'.$g4['data_dir'];

$g4['cache_dir']    = 'cache';
$g4['cache_path']   = $g4['data_path'].'/'.$g4['cache_dir'];

$g4['session_dir']    = 'session';
$g4['session_path']   = $g4['data_path'].'/'.$g4['session_dir'];

$g4['cache_latest_dir']     = $g4['cache_dir'].'/latest';
$g4['cache_member_dir']     = $g4['cache_dir'].'/member';
$g4['cache_captcha_dir']    = $g4['cache_dir'].'/captcha';

$g4['cache_latest_path']    = $g4['data_path'].'/'.$g4['cache_latest_dir'];
$g4['cache_member_path']    = $g4['data_path'].'/'.$g4['cache_member_dir'];
$g4['cache_captcha_path']   = $g4['data_path'].'/'.$g4['cache_captcha_dir'];

// g4s 기본 DHTML EDITOR
if (!defined('G4_EDITOR')) define('G4_EDITOR', 0);
if (G4_EDITOR) {
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
if (!defined('G4_CAPTCHA')) define('G4_CAPTCHA', 0);
if (G4_CAPTCHA) {
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