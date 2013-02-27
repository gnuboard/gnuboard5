<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<div id="mb_confirm">
    <h1><?=$g4['title']?></h1>

    <p>
        <strong>패스워드를 한번 더 입력해주세요.</strong>
        회원님의 정보를 안전하게 보호하기 위해 패스워드를 한번 더 확인합니다.
    </p>

    <form name="fmemberconfirm" method="post" onsubmit="return fmemberconfirm_submit(this);">
    <input type=hidden name="mb_id" value="<?=$member[mb_id]?>">
    <input type=hidden name="w" value="u">

    <fieldset>
        회원아이디
        <span id="mb_confirm_id"><?=$member[mb_id]?></span>

        <label for="confirm_mb_password">패스워드<strong class="sound_only">필수</strong></label>
        <input type="password" id="confirm_mb_password" name="mb_password" class="fs_input" maxLength="20" size="15" required>
        <input type="submit" id="btn_submit" class="fs_submit" value="확인">
    </fieldset>

    </form>

    <div class="btn_confirm">
        <a href="<?=G4_URL?>">메인으로 돌아가기</a>
    </div>

</div>

<script>
document.onload = document.fmemberconfirm.mb_password.focus();

function fmemberconfirm_submit(f)
{
    document.getElementById("btn_submit").disabled = true;

    f.action = "<?=$url?>";
    return true;
}
</script>
