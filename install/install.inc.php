<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$data_path = '../'.G5_DATA_DIR;

if (!$title) $title = G5_VERSION." 설치";
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="install.css">
</head>
<body>

<div id="ins_bar">
    <span id="bar_img">GNUBOARD5</span>
    <span id="bar_txt">INSTALLATION</span>
</div>

<?php
// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
?>
<h1><?php echo G5_VERSION; ?> 프로그램이 이미 설치되어 있습니다.</h1>

<div class="ins_inner">
    <p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 다음 파일을 삭제 하신 후 새로고침 하십시오.</p>
    <ul>
        <li><?php echo $dbconfig_file ?></li>
    </ul>
</div>
<?php
    exit;
}
?>

<?php
$exists_data_dir = true;
// data 디렉토리가 있는가?
if (!is_dir($data_path))
{
?>
<h1><?php echo G5_VERSION; ?> 설치를 위해 아래 내용을 확인해 주십시오.</h1>

<div class="ins_inner">
    <p>
        루트 디렉토리에 아래로 <?php echo G5_DATA_DIR ?> 디렉토리를 생성하여 주십시오.<br />
        (common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />
        $> mkdir <?php echo G5_DATA_DIR ?><br /><br />
        윈도우의 경우 data 폴더를 하나 생성해 주시기 바랍니다.<br /><br />
        위 명령 실행후 브라우저를 새로고침 하십시오.
    </p>
</div>
<?php
    $exists_data_dir = false;
}
?>

<?php
$write_data_dir = true;
// data 디렉토리에 파일 생성 가능한지 검사.
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $sapi_type = php_sapi_name();
    if (substr($sapi_type, 0, 3) == 'cgi') {
        if (!(is_readable($data_path) && is_executable($data_path)))
        {
        ?>
        <div class="ins_inner">
            <p>
                <?php echo G5_DATA_DIR ?> 디렉토리의 퍼미션을 705로 변경하여 주십시오.<br /><br />
                $> chmod 705 <?php echo G5_DATA_DIR ?> 또는 chmod uo+rx <?php echo G5_DATA_DIR ?><br /><br />
                위 명령 실행후 브라우저를 새로고침 하십시오.
            </p>
        </div>
        <?php
            $write_data_dir = false;
        }
    } else {
        if (!(is_readable($data_path) && is_writeable($data_path) && is_executable($data_path)))
        {
        ?>
        <div class="ins_inner">
            <p>
                <?php echo G5_DATA_DIR ?> 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />
                $> chmod 707 <?php echo G5_DATA_DIR ?> 또는 chmod uo+rwx <?php echo G5_DATA_DIR ?><br /><br />
                위 명령 실행후 브라우저를 새로고침 하십시오.
            </p>
        </div>
        <?php
            $write_data_dir = false;
        }
    }
}
?>