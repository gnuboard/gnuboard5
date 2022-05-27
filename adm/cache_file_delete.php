<?php
$sub_menu = '100900';
require_once './_common.php';

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_URL);
}

@require_once './safe_check.php';
if (function_exists('social_log_file_delete')) {
    social_log_file_delete();
}

run_event('adm_cache_file_delete_before');

$g5['title'] = '캐시파일 일괄삭제';
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

$files = glob(G5_DATA_PATH . '/cache/latest-*');
$content_files = glob(G5_DATA_PATH . '/cache/content-*');

$files = array_merge($files, $content_files);
if (is_array($files)) {
    foreach ($files as $cache_file) {
        $cnt++;
        unlink($cache_file);
        echo '<li>' . $cache_file . '</li>' . PHP_EOL;

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}

run_event('adm_cache_file_delete');

echo '<li>완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>최신글 캐시파일 ' . $cnt . '건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

<?php
require_once './admin.tail.php';
