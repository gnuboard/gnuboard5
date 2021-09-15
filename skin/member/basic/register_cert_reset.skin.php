<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $member_skin_url . '/style.css">', 0);
?>

<!-- 기존 회원 본인인증 시작 { -->
<div class="register_cert_reset">
    <form name="register_cert_reset" id="register_cert_reset" action="<?php echo $register_action_url ?>" onsubmit="return register_cert_reset_submit(this);" method="POST" autocomplete="off">
        <section id="register_cert_reset_private">
            <h2>추가 개인정보처리방침 안내</h2>
            <div>
                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <caption>추가 개인정보처리방침 안내</caption>
                        <thead>
                            <tr>
                                <th>목적</th>
                                <th>항목</th>
                                <th>보유기간</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>이용자 식별 및 본인여부 확인</td>
                                <td>생년월일, 암호화된 개인식별부호(CI)</td>
                                <td>회원 탈퇴 시까지</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <fieldset class="register_cert_reset_agree">
                <input type="checkbox" name="agree2" value="1" id="agree21" class="selec_chk">
                <label for="agree21"><span></span><b class="sound_only">개인정보 수집 및 이용의 내용에 동의합니다.</b></label>
            </fieldset>
        </section>

        <div id="find_info" class="new_win">
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
    </form>

    <script>
        function register_cert_reset_submit(f) {
            if (!f.agree2.checked) {
                alert("개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
                f.agree2.focus();
                return false;
            }

            return true;
        }
    </script>
</div>
<!-- } 기존 회원 본인인증 끝 -->