<?
if (!defined('_GNUBOARD_')) exit;

function cheditor1($id, $content)
{
    return "<textarea id='ps_{$id}' style='display:none;'>{$content}</textarea>";
}

function cheditor2($form, $id, $width='100%', $height='250')
{
    global $g4;

    return "
    <input type='hidden' name='{$id}' id='{$id}'>
    <script>
    var ed_{$id} = new cheditor('ed_{$id}');
    ed_{$id}.editorPath = '{$g4[editor_path]}';
    ed_{$id}.width = '{$width}';
    ed_{$id}.height = '{$height}';
    ed_{$id}.pasteContent = true;
    ed_{$id}.pasteContentForm = 'ps_{$id}';
    ed_{$id}.formName = '{$form}';
    ed_{$id}.run();
    </script>";
}

function cheditor3($id)
{
    //return "document.getElementById('{$id}').value = ed_{$id}.outputHTML();";
    // body 태그 안의 내용만 반환 (백경동 님)
    return "document.getElementById('{$id}').value = ed_{$id}.outputBodyHTML();";
}
?>