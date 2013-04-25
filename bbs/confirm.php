<?php
include_once('./_common.php');
include_once(G4_PATH.'/head.sub.php');
?>

<article id="confirm_check">
<header>
    <hgroup>
        <h1><?php echo $header; ?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2>아래 내용을 확인해 주세요.</h2>
    </hgroup>
</header>
<p>
    <?php echo $msg; ?>
</p>

<a href="<?php echo $url1; ?>">확인</a>
<a href="<?php echo $url2; ?>">취소</a><br><br>
<a href="<?php echo $url3; ?>">돌아가기</a>
</article>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>