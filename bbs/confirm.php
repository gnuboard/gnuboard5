<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');

$url1 = clean_xss_tags($url1);
$url2 = clean_xss_tags($url2);
$url3 = clean_xss_tags($url3);

// url 체크
check_url_host($url1);
check_url_host($url2);
check_url_host($url3);
?>

<script>
var conf = "<?php echo strip_tags($msg); ?>";
if (confirm(conf)) {
    document.location.replace("<?php echo $url1; ?>");
} else {
    document.location.replace("<?php echo $url2; ?>");
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
    <?php echo $msg; ?>
</p>

<a href="<?php echo $url1; ?>">확인</a>
<a href="<?php echo $url2; ?>">취소</a><br><br>
<a href="<?php echo $url3; ?>">돌아가기</a>
</article>
</noscript>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>