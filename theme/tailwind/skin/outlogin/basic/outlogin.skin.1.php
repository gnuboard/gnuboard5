<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
?>

<!-- 로그인 전 아웃로그인 시작 { -->
<section id="ol_before" class="ol relative border border-solid border-gray-200 rounded mb-3.5 dark:border-mainborder dark:text-white">
	<div id="ol_be_cate" class="flex">
    	<h2 class="w-1/2 text-center py-3.5"><span class="sound_only">회원</span>로그인</h2>
    	<a href="<?php echo G5_BBS_URL ?>/register.php" class="join w-1/2 text-center bg-gray-100 text-gray-500 py-3.5 dark:bg-zinc-800 dark:text-zinc-400">회원가입</a>
    </div>
    <form name="foutlogin" action="<?php echo $outlogin_action_url ?>" onsubmit="return fhead_submit(this);" method="post" autocomplete="off" class="p-5">
    <fieldset>
        <div class="ol_wr relative mb-1.5">
            <input type="hidden" name="url" value="<?php echo $outlogin_url ?>">
            <label for="ol_id" id="ol_idlabel" class="sound_only">회원아이디<strong>필수</strong></label>
            <input type="text" id="ol_id" name="mb_id" required maxlength="20" placeholder="아이디" class="block w-full border border-solid border-gray-300 h-9 rounded px-2.5 mb-1.5 dark:bg-zinc-800 dark:border-mainborder">
            <label for="ol_pw" id="ol_pwlabel" class="sound_only">비밀번호<strong>필수</strong></label>
            <input type="password" name="mb_password" id="ol_pw" required maxlength="20" placeholder="비밀번호" class="block w-full border border-solid border-gray-300 h-9 rounded px-2.5 mb-1.5 dark:bg-zinc-800 dark:border-mainborder">
            <input type="submit" id="ol_submit" value="로그인" class="w-full h-9 bg-blue-500 text-white font-bold text-sm rounded">
        </div>
        <div class="ol_auto_wr flex justify-between"> 
            <div id="ol_auto" class="chk_box relative leading-5 mt-1">
                <input type="checkbox" name="auto_login" value="1" id="auto_login" class="selec_chk absolute top-0 left-0 w-0 h-0 opacity-0 outline-0 -z-10 overflow-hidden">
                <label for="auto_login" id="auto_login_label" class="text-gray-600 align-baseline pl-5 hover:text-blue-500 group checked:text-black dark:text-zinc-500"><span class="absolute top-0.5 left-0 block w-3.5 h-3.5 bg-white border border-gray-300 rounded-sm m-0"></span>자동로그인</label>
            </div>
            <div id="ol_svc" class="leading-5">
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php" class="inline-block border border-solid border-gray-300 text-blue-500 rounded px-1 py-0.5 dark:border-mainborder">ID/PW 찾기</a>
            </div>
        </div>
        <?php
        // 소셜로그인 사용시 소셜로그인 버튼
        @include_once(get_social_skin_path().'/social_login.skin.php');
        ?>

    </fieldset>
    </form>
</section>

<script>
jQuery(function($) {

    var $omi = $('#ol_id'),
        $omp = $('#ol_pw'),
        $omi_label = $('#ol_idlabel'),
        $omp_label = $('#ol_pwlabel');

    $omi_label.addClass('ol_idlabel');
    $omp_label.addClass('ol_pwlabel');

    $("#auto_login").click(function(){
        if ($(this).is(":checked")) {
            if(!confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?"))
                return false;
        }
    });
});

function fhead_submit(f)
{
    if( $( document.body ).triggerHandler( 'outlogin1', [f, 'foutlogin'] ) !== false ){
        return true;
    }
    return false;
}
</script>
<!-- } 로그인 전 아웃로그인 끝 -->
