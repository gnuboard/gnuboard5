<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// _EDITOR_ 의 값에 상관없이 선언 되었는지의 여부만 따짐
// if (defined('_EDITOR_')) true;
define('_EDITOR_', 1);

if (defined('_EDITOR_')) {
    $ckeditor = new stdClass;
    $ckeditor->url  = $g4['url']."/extend/ckeditor";
    $ckeditor->path = $g4['path']."/extend/ckeditor";
    $ckeditor->data = "data/editor";

    include_once($ckeditor->path."/ckeditor.lib.php");

    $g4['js_code'][] = "var g4_ckeditor_path = \"{$ckeditor->path}\";";
    $g4['js_file'][] = $ckeditor->url."/ckeditor.js";
    $g4['js_file'][] = $ckeditor->url."/config.js";
}
?>