<?php
include_once('./_common.php');

include_once(G5_PATH.'/head.sub.php');

if ($is_guest) {
    $href = './login.php?'.$qstr.'&amp;url='.urlencode('./board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
    $href2 = str_replace('&amp;', '&', $href);
    echo <<<HEREDOC
    <script>
        alert('회원만 접근 가능합니다.');
        opener.location.href = '$href2';
        window.close();
    </script>
    <noscript>
    <p>회원만 접근 가능합니다.</p>
    <a href="$href">로그인하기</a>
    </noscript>
HEREDOC;
    exit;
}

echo <<<HEREDOC
<script>
    if (window.name != 'win_scrap') {
        alert('올바른 방법으로 사용해 주십시오.');
        window.close();
    }
</script>
HEREDOC;

if ($write['wr_is_comment'])
    alert_close('코멘트는 스크랩 할 수 없습니다.');

$sql = " select count(*) as cnt from {$g5['scrap_table']}
            where mb_id = '{$member['mb_id']}'
            and bo_table = '$bo_table'
            and wr_id = '$wr_id' ";
$row = sql_fetch($sql);
if ($row['cnt']) {
    echo <<<HEREDOC
    <script>
    if (confirm('이미 스크랩하신 글 입니다.\\n\\n지금 스크랩을 확인하시겠습니까?'))
        document.location.href = './scrap.php';
    else
        window.close();
    </script>
    <noscript>
    <p>이미 스크랩하신 글 입니다.</p>
    <a href="./scrap.php">스크랩 확인하기</a>
    <a href="./board.php?bo_table={$bo_table}&amp;wr_id=$wr_id">돌아가기</a>
    </noscript>
HEREDOC;
    exit;
}

include_once($member_skin_path.'/scrap_popin.skin.php');

include_once(G5_PATH.'/tail.sub.php');
?>
