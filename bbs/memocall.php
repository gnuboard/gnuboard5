<?php
include_once('./_common.php');
include_once($g4['path'].'/head.sub.php');
?>

<script>
if(confirm("<? echo $memo_msg; ?>")) {
    win_memo();
}
</script>

<noscript>
<article id="confirm_check">
<header>
    <hgroup>
        <h1><? echo $header; ?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2>아래 내용을 확인해 주세요.</h2>
    </hgroup>
</header>
<p>
    <? echo $memo_msg; ?>
</p>

<a href="<? echo $g4['bbs_path']; ?>/memo.php" target="_blank">확인</a>
<a href="<? echo $$g4['path']; ?>">취소</a><br><br>
</article>
</noscript>

<?php
include_once($g4['path'].'/tail.sub.php');
?>