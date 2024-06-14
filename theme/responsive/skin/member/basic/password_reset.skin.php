<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $member_skin_url . '/style.css">', 0);
?>

<!-- 비밀번호 재설정 시작 { -->
<div id="pw_reset" class="new_win">
    <div class="new_win_con">
        <form name="fpasswordreset" action="<?php echo $action_url; ?>" onsubmit="return fpasswordreset_submit(this);" method="post" autocomplete="off">
            <fieldset id="info_fs">
                <p>새로운 비밀번호를 입력해주세요.</p>
                <label for="mb_id" class="sound_only">아이디</label>
                <br>
                <b>회원 아이디 : <?php echo get_text($_POST['mb_id']); ?></b>
                <label for="mb_pw" class="sound_only">새 비밀번호<strong class="sound_only">필수</strong></label>
                <input type="password" name="mb_password" id="mb_pw" required class="required frm_input full_input" size="30" placeholder="새 비밀번호">
                <label for="mb_pw2" class="sound_only">새 비밀번호 확인<strong class="sound_only">필수</strong></label>
                <input type="password" name="mb_password_re" id="mb_pw2" required class="required frm_input full_input" size="30" placeholder="새 비밀번호 확인">
            </fieldset>
            <div class="win_btn">
                <button type="submit" class="btn_submit">확인</button>
            </div>
        </form>
    </div>
</div>

<script>
function fpasswordreset_submit(f) {
    if ($("#mb_pw").val() == $("#mb_pw2").val()) {
        alert("비밀번호 변경되었습니다. 다시 로그인해 주세요.");
    } else {
        alert("새 비밀번호와 비밀번호 확인이 일치하지 않습니다.");
        return false;
    }
}
</script>
<!-- } 비밀번호 재설정 끝 -->