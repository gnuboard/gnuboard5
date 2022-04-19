<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 step2';
include_once ('../admin.head.php');

$rollback_file = isset($_POST['rollback_file']) ? $_POST['rollback_file'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$userpassword = isset($_POST['password']) ? $_POST['password'] : null;
$port = isset($_POST['port']) ? $_POST['port'] : null;

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $port, $username, $password);
if($conn_result == false) die("연결에 실패했습니다.");

if($g5['update']->target_version == $g5['update']->now_version) die("목표버전이 현재버전과 동일합니다.");

$g5['update']->setRollbackVersion(preg_replace('/.zip/', '', G5_DATA_PATH . '/backup/' .  $rollback_file));
$rollback_version = $g5['update']->getRollbackVersion();

$g5['update']->setTargetVersion($rollback_version);
$list = $g5['update']->getVersionCompareList();
if($list == null) die("비교파일리스트가 존재하지 않습니다.");

?>
    <p style="font-size:15px; font-weight:bold;">현재 서버 버전 : <?php echo $g5['update']->now_version; ?> -> 백업 파일 버전 : <?php echo $g5['update']->target_version; ?> 복원 진행</p>
    <br>
<?php

$result = $g5['update']->createBackupZipFile(G5_DATA_PATH."/backup/".date('YmdHis', G5_SERVER_TIME).".zip");
if($result == "success") {
    $update_check = array();
    foreach($list as $key => $var) {
        $originPath = G5_PATH.'/'.$var;
        $backupPath = preg_replace('/.zip/', '', G5_DATA_PATH . '/backup/' .  $rollback_file) .'/'. $var;

        if(!file_exists($backupPath) && file_exists($originPath)) { // 백업파일은 존재하지않지만 서버파일은 존재할때
            $result = $g5['update']->deleteOriginFile($originPath, $backupPath);
            if($result == "success") {
                $update_check['success'][] = $var;
            } else {
                $update_check['fail'][] = array('file' => $var, 'message' => $result);
            }
        }
        if(!is_dir(dirname($backupPath)) && is_dir(dirname($originPath))) { // 백업디렉토리는 존재하지않지만 서버디렉토리는 존재할때
            $result = $g5['update']->removeEmptyDir(dirname($originPath));
            if($result == "success") {
                $update_check['success'][] = $var;
            } else {
                $update_check['fail'][] = array('file' => $var, 'message' => $result);
            }
        }
        $result = $g5['update']->writeUpdateFile($originPath, $backupPath);
        if($result == "success") {
            $update_check['success'][] = $var;
        } else {
            $update_check['fail'][] = array('file' => $var, 'message' => $result);
        }
    }

    $g5['update']->deleteBackupDir(preg_replace('/.zip/', '', G5_DATA_PATH . '/backup/' .  $rollback_file));
}else {
    $update_check['fail'][] = array('file' => $var, 'message' => $result);
}
$g5['update']->disconnect();

?>

<div>
    <p style="font-weight:bold; font-size:15px;">복원 성공</p>
    <?php foreach($update_check['success'] as $key => $var) { ?>
        <p><?php echo $var; ?></p>
    <?php } ?>
    <br>

    <p style="font-weight:bold; font-size:15px;">백업본에 존재하지 않아 제거된 파일</p>
    <?php foreach($update_check['fail'] as $key => $var) { ?>
        <p><span style="color:red;"><?php echo $var['file']; ?></span><?php echo ' : ' . $var['message']; ?></p>
    <?php } ?>
</div>

<?php
include_once ('../admin.tail.php');
?>