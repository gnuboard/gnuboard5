<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $g5, $config;
    static $js = true;

    $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];

    $html = "";
    $html .= "<span class=\"sound_only\">웹에디터 시작</span>";
    if ($is_dhtml_editor)
        $html .= '<script>document.write("<div class=\'cke_sc\'><button type=\'button\' class=\'btn_cke_sc\'>단축키 일람</button></div>");</script>';

    if ($is_dhtml_editor && $js) {
        $html .= "\n".'<script src="'.$editor_url.'/js/HuskyEZCreator.js"></script>';
        $html .= "\n".'<script>var g5_editor_url = "'.$editor_url.'", oEditors = [];</script>';
        $html .= "\n".'<script src="'.$editor_url.'/config.js"></script>';
        $html .= "\n<script>";
        $html .= '
        $(function(){
            $(".btn_cke_sc").click(function(){
                if ($(this).next("div.cke_sc_def").length) {
                    $(this).next("div.cke_sc_def").remove();
                    $(this).text("단축키 일람");
                } else {
                    $(this).after("<div class=\'cke_sc_def\' />").next("div.cke_sc_def").load("'.$editor_url.'/shortcut.html");
                    $(this).text("단축키 일람 닫기");
                }
            });
            $(".btn_cke_sc_close").live("click",function(){
                $(this).parent("div.cke_sc_def").remove();
            });
        });';
        $html .= "\n</script>";
        $js = false;
    }

    $smarteditor_class = $is_dhtml_editor ? "smarteditor2" : "";
    $html .= "\n<textarea id=\"$id\" name=\"$id\" class=\"$smarteditor_class\" maxlength=\"65536\">$content</textarea>";
    $html .= "\n<span class=\"sound_only\">웹 에디터 끝</span>";
    return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "var {$id}_editor_data = oEditors.getById['{$id}'].getIR();\noEditors.getById['{$id}'].exec('UPDATE_CONTENTS_FIELD', []);\n";
    } else {
        return "var {$id}_editor = document.getElementById('{$id}');\n";
    }
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "if (!{$id}_editor_data || {$id}_editor_data == '&nbsp;' || {$id}_editor_data == '<p>&nbsp;</p>' || {$id}_editor_data == '<p><br></p>' || {$id}_editor_data == '<p></p>') { alert(\"내용을 입력해 주십시오.\"); oEditors.getById['{$id}'].exec('FOCUS'); return false; }\n";
    } else {
        return "if (!{$id}_editor.value) { alert(\"내용을 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
}
?>