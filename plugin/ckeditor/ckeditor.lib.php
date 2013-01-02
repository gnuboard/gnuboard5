<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_textarea($id, $content="", $class="") 
{
    global $g4;
    $upload_url = $g4['path']."/plugin/ckeditor/upload.php?type=Images";
    $str  = "<textarea id=\"$id\" name=\"$id\" class=\"ckeditor $class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
    //$str .= "<script> CKEDITOR.replace('$id',{ filebrowserUploadUrl : '$upload_url'}); </script>\n";
    return $str;
}

function chk_editor($id, $textarea_name="내용")
{
    $str  = "var {$id}_data = CKEDITOR.instances.{$id}.getData();\n";
    $str .= "    ";
    $str .= "if (!{$id}_data) { alert(\"$textarea_name 입력해 주십시오.\"); return false; }\n";
    return $str;
}
?>