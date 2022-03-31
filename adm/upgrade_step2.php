<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 step2';
include_once ('./admin.head.php');

$target_version = isset($_POST['target_version']) ? $_POST['target_version'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$userpassword = isset($_POST['password']) ? $_POST['password'] : null;
$port = isset($_POST['port']) ? $_POST['port'] : null;

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $port, $username, $password);
if($conn_result == false) alert("연결에 실패했습니다.");

$result = $g5['update']->downloadVersion($target_version);
if($result == false) die("목표버전 다운로드에 실패했습니다.");

echo $g5['update']->targetVersion." 버전 파일 다운로드 완료<br>";

$g5['update']->setTargetVersion($target_version);
$list = $g5['update']->getVersionCompareList();
if($list == null) alert("비교파일리스트가 존재하지 않습니다.");

foreach($list as $key => $var) {
    $result = $g5['update']->writeUpdateFile(G5_PATH.'/'.$var, G5_DATA_PATH.'/update/'.$target_version.'/'.$var);
    if($result == false) { 
        echo $var." 업데이트 실패<br>";
    } else {
        echo $var." 업데이트 성공<br>";
    }
}

?>


<?php
include_once ('./admin.tail.php');
?>