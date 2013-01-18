<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div id="password_confirm">
    <p>
        <strong>비밀글 기능으로 보호된 글입니다.</strong>
        작성자와 관리자만 열람하실 수 있습니다. 작성자 본인이시라면 패스워드를 입력하세요.
    </p>

    <form name="fboardpassword" method="post" action="<? echo $action; ?>">
    <input type="hidden" name="w" value="<?=$w?>">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="wr_id" value="<?=$wr_id?>">
    <input type="hidden" name="comment_id" value="<?=$comment_id?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">

    <fieldset>
        <label for="password_wr_password">패스워드<strong class="sound_only">필수</strong></label>
        <input type="password" id="password_wr_password" name="wr_password" class="fieldset_input required" maxLength="20" size="15" required title="패스워드">
        <input type="submit" class="fieldset_submit" value="확인">
    </fieldset>

    </form>
</div>

<script>
document.fboardpassword.wr_password.focus();
</script>
