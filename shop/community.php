<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

$list_mod = 2; // 한라인에 몇개씩 출력할것인지?
$list_row = 3; // 한게시판당 몇행씩 출력할것인지?
$subject_len = 70; // 제목의 길이는?

$g5['title'] = '커뮤니티';
include_once('./_head.php');
?>

    <?php
    //  최신글 시작
    $sql = " select bo_table, bo_subject from {$g5['board_table']} order by gr_id, bo_table ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {

        // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
        // 사용방법
        // latest(스킨, 게시판아이디, 출력라인, 글자수);
        // 스킨은 입력하지 않을 경우 운영자 > 환경설정의 최신글 스킨경로를 기본 스킨으로 합니다.
        echo latest(shop_basic, $row['bo_table'], $list_row, $subject_len);

        echo "";
    }

    if ($i > 0 && ($i % $list_mod == 1))
        echo '';
    ?>

<?php
include_once('./_tail.php');
?>