<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<script src="<?=$g4[path]?>/js/capslock.js"></script>

<form name="fboardpassword" method="post" onsubmit="return fboardpassword_submit(this);">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="wr_id" value="<?=$wr_id?>">
<input type="hidden" name="comment_id" value="<?=$comment_id?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">

<fieldset>
    <legend>패스워드 확인</legend>
    <p>비밀글 기능으로 보호된 글입니다. 작성자와 관리자만 열람하실 수 있습니다. 작성자 본인이시라면 패스워드를 입력하세요.</p>
    <label for="password_wr_password">패스워드</label>
    <input type="password" id="password_wr_password" name="wr_password" maxLength="20" size="15" required onkeypress="check_capslock(event, 'password_wr_password');">
    <input type="submit" value="확인">
</fieldset>

</form>

<script>
document.fboardpassword.wr_password.focus();

function fboardpassword_submit(f)
{
    f.action = "<?=$action?>";
    return true;
}
</script>
