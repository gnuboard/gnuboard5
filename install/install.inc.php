<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 파일이 존재한다면 설치할 수 없다.
if (file_exists("../{$g4['dbconfig']}")) {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo "<p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 dbconfig 파일을 삭제후 설치하시기 바랍니다.</p>";
    exit;
}

// data 디렉토리가 있는가?
if (!is_dir("../data")) 
{
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";
    echo "<p>루트 디렉토리에 아래로 data 디렉토리를 생성하여 주십시오.<br />(common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />$> mkdir data<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>"; 
    exit;
}

// data 디렉토리에 파일 생성 가능한지 검사.
if (!(is_readable("../data") && is_writeable("../data") && is_executable("../data"))) 
{
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";
    echo "<p>data 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />$> chmod 707 data 또는 chmod uo+rwx data<br /><br />위 명령 실행후 다시 설치하여 주십시오.</p>"; 
    exit;
}
?>