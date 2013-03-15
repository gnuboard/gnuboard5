<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $ckeditor=true, $class="")
{
    global $g4;
    static $js = true;

    $html = "";
    $html .= "<span class=\"sound_only\">웹에디터 시작</span>";
    $html .= "<div class=\"cke_sc\"><button type=\"button\" class=\"btn_cke_sc\">단축키 일람</button></div>";

    ob_start();
    include_once("shortcut.php");
    $html .= ob_get_contents();
    ob_end_clean();

    if ($js) {
        $html .= "\n".'<script src="'.G4_CKEDITOR_URL.'/ckeditor.js"></script>';
        $html .= "\n".'<script>var g4_ckeditor_url = "'.G4_CKEDITOR_URL.'";</script>';
        $html .= "\n".'<script src="'.G4_CKEDITOR_URL.'/config.js"></script>';
        $js = false;
    }

    $ckeditor_class = $ckeditor ? "ckeditor" : "";
    $html .= "\n<textarea id=\"$id\" name=\"$id\" class=\"$ckeditor_class $class\" style=\"width:100%;\">$content</textarea>";
    $html .= "\n<span class=\"sound_only\">웹 에디터 끝</span>";
    $html .= "\n<script>";
    $html .= "$('.btn_cke_sc').click(function(){";
    $html .= "$('.cke_sc_def').toggleClass('cke_sc_def_on');";
    $html .= "\n});";
    $html .= "\n</script>";
    return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $ckeditor=true)
{
    if ( $ckeditor ) {
        return "var {$id}_editor_data = CKEDITOR.instances.{$id}.getData();\n";
    } else {
        return "var {$id}_editor = document.getElementById('{$id}');\n";
    }
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $ckeditor=true, $textarea_name="내용을")
{
    if ( $ckeditor ) {
        return "if (!{$id}_editor_data) { alert(\"$textarea_name 입력해 주십시오.\"); CKEDITOR.instances.{$id}.focus(); return false; }\n";
    } else {
        return "if (!{$id}_editor.value) { alert(\"$textarea_name 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
}
?>