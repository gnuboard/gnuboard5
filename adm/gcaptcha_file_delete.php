<?php
$sub_menu = '100910';
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.', G4_URL);

$g4['title'] = '캡챠파일 일괄삭제';
include_once('./admin.head.php');
?>

<div id="cache_del">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
    <?php
    flush();

    if (!$dir=@opendir(G4_DATA_PATH.'/cache')) {
        echo '<p>캐시디렉토리를 열지못했습니다.</p>';
    }

    $cnt=0;
    echo '<ul>'.PHP_EOL;

    $files = glob(G4_DATA_PATH.'/cache/gcaptcha-*');
    if (is_array($files)) {
        $before_time  = date('ymdHis', G4_SERVER_TIME - 3600); // 한시간전
        foreach ($files as $gcaptcha_file) {
            $access_time = date('ymdHis', fileatime($gcaptcha_file)); // 파일접근시간

            if ($access_time > $before_time) continue;

            $cnt++;
            unlink($gcaptcha_file);
            echo '<li>'.$gcaptcha_file.'</li>'.PHP_EOL;

            flush();

            if ($cnt%10==0) 
                echo PHP_EOL;
        }
    }

    echo '<li>완료됨</li></ul>'.PHP_EOL;
    echo '<p><span>캡챠파일 '.$cnt.'건의 삭제가 완료됐습니다.</span><br>프로그램의 실행을 끝마치셔도 좋습니다.</p>'.PHP_EOL;
    ?>
</div>

<?php
include_once('./admin.tail.php');
?>