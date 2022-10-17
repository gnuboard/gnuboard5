<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');

$msg = isset($msg) ? strip_tags($msg) : '';

$msg2 = str_replace("\\n", "<br>", $msg);

if($error) {
    $header2 = "다음 항목에 오류가 있습니다.";
    $msg3 = "새창을 닫으시고 이전 작업을 다시 시도해 주세요.";
} else {
    $header2 = "다음 내용을 확인해 주세요.";
    $msg3 = "새창을 닫으신 후 서비스를 이용해 주세요.";
}
?>

<script>
alert("<?php echo $msg; ?>");
try {
    window.close();
} catch(error) {
    history.back();
}

setTimeout(function() {
    if (window.history.length) {
        window.history.back();
    }
}, 500);
</script>

<noscript>
<div id="validation_check">
    <h1><?php echo $header2 ?></h1>
    <p class="cbg">
        <?php echo $msg2 ?>
    </p>
    <p class="cbg">
        <?php echo $msg3 ?>
    </p>

</div>

<?php /*
<article id="validation_check">
<header>
    <hgroup>
        <!-- <h1>회원가입 정보 입력 확인</h1> --> <!-- 수행 중이던 작업 내용 -->
        <h1><?php echo $header ?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2><?php echo $header2 ?></h2>
    </hgroup>
</header>
<p>
    <!-- <strong>항목</strong> 오류내역 -->
    <!--
    <strong>이름</strong> 필수 입력입니다. 한글만 입력할 수 있습니다.<br>
    <strong>이메일</strong> 올바르게 입력하지 않았습니다.<br>
    -->
    <?php echo $msg2 ?>
</p>
<p>
    <?php echo $msg3 ?>
</p>

</article>
*/ ?>

</noscript>

<?php
include_once(G5_PATH.'/tail.sub.php');