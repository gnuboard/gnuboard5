<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원정보 찾기 시작 { -->
<!-- #TODO 본인인증 사용 시 아래 div에 cert 클래스 추가 -->
<div id="find_info" class="new_win">
    <div class="new_win_con">
        <form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
        <h3>이메일로 찾기</h3>
        <fieldset id="info_fs">
            <p>
                회원가입 시 등록하신 이메일 주소를 입력해 주세요.<br>
                해당 이메일로 아이디와 비밀번호 정보를 보내드립니다.
            </p>
            <input type="email" id="mb_email" name="mb_email" placeholder="이메일주소(필수)" required class="frm_input email">
        </fieldset>
        <?php echo captcha_html(); ?>
        
        <div class="win_btn">
            <button type="submit" class="btn_submit">인증메일 보내기</button>
        </div>
        </form>
    </div>

    <div class="new_win_con">
        <h3>본인인증으로 찾기</h3>
        <div class="cert_btn">
            <button type="submit" class="btn_close">토스 인증</button>
            <button type="submit" class="btn_close">PASS 인증</button>
            <button type="submit" class="btn_close">페이코 인증</button>
            <button type="submit" class="btn_close">금융인증서</button>
        </div>
        <div class="win_btn">
            <button type="submit" class="btn_submit">휴대폰 본인확인</button>
            <button type="submit" class="btn_submit">아이핀 본인확인</button>
        </div>
    </div>
</div>

<script>
function fpasswordlost_submit(f)
{
    <?php echo chk_captcha_js(); ?>

    return true;
}
</script>
