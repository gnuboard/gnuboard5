<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 step2';
include_once ('./admin.head.php');

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $_POST['port'], $_POST['username'], $_POST['password']);
if($conn_result == false) alert("연결에 실패했습니다.");

$g5['update']->setTargetVersion($_POST['target_version']);

$result = $g5['update']->downloadVersion();
if($result == false) die("목표버전 다운로드에 실패했습니다.");

echo $g5['update']->targetVersion." 버전 파일 다운로드 완료<br>";
foreach($list as $key => $var) {
    $result = $g5['update']->writeUpdateFile(G5_PATH.'/'.$var, G5_DATA_PATH.'/update/'.$version_list.'/'.$var);
    if($result == false) echo $var." 업데이트 실패<br>";
}

?>


<?php
include_once ('./admin.tail.php');
?>