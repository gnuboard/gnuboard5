<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<div id="mb_confirm">
    <h1><?=$g4['title']?></h1>

    <p>
        <strong>패스워드를 한번 더 입력해주세요.</strong>
        회원님의 정보를 안전하게 보호하기 위해 패스워드를 한번 더 확인합니다.
    </p>

    <form name="fmemberconfirm" onsubmit="return fmemberconfirm_submit(this);" method="post">
    <input type="hidden" name="mb_id" value="<?=$member[mb_id]?>">
    <input type="hidden" name="w" value="u">

    <fieldset>
        회원아이디
        <span id="mb_confirm_id"><?=$member[mb_id]?></span>
        <input type="password" name="mb_password" id="mb_confirm_pw" title="패스워드(필수)"placeholder="패스워드(필수)" required class="frm_input" size="15" maxLength="20">
        <input type="submit" value="확인" id="btn_submit" class="btn_submit">
    </fieldset>

    </form>

    <div class="btn_confirm">
        <a href="<?=G4_URL?>">메인으로 돌아가기</a>
    </div>

</div>

<script>
function fmemberconfirm_submit(f)
{
    document.getElementById("btn_submit").disabled = true;

    f.action = "<?=$url?>";
    return true;
}
</script>
