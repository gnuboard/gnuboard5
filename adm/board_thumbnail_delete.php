<?php
$sub_menu = '300100';
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'w');

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.');
}

$g5['title'] = $board['bo_subject'] . ' 게시판 썸네일 삭제';
require_once './admin.head.php';
?>

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

<?php
$dir = G5_DATA_PATH . '/file/' . $bo_table;

$cnt = 0;
if (is_dir($dir)) {
    echo '<ul>';
    $files = glob($dir . '/thumb-*');
    if (is_array($files)) {
        foreach ($files as $thumbnail) {
            $cnt++;
            @unlink($thumbnail);

            echo '<li>' . $thumbnail . '</li>' . PHP_EOL;

            flush();

            if (($cnt % 10) == 0) {
                echo PHP_EOL;
            }
        }
    }

    echo '<li>완료됨</li></ul>' . PHP_EOL;
    echo '<div class="local_desc01 local_desc"><p><strong>썸네일 ' . $cnt . '건의 삭제 완료됐습니다.</strong></p></div>' . PHP_EOL;
} else {
    echo '<p>첨부파일 디렉토리가 존재하지 않습니다.</p>';
}
?>

<div class="btn_confirm01 btn_confirm"><a href="./board_form.php?w=u&amp;bo_table=<?php echo $bo_table; ?>&amp;<?php echo $qstr; ?>">게시판 수정으로 돌아가기</a></div>

<?php
require_once './admin.tail.php';
