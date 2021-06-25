<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $g5, $config, $is_mobile, $w, $board, $write;
    static $js = true;
    if( 
        $is_dhtml_editor && $content && 
        (
        (!$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])))
        || ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false )
        )
    ){       //글쓰기 기본 내용 처리
        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {  //textarea로 작성되고, html 내용이 없다면
            $content = nl2br($content);
        }
    }
    $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];

    $html = "";
    $html .= "<span class=\"sound_only\">웹에디터 시작</span>";
    if (!$is_mobile && $is_dhtml_editor) {
        $html .= '<script>document.write("<div class=\'cke_sc\'><!--<button type=\'button\' class=\'btn_cke_sc\'>단축키 일람</button>--></div>");</script>';
    }

    if ($is_dhtml_editor && $js) {
        switch($id) {
            case "wr_content":  $editor_height = 350;   break;
            default :           $editor_height = 200;   break;
        }
        $html .= "\n".'<script src="'.$editor_url.'/ckeditor.js?v=210624"></script>';
        $html .= "\n".'<script>var g5_editor_url = "'.$editor_url.'";</script>';
        $html .= "\n".'<script src="'.$editor_url.'/config.js?v=210624"></script>';
        $html .= "\n<script>";
        $html .= '
        var editor_id = "'.$id.'",       // 에디터 구분
            editor_height = '.$editor_height.',     // 에디터 높이
            editor_chk_upload = true,           // 업로드 상태
            editor_uri = "'.urlencode($_SERVER['REQUEST_URI']).'";     // 업로드 경로
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

    // 로딩상태 띄우기 (에디터 사용상태일때만)
    if($is_dhtml_editor) {
        // 에디터 사용상태일때 textarea 숨기기
        $editor_taDisplay = "border:none;";
        $html .= "
            <style>
            .editor_loading { position: absolute; left: 50%; top: 50%; z-index: 1; margin: -25px 0 0 -25px; border: 5px solid #f3f3f3; border-radius: 50%; border-top: 5px solid #3498db; width: 30px; height: 30px; -webkit-animation: spin 2s linear infinite; animation: spin 2s linear infinite; }
            @-webkit-keyframes spin { 0% { -webkit-transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); } } 
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        ";
        $html .= "<div class=\"editor_loading\"></div>".PHP_EOL;    // 로딩이미지 출력 부
        $html .= "
            <script>
            CKEDITOR.on( 'instanceLoaded', function (e) {
                var loader = $(\"div.editor_loading\").css(\"display\",\"none\");
            });
            </script>
        ";
    }

    $ckeditor_class = $is_dhtml_editor ? "ckeditor" : "";
    $html .= "\n<textarea id=\"$id\" name=\"$id\" class=\"$ckeditor_class\" maxlength=\"65536\" style=\"height:{$editor_height}px; {$editor_taDisplay}\">$content</textarea>";
    $html .= "\n<span class=\"sound_only\">웹 에디터 끝</span>";
    // 현재 폼이름 GET
    $html .= "<script> var editor_form_name = document.getElementById('{$id}').form.name; </script>";
    return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $is_dhtml_editor=true)
{
    $print_js = "";
    if ($is_dhtml_editor) {
        $print_js .= "var {$id}_editor_data = CKEDITOR.instances.{$id}.getData();\n";
    } else {
        $print_js .= "var {$id}_editor = document.getElementById('{$id}');\n";
    }

    return $print_js;
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $is_dhtml_editor=true)
{
    $print_js = "";
    if ($is_dhtml_editor) {
        $print_js .= "if (!{$id}_editor_data) { alert(\"내용을 입력해 주십시오.\"); CKEDITOR.instances.{$id}.focus(); return false; }\n";
        $print_js .= "if (typeof(f.{$id})!=\"undefined\") f.{$id}.value = {$id}_editor_data;\n";
        // 썸네일 이미지경로 원본파일로 변경
        $print_js .= "
        var temp_data = {$id}_editor_data.replace(/thumb\-([_\d\.]+)_\d+x\d+/gim, function(res1, res2) { return res2; });
        CKEDITOR.instances.wr_content.setData(temp_data);".PHP_EOL;
    } else {
        $print_js .= "if (!{$id}_editor.value) { alert(\"내용을 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
    $print_js .= "if(typeof(editor_chk_upload) != \"undefined\" && !editor_chk_upload) { alert(\"이미지가 업로드 중 입니다.\"); return false; }\n";

    return $print_js;
}
?>