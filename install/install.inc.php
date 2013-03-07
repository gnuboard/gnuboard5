<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$data_path = '../'.G4_DATA_DIR;

if (!$title) $title = "그누보드4S 설치";
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?=$title?></title>
<style>
body {margin:0;padding:0;background:#f7f7f7}
h1 {margin:50px auto 30px;width:540px;font-size:1.4em}
p {line-height:1.5em}
table {width:100%;border-collapse:collapse;border-spacing:0;font-size:0.895em}
caption {padding:0 0 20px;font-weight:bold;text-align:left}
th,td {padding:5px;border:1px solid #ddd}
th {text-align:left}
td span {display:block;margin:0 0 5px;color:#666;font-size:0.9em}

#wrapper {margin:0 auto;padding:20px;width:500px;border:1px solid #eee;background:#fff}

#idx_license {padding:10px;width:480px;height:300px;border:1px solid #ccc;background:#000;color:#fff}
#idx_agree {padding:20px;font-weight:bold;text-align:center}

#btn_confirm {text-align:center}

.outside {margin:0 auto;padding:20px 0;width:542px}
.st_strong {color:#ff3061;font-weight:normal}
</style>
</head>
<body>

<h1><?=$title?></h1>

<div id="wrapper">

<?
// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G4_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
?>
    <p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 '.$dbconfig_file.' 파일을 삭제후 설치하시기 바랍니다.</p>
<?
    exit;
}
?>

<?
// data 디렉토리가 있는가?
if (!is_dir($data_path))
{
?>
    <p>루트 디렉토리에 아래로 '.G4_DATA_DIR.' 디렉토리를 생성하여 주십시오.<br />(common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />$> mkdir '.G4_DATA_DIR.'<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>
<?
    exit;
}
?>

<?
// data 디렉토리에 파일 생성 가능한지 검사.
if (!(is_readable($data_path) && is_writeable($data_path) && is_executable($data_path)))
{
?>
    <p>'.G4_DATA_DIR.' 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />$> chmod 707 '.G4_DATA_DIR.' 또는 chmod uo+rwx '.G4_DATA_DIR.'<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>
<?
    exit;
}
?>