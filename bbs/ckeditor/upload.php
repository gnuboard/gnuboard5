<?php
$g4_path = "../.."; // common.php 의 상대 경로
include_once("$g4_path/common.php");

$up_dir = $g4['path'].'/'.$ckeditor->data;; // 기본 업로드 폴더
@mkdir($up_dir, 0707);
@chmod($up_dir, 0707);

$ym = date('ym', $g4['server_time']);

$data_dir = $g4['path'].'/'.$ckeditor->data.'/'.$ym;
$data_url = $g4['url'] .'/'.$ckeditor->data.'/'.$ym;
@mkdir($data_dir, 0707);
@chmod($data_dir, 0707);
 
// 업로드 DIALOG 에서 전송된 값
$funcNum = $_GET['CKEditorFuncNum'] ;
$CKEditor = $_GET['CKEditor'] ;
$langCode = $_GET['langCode'] ;

header("Content-Type: text/html; charset=$g4[charset]");
 
if(isset($_FILES['upload']['tmp_name'])) {
    $file_name = $_FILES['upload']['name'];
    $ext = substr($file_name, (strrpos($file_name, '.') + 1));
    if (!preg_match("/\.(jpe?g|gif|png)$/i", $file_name)) {
        echo '이미지만 가능';
        return false;
    }
 
    $save_dir = sprintf('%s/%s', $data_dir, $file_name);
    $save_url = sprintf('%s/%s', $data_url, $file_name);
 
    if (move_uploaded_file($_FILES["upload"]["tmp_name"],$save_dir))
        echo "<script>window.parent.CKEDITOR.tools.callFunction($funcNum, '$save_url', '업로드완료');</script>";
}
?>