<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$editor = (object)array(
    'lib'       => $g4['path']."/plugin/ckeditor/ckeditor.lib.php",
    'js'        => $g4['path']."/plugin/ckeditor/ckeditor.js",
    'config_js' => $g4['path']."/plugin/ckeditor/config.js",
    'data'      => "data/editor"
);

include_once($editor->lib);
?>