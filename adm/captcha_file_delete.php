<?php
$sub_menu = '100910';
require_once './_common.php';

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_URL);
}

$g5['title'] = '캡챠파일 일괄삭제';
require_once './admin.head.php';
?>

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

<?php
flush();

if (!$dir = @opendir(G5_DATA_PATH . '/cache')) {
    echo '<p>캐시디렉토리를 열지못했습니다.</p>';
}

$cnt = 0;
echo '<ul class="session_del">' . PHP_EOL;

$files = glob(G5_DATA_PATH . '/cache/?captcha-*');
if (is_array($files)) {
    $before_time  = G5_SERVER_TIME - 3600; // 한시간전
    foreach ($files as $gcaptcha_file) {
        $modification_time = filemtime($gcaptcha_file); // 파일접근시간

        if ($modification_time > $before_time) {
            continue;
        }

        $cnt++;
        unlink($gcaptcha_file);
        echo '<li>' . $gcaptcha_file . '</li>' . PHP_EOL;

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}

echo '<li>완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>캡챠파일 ' . $cnt . '건의 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

<?php
require_once './admin.tail.php';
