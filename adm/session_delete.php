<?
$sub_menu = "100700";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", $g4[path]);

$g4[title] = "세션 삭제";
include_once("./admin.head.php");
echo "'완료' 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.<br><br>";
echo "<span id='ct'></span>";
include_once("./admin.tail.php");
flush();

$session_path = "$g4[path]/data/session";  // 세션이저장된 디렉토리 
if (!$dir=@opendir($session_path)) { 
  echo "세션 디렉토리를 열지못했습니다."; 
} 

$cnt=0;
while($file=readdir($dir)) { 
	
    if (!strstr($file,'sess_')) { 
	    continue; 
	} 

    if (strpos($file,'sess_')!=0) { 
	    continue; 
	} 

	if (!$atime=@fileatime("$session_path/$file")) { 
	    continue; 
	} 
	if (time() > $atime + (3600 * 6)) {  // 지난시간을 초로 계산해서 적어주시면 됩니다. default : 6시간전
        $cnt++;
	    $return = unlink("$session_path/$file"); 
	    echo "<script>document.getElementById('ct').innerHTML += '$session_path/$file<br/>';</script>\n";

        flush();

        if ($cnt%10==0)
            echo "<script>document.getElementById('ct').innerHTML = '';</script>\n";
	} 
} 
echo "<script>document.getElementById('ct').innerHTML += '<br><br>세션데이터 {$cnt}건 삭제 완료.<br><br>프로그램의 실행을 끝마치셔도 좋습니다.';</script>\n";
?>