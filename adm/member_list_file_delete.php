<?php
$sub_menu = '100930';
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.', G5_URL);

$g5['title'] = '회원관리파일 일괄삭제';
include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

<?php
flush();

if (!$dir = @opendir(G5_DATA_PATH . '/member_list')) {
    echo '<p>회원관리파일를 열지못했습니다.</p>';
}

$cnt = 0;
echo '<ul class="session_del">' . PHP_EOL;

$files = glob(G5_DATA_PATH . '/member_list/*');
$cnt = 0;

// 폴더 및 하위 파일 재귀 삭제 함수
function deleteFolder($folderPath) {
    $items = glob($folderPath . '/*');
    foreach ($items as $item) {
        if (is_dir($item)) {
            deleteFolder($item);
        } else {
            unlink($item);
        }
    }
    rmdir($folderPath); // 폴더 자체 삭제
}

if (is_array($files)) {
    foreach ($files as $member_list_file) {
        // log 확장자가 아닌 파일/디렉토리 처리
        $ext = strtolower(pathinfo($member_list_file, PATHINFO_EXTENSION));
        $basename = basename($member_list_file);

        if (is_file($member_list_file) && $ext !== 'log') {
            unlink($member_list_file);
            echo '<li>파일 삭제: ' . $member_list_file . '</li>' . PHP_EOL;
            $cnt++;
        } elseif (is_dir($member_list_file) && $basename !== 'log') {
            deleteFolder($member_list_file);
            echo '<li>폴더 삭제: ' . $member_list_file . '</li>' . PHP_EOL;
            $cnt++;
        }

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}
echo '<li>완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>회원관리파일 ' . $cnt . '건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');