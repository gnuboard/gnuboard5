<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원가입약관 동의 시작 { -->
<div class="register mx-auto">

    <form  name="fregister" id="fregister" action="<?php echo $register_action_url ?>" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off">

    <p class="relative text-center text-whtie text-sm text-white bg-rose-400 rounded font-bold mb-2 py-4 before:absolute before:top-0 before:left-0 before:w-1 before:h-full before:rounded-s before:bg-rose-500"><i class="fa fa-check-circle text-sm align-middle" aria-hidden="true"></i> 회원가입약관 및 개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.</p>
    
    <?php
    // 소셜로그인 사용시 소셜로그인 버튼
    @include_once(get_social_skin_path().'/social_register.skin.php');
    ?>
    <section id="fregister_term" class="relative border border-gray-200 rounded my-2 mx-auto dark:border-mainborder dark:bg-zinc-900 dark:text-white">
        <h2 class="text-left border-b border-solid border-gray-200 text-sm p-5 dark:border-mainborder">회원가입약관</h2>
        <textarea class="block w-full h-40 bg-white border-0 leading-relaxed p-5 dark:bg-zinc-900" readonly><?php echo get_text($config['cf_stipulation']) ?></textarea>
        <fieldset class="fregister_agree absolute top-0 right-0">
            <input type="checkbox" name="agree" value="1" id="agree11" class="selec_chk">
            <label for="agree11" class="text-gray-600 hover:text-blue-500"><span class="absolute top-5 right-4 w-4 h-4 block bg-white border border-gray-200 rounded"></span><b class="sound_only">회원가입약관의 내용에 동의합니다.</b></label>
        </fieldset>
    </section>

    <section id="fregister_private" class="relative border border-gray-200 rounded my-2 mx-auto dark:border-mainborder dark:bg-zinc-900 dark:text-white">
        <h2 class="text-left border-b border-solid border-gray-200 text-sm p-5 dark:border-mainborder">개인정보 수집 및 이용</h2>
        <div class="bg-white p-5 dark:bg-zinc-900">
            <table class="w-full border-collapse text-xs">
                <caption class="blind">개인정보 수집 및 이용</caption>
                <thead>
                <tr>
                    <th class="bg-gray-100 w-1/3 text-black p-3 border border-gray-200 dark:bg-zinc-800 dark:border-mainborder dark:text-white">목적</th>
                    <th class="bg-gray-100 w-1/3 text-black p-3 border border-gray-200 dark:bg-zinc-800 dark:border-mainborder dark:text-white">항목</th>
                    <th class="bg-gray-100 w-1/3 text-black p-3 border border-gray-200 dark:bg-zinc-800 dark:border-mainborder dark:text-white">보유기간</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">이용자 식별 및 본인여부 확인</td>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">아이디, 이름, 비밀번호<?php echo ($config['cf_cert_use'])? ", 생년월일, 휴대폰 번호(본인인증 할 때만, 아이핀 제외), 암호화된 개인식별부호(CI)" : ""; ?></td>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">회원 탈퇴 시까지</td>
                </tr>
                <tr>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">고객서비스 이용에 관한 통지,<br>CS대응을 위한 이용자 식별</td>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">연락처 (이메일, 휴대전화번호)</td>
                    <td class="border border-gray-200 border-t-0 p-3 dark:border-mainborder">회원 탈퇴 시까지</td>
                </tr>
                </tbody>
            </table>
        </div>

        <fieldset class="fregister_agree absolute top-0 right-0">
            <input type="checkbox" name="agree2" value="1" id="agree21" class="selec_chk">
            <label for="agree21" class="text-gray-600 hover:text-blue-500"><span class="absolute top-5 right-4 w-4 h-4 block bg-white border border-gray-200 rounded"></span><b class="sound_only">개인정보 수집 및 이용의 내용에 동의합니다.</b></label>
       </fieldset>
    </section>
	
    <div id="fregister_chkall" class="chk_all fregister_agree relative text-center bg-gray-100 border border-gray-200 rounded mb-4 py-4 dark:bg-zinc-800 dark:border-mainborder">
      <input type="checkbox" name="chk_all" id="chk_all" class="selec_chk">
      <label for="chk_all" class="text-gray-600 hover:text-blue-500 dark:text-white dark:hover:text-blue-500"><span class="absolute top-5 right-4 w-4 h-4 block bg-white border border-gray-200 rounded"></span>회원가입 약관에 모두 동의합니다</label>
    </div>
	    
    <div class="btn_confirm flex">
    	<a href="<?php echo G5_URL ?>" class="btn_close w-1/2 h-12 font-bold text-sm dark:bg-zinc-900 dark:border-mainborder dark:text-white">취소</a>
      <button type="submit" class="btn_submit w-1/2 h-12 font-bold text-sm ml-2">회원가입</button>
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
            alert("개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree2.focus();
            return false;
        }

        return true;
    }
    
    jQuery(function($){
        // 모두선택
        $("input[name=chk_all]").click(function() {
            if ($(this).prop('checked')) {
                $("input[name^=agree]").prop('checked', true);
            } else {
                $("input[name^=agree]").prop("checked", false);
            }
        });
    });

    </script>
</div>
<!-- } 회원가입 약관 동의 끝 -->
