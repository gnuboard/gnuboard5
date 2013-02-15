<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$data_path = '../'.G4_DATA_DIR;

// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G4_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    echo '<p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 '.$dbconfig_file.' 파일을 삭제후 설치하시기 바랍니다.</p>';
    exit;
}

// data 디렉토리가 있는가?
if (!is_dir($data_path))
{
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    echo '<p>루트 디렉토리에 아래로 '.G4_DATA_DIR.' 디렉토리를 생성하여 주십시오.<br />(common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />$> mkdir '.G4_DATA_DIR.'<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>';
    exit;
}

// data 디렉토리에 파일 생성 가능한지 검사.
if (!(is_readable($data_path) && is_writeable($data_path) && is_executable($data_path)))
{
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.PHP_EOL;
    echo '<p>'.G4_DATA_DIR.' 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />$> chmod 707 '.G4_DATA_DIR.' 또는 chmod uo+rwx '.G4_DATA_DIR.'<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>';
    exit;
}
?>