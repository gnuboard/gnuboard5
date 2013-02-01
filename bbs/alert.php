<?
global $lo_location;
global $lo_url;

include_once('./_common.php');

if($error) {
    $g4['title'] = "오류가 있습니다.";
} else {
    $g4['title'] = "내용을 확인해 주세요.";
}
include_once(G4_PATH.'/head.sub.php');
// 필수 입력입니다.
// 양쪽 공백 없애기
// 필수 (선택 혹은 입력)입니다.
// 전화번호 형식이 올바르지 않습니다. 하이픈(-)을 포함하여 입력하세요.
// 이메일주소 형식이 아닙니다.
// 한글이 아닙니다. (자음, 모음만 있는 한글은 처리하지 않습니다.)
// 한글이 아닙니다.
// 한글, 영문, 숫자가 아닙니다.
// 한글, 영문이 아닙니다.
// 숫자가 아닙니다.
// 영문이 아닙니다.
// 영문 또는 숫자가 아닙니다.
// 영문, 숫자, _ 가 아닙니다.
// 최소 글자 이상 입력하세요.
// 이미지 파일이 아닙니다..gif .jpg .png 파일만 가능합니다.
// 파일만 가능합니다.
// 공백이 없어야 합니다.

$msg2 = str_replace("\\n", "<br>", $msg);

if (!$url) $url = $_SERVER['HTTP_REFERER'];

if($error) {
    $header2 = "다음 항목에 오류가 있습니다.";
} else {
    $header2 = "다음 내용을 확인해 주세요.";
}
?>

<script>
alert("<? echo $msg; ?>");
//document.location.href = "<? echo $url; ?>";
document.location.replace("<? echo $url; ?>");
</script>

<noscript>
<div id="validation_check">
    <h1><?=$header2?></h1>
    <p class="cbg">
        <?=$msg2?>
    </p>
    <? if($post) { ?>
    <form method="post" action="<?=$url?>">
    <?
    foreach($_POST as $key => $value) {
        if(strlen($value) < 1)
            continue;

        if(preg_match("/pass|pwd|capt|url/", $key))
            continue;
    ?>
    <input type="hidden" name="<?=$key?>" value="<?=$value?>">
    <?
    }
    ?>
    <input type="submit" value="돌아가기">
    </form>
    <? } else { ?>
    <div class="btn_confirm">
        <a href="<?=$url?>">돌아가기</a>
    </div>
    <? } ?>

<? /*
<article id="validation_check">
<header>
    <hgroup>
        <!-- <h1>회원가입 정보 입력 확인</h1> --> <!-- 수행 중이던 작업 내용 -->
        <h1><?=$header?></h1> <!-- 수행 중이던 작업 내용 -->
        <h2><?=$header2?></h2>
    </hgroup>
</header>
<p>
    <!-- <strong>항목</strong> 오류내역 -->
    <!--
    <strong>이름</strong> 필수 입력입니다. 한글만 입력할 수 있습니다.<br>
    <strong>이메일</strong> 올바르게 입력하지 않았습니다.<br>
    -->
    <?=$msg2?>
</p>

<a href="<?=$url?>">돌아가기</a>
</article>
*/ ?>
</div>
</noscript>

<?
include_once(G4_PATH.'/tail.sub.php');
?>