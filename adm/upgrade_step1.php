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

if($target_version == null) alert("목표버전 정보가 입력되지 않았습니다.");
if($port == null) alert("포트가 입력되지 않았습니다.");
if($username == null)  alert("{$port}계정명이 입력되지 않았습니다.");
if($userpassword == null) alert("{$port} 비밀번호가 입력되지 않았습니다.");

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $port, $username, $userpassword);
if($conn_result == false) alert("연결에 실패했습니다.");

$g5['update']->setTargetVersion($version_list);
$list = $g5['update']->getVersionCompareList();

if($list == null) alert("비교파일리스트가 존재하지 않습니다.");

$compare_list = $g5['update']->checkSameVersionComparison($list);
if($compare_list == false) alert("파일 비교에 실패했습니다.");

?>

<div class="version_box">
    <form method="POST" name="update_box" class="update_box" action="./upgrade_step2.php" onsubmit="return update_submit(this);">
        <input type="hidden" name="compare_check" value="<?php echo $compare_list['type']; ?>">
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <input type="hidden" name="password" value="<?php echo $userpassword; ?>">
        <input type="hidden" name="port" value="<?php echo $port; ?>">
        <input type="hidden" name="target_version" value="<?php echo $version_list; ?>">
        <?php if($compare_list['type'] == 'Y') { ?>
            <button type="submit" class="btn btn_submit">업데이트 진행</button>
        <?php } else { ?>
            <p style="color:red; font-weight:bold;">기존 버전간의 변경된 파일이 존재합니다.</p>
            <?php foreach($compare_list['item'] as $key => $var) { ?>
                <p>파일위치 : <?php echo $var; ?><p>
            <?php } ?>
            <div style="margin-top:30px;">
                <button type="submit" class="btn btn_submit">강제 업데이트 진행</button>
                <button type="" class="btn btn_03">업데이트 진행 취소</button>
            </div>
        <?php } ?>
    </form>
</div>

<script>
    function update_submit(f) {
        if(f.compare_check.value == 'N') {
            if(confirm("기존에 변경한 파일에 문제가 발생할 수 있습니다.\n패치 진행하시겠습니까?")) {
                return true;
            }

            return false;
        }

        return true;
    }
</script>

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




