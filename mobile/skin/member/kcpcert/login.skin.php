<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div id="mb_login">
    <h1><?=$g4['title']?></h1>

    <form name="flogin" action="<?=$login_action_url?>" onsubmit="return flogin_submit(this);" method="post">
    <input type="hidden" name="url" value='<?=$login_url?>'>

    <fieldset>
        <input type="text" name="mb_id" id="login_id" title="아이디(필수)" placeholder="아이디(필수)" required class="frm_input required" maxLength="20">
        <input type="password" name="mb_password" id="login_pw" title="패스워드(필수)" placeholder="패스워드(필수)" required class="frm_input required" maxLength="20">
        <input type="submit" value="로그인" class="btn_submit">
        <input type="checkbox" name="auto_login" id="login_auto_login">
        <label for="login_auto_login">자동로그인</label>
    </fieldset>

    <section>
        <h2>회원로그인 안내</h2>
        <p>
            회원아이디 및 패스워드가 기억 안나실 때는 아이디/패스워드 찾기를 이용하십시오.<br>
            아직 회원이 아니시라면 회원으로 가입 후 이용해 주십시오.
        </p>
        <div>
            <a href="<?=$g4['bbs_url']?>/password_lost.php" target="win_password_lost" id="login_password_lost" class="btn02">아이디 패스워드 찾기</a>
            <a href="./register.php" class="btn01">회원 가입</a>
        </div>
    </section>

    <div class="btn_confirm">
        <a href="<?=G4_URL?>/">메인으로 돌아가기</a>
    </div>

    </form>

</div>

<script>
$(function(){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    return true;
}
</script>
