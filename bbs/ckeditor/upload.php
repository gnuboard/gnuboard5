<?php
include_once("../../common.php");

$ym = date('ym', G4_SERVER_TIME);

$data_dir = G4_DATA_PATH.'/editor/'.$ym;
$data_url = G4_DATA_URL.'/editor/'.$ym;
@mkdir($data_dir, 0707);
@chmod($data_dir, 0707);
 
// 업로드 DIALOG 에서 전송된 값
$funcNum = $_GET['CKEditorFuncNum'] ;
$CKEditor = $_GET['CKEditor'] ;
$langCode = $_GET['langCode'] ;
 
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