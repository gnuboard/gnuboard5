<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor($id, $content="", $class="") 
{
    global $g4;
    $str  = "<textarea id=\"$id\" name=\"$id\" class=\"ckeditor $class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
    //if (_EDITOR_) $str .= "<script>CKEDITOR.replace('$id',{height:'500px'});</script>\n";
    return $str;
}

// textarea 로 값을 넘김
function editor_getdata($id)
{
    return "var {$id}_data = CKEDITOR.instances.{$id}.getData();\n";
}

// textarea 의 값이 비어 있는지 검사
function editor_empty($id, $textarea_name="내용을")
{
    return "if (!{$id}_data) { alert(\"$textarea_name 입력해 주십시오.\"); CKEDITOR.instances.{$id}.focus(); return false; }\n";
}
?>