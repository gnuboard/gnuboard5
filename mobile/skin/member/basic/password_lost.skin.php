<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<div id="find_info" class="new_win">
    <h1>아이디/패스워드 찾기</h1>

    <form name="fpasswordlost" method="post" action="<?=$action_url?>" onsubmit="return fpasswordlost_submit(this);" autocomplete="off">
    <fieldset id="find_info_fs">
        <p>
            회원가입 시 등록하신 이메일 주소를 입력해 주세요.<br>
            해당 이메일로 아이디와 패스워드 정보를 보내드립니다.
        </p>
        <label for="mb_email">E-mail 주소<strong class="sound_only">필수</strong></label>
        <input type="text" id="mb_email" name="mb_email" class="fs_input email" required size="30">
    </fieldset>
    <?=captcha_html(); ?>
    <div class="btn_win">
        <input type="submit" class="btn_submit" value="확인">
        <a href="javascript:window.close();" class="btn_cancel">창닫기</a>
    </div>
    </form>
</div>

<script>
function fpasswordlost_submit(f)
{
    <? echo chk_captcha_js(); ?>

    return true;
}

self.focus();
document.fpasswordlost.mb_email.focus();

$(function() {
    var sw = screen.width;
    var sh = screen.height;
    var cw = document.body.clientWidth;
    var ch = document.body.clientHeight;
    var top  = sh / 2 - ch / 2 - 100;
    var left = sw / 2 - cw / 2;
    moveTo(left, top);
});
</script>
