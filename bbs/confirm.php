<?
include_once('./_common.php');
include_once(G4_PATH.'/head.sub.php');
?>

<article id="confirm_check">
<header>
    <hgroup>
        <h1><? echo $header; ?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2>아래 내용을 확인해 주세요.</h2>
    </hgroup>
</header>
<p>
    <? echo $msg; ?>
</p>

<a href="<? echo $url1; ?>">확인</a>
<a href="<? echo $url2; ?>">취소</a><br><br>
<a href="<? echo $url3; ?>">돌아가기</a>
</article>

<?
include_once(G4_PATH.'/tail.sub.php');
?>