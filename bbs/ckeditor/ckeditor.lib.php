<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $class="") 
{
    if ( (isset($GLOBALS['is_dhtml_editor']) && $GLOBALS['is_dhtml_editor']) || !isset($GLOBALS['is_dhtml_editor']) ) {
        $str  = "<textarea id=\"$id\" name=\"$id\" class=\"ckeditor $class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
        //if (_EDITOR_) $str .= "<script>CKEDITOR.replace('$id',{height:'500px'});</script>\n";
    } else {
        $str  = "<textarea id=\"$id\" name=\"$id\" class=\"$class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
    }
    return $str;
}

// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id) 
{
    if ( (isset($GLOBALS['is_dhtml_editor']) && $GLOBALS['is_dhtml_editor']) || !isset($GLOBALS['is_dhtml_editor']) ) {
        $str  = "var {$id}_editor_data = CKEDITOR.instances.{$id}.getData();\n";
    } else {
        $str  = "var {$id}_editor = document.getElementById('{$id}');\n";
    }
    return $str;
}

//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $textarea_name="내용을")
{
    if ( (isset($GLOBALS['is_dhtml_editor']) && $GLOBALS['is_dhtml_editor']) || !isset($GLOBALS['is_dhtml_editor']) ) {
        return "if (!{$id}_editor_data) { alert(\"$textarea_name 입력해 주십시오.\"); CKEDITOR.instances.{$id}.focus(); return false; }\n";
    } else {
        return "if (!{$id}_editor.value) { alert(\"$textarea_name 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
}
?>