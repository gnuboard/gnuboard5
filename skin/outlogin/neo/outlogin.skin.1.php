<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="ol_before" class="outlogin">
    <h2>멤버쉽</h2>
    <!-- 로그인 전 외부로그인 시작 -->
    <form name="foutlogin" method="post" action="<?=$outlogin_action_url?>" onsubmit="return fhead_submit(this);" autocomplete="off">
    <fieldset>
        <input type="hidden" name="url" value="<?=$outlogin_url?>">
        <label for="ol_id" id="ol_idlabel">회원아이디</label>
        <input type="text" id="ol_id" name="mb_id" maxlength="20" required title="회원아이디">
        <label for="ol_pw" id="ol_pwlabel">패스워드</label>
        <input type="password" id="ol_pw" name="mb_password" maxlength="20" required title="패스워드">
        <input type="submit" id="ol_submit" value="로그인">
        <ul>
            <li id="ol_auto">
                <input type="checkbox" id="auto_login" name="auto_login" value="1">
                <label for="auto_login" id="auto_login_label">자동로그인</label>
            </li>
            <li><a href="<?=$g4['bbs_url']?>/register.php">회원가입</a></li>
            <li><a href="<?=$g4['bbs_url']?>/password_lost.php" id="ol_password_lost">정보찾기</a></li>
        </ul>
    </fieldset>
    </form>
</section>

<script>
$(function(){
    $omi = $('#ol_id');
    $omp = $('#ol_pw');
    $omp.css('display','inline-block');
    $omp.css('width',121);
    $omi_label = $('#ol_idlabel');
    $omp_label = $('#ol_pwlabel');
    $omi_label.addClass('ol_idlabel');
    $omp_label.addClass('ol_pwlabel');
    $omi.focus(function() {
        $omi_label.css('visibility','hidden');
    });
    $omp.focus(function() {
        $omp_label.css('visibility','hidden');
    });
    $omi.blur(function() {
        $this = $(this);
        if($this.attr('id') == "ol_id" && $this.attr('value') == "") $omi_label.css('visibility','visible');
    });
    $omp.blur(function() {
        $this = $(this);
        if($this.attr('id') == "ol_pw" && $this.attr('value') == "") $omp_label.css('visibility','visible');
    });

    $("#auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function fhead_submit(f)
{
    return true;
}
</script>
<!-- 로그인 전 외부로그인 끝 -->
