<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 step1';
include_once ('../admin.head.php');

$rollback_file = isset($_POST['rollback_file']) ? $_POST['rollback_file'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$userpassword = isset($_POST['password']) ? $_POST['password'] : null;
$port = isset($_POST['port']) ? $_POST['port'] : null;

$totalSize = $g5['update']->getTotalStorageSize();
$freeSize = $g5['update']->getUseableStorageSize();
$useSize = $g5['update']->getUseStorageSize();
$usePercent = $g5['update']->getUseStoragePercenty();


?>
<div>
    <p>사용량 : <?php echo $useSize; ?>/<?php echo $totalSize; ?> (<?php echo $usePercent; ?>%)</p>
    <br>
</div>

<?php
if($g5['update']->checkInstallAvailable() == false ) {
    die("가용용량이 부족합니다. (20MB 이상)");
} else {
    echo "<p><b>업데이트 가능</b></p>";
}
if($rollback_file == null) die("롤백할 파일이 선택되지 않았습니다.");
if($port == null) die("포트가 입력되지 않았습니다.");
if($username == null)  die("{$port}계정명이 입력되지 않았습니다.");
if($userpassword == null) die("{$port} 비밀번호가 입력되지 않았습니다.");

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $port, $username, $userpassword);
if($conn_result == false) die("연결에 실패했습니다.");

$result = $g5['update']->unzipBackupFile(G5_DATA_PATH . '/backup/' . $rollback_file);
if($result == false) die("압축해제에 실패했습니다.");

$g5['update']->setRollbackVersion(preg_replace('/.zip/', '', G5_DATA_PATH . '/backup/' .  $rollback_file));
$rollback_version = $g5['update']->getRollbackVersion();

$g5['update']->setTargetVersion($rollback_version);
$list = $g5['update']->getVersionCompareList();
if($list == null) die("비교파일리스트가 존재하지 않습니다.");

$compare_list = $g5['update']->checkRollbackVersionComparison($list, G5_DATA_PATH . '/backup/' . $rollback_file);
if($compare_list == false) die("파일 비교에 실패했습니다.");

?>

<div class="version_box">
    <form method="POST" name="update_box" class="update_box" action="./rollback_step2.php" onsubmit="return update_submit(this);">
        <input type="hidden" name="compare_check" value="<?php echo $compare_list['type']; ?>">
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <input type="hidden" name="password" value="<?php echo $userpassword; ?>">
        <input type="hidden" name="port" value="<?php echo $port; ?>">
        <input type="hidden" name="rollback_file" value="<?php echo $rollback_file; ?>">
        <?php foreach($list as $key => $var) {
            $txt = '';
            if(isset($var) && isset($compare_list['item'])) {
                if(in_array($var, $compare_list['item'])) {
                    $txt = " (변경)";
                }    
            } ?>
            <p>파일위치 : <?php echo $var.$txt; ?><p>
        <?php } ?>
        <br>
        <?php if($compare_list['type'] == 'Y') { ?>
            <button type="submit" class="btn btn_submit">업데이트 진행</button>
        <?php } else { ?>
            <?php if($compare_list['type'] == 'N') { ?>
                <p style="color:red; font-weight:bold;">롤백이 진행될 파일리스트 입니다.</p>
            <?php } ?>
            <div style="margin-top:30px;">
                <button type="submit" class="btn btn_submit">복원 진행</button>
                <button type="button" class="btn btn_03 btn_cancel">복원 진행 취소</button>
            </div>
        <?php } ?>
    </form>
</div>

<script>
    $(".btn_cancel").click(function() {
        history.back();
    })

    function update_submit(f) {
        if(f.compare_check.value == 'N') {
            if(confirm("기존에 변경한 파일에 문제가 발생할 수 있습니다.\n복원을 진행하시겠습니까?")) {
                return true;
            }

            return false;
        }

        return true;
    }
</script>

<?php
    include_once ('../admin.tail.php');
?>




