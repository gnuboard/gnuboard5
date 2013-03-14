<?
$sub_menu = "100900";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", G4_URL);

$g4['title'] = "캐시파일 일괄삭제";
include_once("./admin.head.php");
?>

<div id="cache_del">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
    <?
    flush();

    if (!$dir=@opendir(G4_DATA_PATH.'/cache')) {
        echo "<p>최신글 캐시디렉토리를 열지못했습니다.</p>";
    }

    $cnt=0;
    echo "<ul>\n";
    while($file=readdir($dir)) {
        if ($file=='.' || $file=='..') continue;

        $cache_file = G4_DATA_PATH.'/cache/'.$file;

        if (!$atime=@fileatime($cache_file))
            continue;

        $cnt++;
        $return = unlink($cache_file);
        //echo "<script>document.getElementById('delete_message').innerHTML += '{$cache_file}<br>';</script>\n";
        echo "<li>{$cache_file}</li>\n";

        flush();

        if ($cnt%10==0)
            //echo "<script>document.getElementById('delete_message').innerHTML = '';</script>\n";
            echo "\n";
    }
    echo "<li>완료됨</li>\n</ul>\n";
    //echo "<script>document.getElementById('delete_message').innerHTML += '최신글 캐시파일 {$cnt}건 삭제 완료.<br><br>프로그램의 실행을 끝마치셔도 좋습니다.';</script>\n";
    echo "<p><span>최신글 캐시파일 {$cnt}건 삭제가 완료됐습니다.</span><br>프로그램의 실행을 끝마치셔도 좋습니다.</p>\n";
    ?>
</div>

<?php
include_once("./admin.tail.php");
?>