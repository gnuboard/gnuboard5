<?php
include_once('./_common.php');
include_once(G4_PATH.'/head.sub.php');
?>

<script>
if(confirm("<?php echo $memo_msg; ?>")) {
    win_memo();
}
</script>

<noscript>
<article id="confirm_check">
<header>
    <hgroup>
        <h1><?php echo $header; ?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2>아래 내용을 확인해 주세요.</h2>
    </hgroup>
</header>
<p>
    <?php echo str_replace("\\n", "<br>", $memo_msg); ?>
</p>

<a href="<?php echo $g4['bbs_path']; ?>/memo.php" target="_blank">확인</a>
<a href="<?php echo $G4_PATH; ?>">취소</a><br><br>
</article>
</noscript>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>