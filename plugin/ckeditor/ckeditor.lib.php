<?php
function editor_textarea($id, $content="", $class="") 
{
    global $g4;
    $upload_url = $g4['path']."/plugin/ckeditor/upload.php?type=Images";
    $str  = "<textarea id=\"$id\" name=\"$id\" class=\"ckeditor $class\" rows=\"10\" style=\"width:100%;\">$content</textarea>\n";
    //$str .= "<script> CKEDITOR.replace('$id',{ filebrowserUploadUrl : '$upload_url'}); </script>\n";
    return $str;
}
?>