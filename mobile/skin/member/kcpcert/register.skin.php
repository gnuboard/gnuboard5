<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<form id="fregister" name="fregister" method="POST" action="<?=$register_action_url?>" onsubmit="return fregister_submit(this);" autocomplete="off">

<section id="fregister_term">
    <h2>회원가입약관</h2>
    <textarea readonly><?=get_text($config['cf_stipulation'])?></textarea>
    <fieldset class="fregister_agree">
        <label for="agree11">회원가입약관의 내용에 동의합니다.</label>
        <input type="checkbox" id="agree11" name="agree" value="1">
    </fieldset>
</section>

<section id="fregister_private">
    <h2>개인정보수집이용안내</h2>
    <textarea readonly><?=get_text($config['cf_privacy'])?></textarea>
    <fieldset class="fregister_agree">
        <label for="agree21">개인정보수집이용안내의 내용에 동의합니다.</label>
        <input type="checkbox" id="agree21" name="agree2" value="1">
    </fieldset>
</section>

<div class="btn_confirm">
    <p>회원가입약관 및 개인정보수집이용안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.</p>
    <input type="submit" class="btn_submit" value="회원가입">
</div>

</form>

<script>
function fregister_submit(f) 
{
    if (!f.agree.checked) {
        alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        f.agree.focus();
        return false;
    }

    if (!f.agree2.checked) {
        alert("개인정보수집이용안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        f.agree2.focus();
        return false;
    }

    return true;
}
</script>
