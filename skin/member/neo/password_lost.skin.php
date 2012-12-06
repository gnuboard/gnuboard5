<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<h1>아이디/패스워드 찾기</h1>

<form name="fpasswordlost" method="post" onsubmit="return fpasswordlost_submit(this);" autocomplete="off">
<fieldset>
    <legend>이메일 주소 입력</legend>
    <p>회원가입 시 등록하신 이메일 주소를 입력해 주시면, 해당 이메일로 아이디와 패스워드 정보를 보내드립니다.</p>
    <label for="mb_email">이메일 주소</label>
    <input type="text" id="mb_email" name="mb_email" required size="45">
    <img id="kcaptcha_image">
    <input type="text" name="wr_key" size="10" required>
</fieldset>
<div class="btn_confirm">
    <input type="submit" value="확인">
    <a href="javascript:window.close();">창닫기</a>
</div>
</form>

<script src="<?="$g4[path]/js/md5.js"?>"></script>
<script src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script>
function fpasswordlost_submit(f)
{
    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/password_lost2.php';";
    else
        echo "f.action = './password_lost2.php';";
    ?>

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
