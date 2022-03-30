<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 step1';
include_once ('./admin.head.php');

function build_folder_structure(&$dirs, $path_array) {
    if (count($path_array) > 1) {
        if (!isset($dirs[$path_array[0]])) {
            $dirs[$path_array[0]] = array();
        }

        build_folder_structure($dirs[$path_array[0]], array_splice($path_array, 1));
    } else {
        $dirs[] = $path_array[0];
    }
}

$target_version = isset($_POST['version_list']) ? $_POST['version_list'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$userpassword = isset($_POST['password']) ? $_POST['password'] : null;
$port = isset($_POST['port']) ? $_POST['port'] : null;

if($target_version == null) alert("목표버번 정보가 입력되지 않았습니다.");
if($port == null) alert("포트가 입력되지 않았습니다.");
if($username == null)  alert("{$port}계정명이 입력되지 않았습니다.");
if($userpassword == null) alert("{$port} 비밀번호가 입력되지 않았습니다.");

$conn_result = $g5_update->connect($_SERVER['HTTP_HOST'], $port, $username, $userpassword);
if($conn_result == false) alert("연결에 실패했습니다.");

$g5_update->setTargetVersion($version_list);
$list = $g5_update->getVersionCompareList();

if($list == null) alert("비교파일리스트가 존재하지 않습니다.");

$parray = array();
echo "변경될 파일리스트<br>";
foreach($list as $key => $var) {
    echo $var."<br>";
}

$g5_update->clearUpdatedir();
$result = $g5_update->downloadVersion($version_list);
if($result == false) alert("목표버전 다운로드에 실패했습니다.");

echo $g5_update->targetVersion." 버전 파일 다운로드 완료";
foreach($list as $key => $var) {
    $result = $g5_update->writeUpdateFile(G5_PATH.'/'.$var, G5_DATA_PATH.'/update/'.$version_list.'/'.$var);
    if($result == false) alert($var." 업데이트 실패<br>");
}

$g5_update->clearUpdatedir();

goto_url("./upgrade.php");

?>


<?php
    include_once ('./admin.tail.php');
// foreach($list as $key => $var) {
//     $path_array = explode('/', $var);
//     build_folder_structure($parray, $path_array);
// }

// foreach($parray as $key => $var) {
//     echo $key =>
// }
?>




