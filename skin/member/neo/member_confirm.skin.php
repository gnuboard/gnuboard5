<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<script src="<?=$g4[path]?>/js/capslock.js"></script>

<form name="fmemberconfirm" method="post" onsubmit="return fmemberconfirm_submit(this);">
<input type=hidden name="mb_id" value="<?=$member[mb_id]?>">
<input type=hidden name="w" value="u">
<fieldset id="member_confirm">
    <legend>패스워드 확인</legend>

    <p>회원님의 정보를 안전하게 보호하기 위해 패스워드를 한번 더 확인합니다.</p>

    회원아이디
    <span id="member_confirm_id"><?=$member[mb_id]?></span>

    <label for="confirm_mb_password">패스워드</label>
    <input type="password" id="confirm_mb_password" name="mb_password" class="fieldset_input" maxLength="20" size="15" required onkeypress="check_capslock('confirm_mb_password');">
    <input type="submit" id="btn_submit" class="fieldset_submit" value="확인">
</fieldset>
</form>

<script>
document.onload = document.fmemberconfirm.mb_password.focus();

function fmemberconfirm_submit(f)
{
    document.getElementById("btn_submit").disabled = true;

    f.action = "<?=$url?>";
    return true;
}
</script>
