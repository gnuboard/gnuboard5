<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_textarea($id, $content="", $class="") 
{
    global $g4;
    //$upload_url = $g4['path']."/plugin/ckeditor/upload.php?type=Images";
    $str  = "<textarea id=\"$id\" name=\"$id\" class=\"ckeditor $class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
    //$str .= "<script> CKEDITOR.replace('$id',{ filebrowserUploadUrl : '$upload_url'}); </script>\n";
    return $str;
}

// textarea 로 값을 넘김
function editor_getdata($id)
{
    if (defined('_EDITOR_'))
        return "var {$id}_data = CKEDITOR.instances.{$id}.getData();\n";
    else 
        return "";
}

// textarea 의 값이 비어 있는지 검사
function editor_empty($id, $textarea_name="내용을")
{
    if (defined('_EDITOR_'))
        return "if (!{$id}_data) { alert(\"$textarea_name 입력해 주십시오.\"); return false; }\n";
    else 
        return "";
}
?>