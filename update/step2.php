<?php
include_once('./_common.php');
include_once('./head.php');

$g5['title'] = '그누보드 step2';

$target_version = isset($_POST['target_version']) ? $_POST['target_version'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$userpassword = isset($_POST['password']) ? $_POST['password'] : null;
$port = isset($_POST['port']) ? $_POST['port'] : null;

$conn_result = $g5['update']->connect($_SERVER['HTTP_HOST'], $port, $username, $password);
if($conn_result == false) die("연결에 실패했습니다.");

$g5['update']->setTargetVersion($target_version);

if($g5['update']->target_version == $g5['update']->now_version) die("목표버전이 현재버전과 동일합니다.");

$list = $g5['update']->getVersionCompareList();
if($list == false) die("비교파일리스트가 존재하지 않습니다.");

$result = $g5['update']->downloadVersion($target_version);
if($result == false) die("목표버전 다운로드에 실패했습니다.");

?>
    <p style="font-size:15px; font-weight:bold;"><?php echo $g5['update']->targetVersion; ?> 버전 파일 다운로드 완료</p>
    <br>
    <br>
    <br>
<?php

$update_check = array();
foreach($list as $key => $var) {
    $result = $g5['update']->writeUpdateFile(G5_PATH.'/'.$var, G5_DATA_PATH.'/update/'.$target_version.'/'.$var);
    if($result == "success") {
        $update_check['success'][] = $var;
    } else {
        $update_check['fail'][] = array('file' => $var, 'message' => $result);
    }
}

$g5['update']->disconnect();

?>

<div>
    <p style="font-weight:bold; font-size:15px;">업데이트 성공</p>
    <?php foreach($update_check['success'] as $key => $var) { ?>
        <p><?php echo $var; ?></p>
    <?php } ?>
    <br>

    <p style="font-weight:bold; font-size:15px;">업데이트 실패</p>
    <?php foreach($update_check['fail'] as $key => $var) { ?>
        <p><span style="color:red;"><?php echo $var['file']; ?></span><?php echo ' : ' . $var['message']; ?></p>
    <?php } ?>
</div>

<?php
include_once ('./admin.tail.php');
?>