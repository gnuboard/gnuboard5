<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/tail.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    include_once(G5_THEME_SHOP_PATH.'/shop.tail.php');
    return;
}
?>

    </div>
    <div id="aside" class="w-60 h-full p-0 my-5 ml-5">
        <?php echo outlogin('theme/basic'); // 외부 로그인, 테마의 스킨을 사용하려면 스킨을 theme/basic 과 같이 지정 ?>
        <?php echo poll('theme/basic'); // 설문조사, 테마의 스킨을 사용하려면 스킨을 theme/basic 과 같이 지정 ?>
    </div>
</div>

</div>
<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="ft" class="bg-mainbg mx-auto text-center">
    <div id="ft_wr" class="relative flex max-w-screen-xl w-full text-left py-10 mx-auto">
        <div id="ft_link" class="ft_cnt w-1/4 px-5 text-left">
            <a href="<?php echo get_pretty_url('content', 'company'); ?>" class="block text-white leading-loose font-bold">회사소개</a>
            <a href="<?php echo get_pretty_url('content', 'privacy'); ?>" class="block text-white leading-loose font-bold">개인정보처리방침</a>
            <a href="<?php echo get_pretty_url('content', 'provision'); ?>" class="block text-white leading-loose font-bold">서비스이용약관</a>
            <a href="<?php echo get_device_change_url(); ?>" class="block text-white leading-loose font-bold">모바일버전</a>
        </div>
        <div id="ft_company" class="ft_cnt w-1/4 px-5 font-normal text-white leading-loose">
        	<h2 class="text-sm mb-5">사이트 정보</h2>
	        <p class="ft_info">
	        	회사명 : 회사명 / 대표 : 대표자명<br>
				주소  : OO도 OO시 OO구 OO동 123-45<br>
				사업자 등록번호  : 123-45-67890<br>
				전화 :  02-123-4567  팩스  : 02-123-4568<br>
				통신판매업신고번호 :  제 OO구 - 123호<br>
				개인정보관리책임자 :  정보책임자명<br>
			</p>
	    </div>
        <?php
        //공지사항
        // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
        // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
        // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
        echo latest('theme/notice', 'notice', 4, 13);
        ?>
        
		<?php echo visit('theme/basic'); // 접속자집계, 테마의 스킨을 사용하려면 스킨을 theme/basic 과 같이 지정 ?>
	</div>      
        <!-- <div id="ft_catch"><img src="<?php echo G5_IMG_URL; ?>/ft_logo.png" alt="<?php echo G5_VERSION ?>"></div> -->
        <div id="ft_copy" class="text-center max-w-screen-xl w-full mx-auto py-5 text-gray-500 text-xs border-t border-mainborder">Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.</div>
    
    
    <button type="button" id="top_btn" class="fixed bottom-5 right-5 w-14 leading-52 border-2 border-solid border-gray-700 text-gray-700 text-center text-sm bg-white bg-opacity-50 hover:border-blue-600 hover:bg-blue-600 hover:text-white">
    	<i class="fa fa-arrow-up" aria-hidden="true"></i><span class="sound_only">상단으로</span>
    </button>
    <button type="button" id="darkmode-toggle-switch" class="group fixed bottom-5 right-20 flex justify-center items-center w-14 h-14 border-2 border-solid border-gray-700 text-center text-sm bg-white bg-opacity-50 hover:border-blue-600 hover:bg-blue-600">
      <svg class="dark:hidden" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
        <path class="fill-gray-500 group-hover:fill-white" d="M7 0h2v2H7zM12.88 1.637l1.414 1.415-1.415 1.413-1.413-1.414zM14 7h2v2h-2zM12.95 14.433l-1.414-1.413 1.413-1.415 1.415 1.414zM7 14h2v2H7zM2.98 14.364l-1.413-1.415 1.414-1.414 1.414 1.415zM0 7h2v2H0zM3.05 1.706 4.463 3.12 3.05 4.535 1.636 3.12z" />
        <path class="fill-gray-700 group-hover:fill-white" d="M8 4C5.8 4 4 5.8 4 8s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4Z" />
      </svg>
      <svg class="hidden dark:block" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
        <path class="fill-gray-700 group-hover:fill-white" d="M6.2 1C3.2 1.8 1 4.6 1 7.9 1 11.8 4.2 15 8.1 15c3.3 0 6-2.2 6.9-5.2C9.7 11.2 4.8 6.3 6.2 1Z" />
        <path class="fill-gray-500 group-hover:fill-white" d="M12.5 5a.625.625 0 0 1-.625-.625 1.252 1.252 0 0 0-1.25-1.25.625.625 0 1 1 0-1.25 1.252 1.252 0 0 0 1.25-1.25.625.625 0 1 1 1.25 0c.001.69.56 1.249 1.25 1.25a.625.625 0 1 1 0 1.25c-.69.001-1.249.56-1.25 1.25A.625.625 0 0 1 12.5 5Z" />
      </svg>
    </button>
    <script>
    $(function() {
        $("#top_btn").on("click", function() {
            $("html, body").animate({scrollTop:0}, '500');
            return false;
        });
    });
    </script>
</div>

<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});

// 다크모드 설정
// Dark mode 상태 확인 함수
function isDarkModeEnabled() {
  const isUserColorTheme = localStorage.getItem('theme');
  if (isUserColorTheme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  document.documentElement.classList.add('dark')
} else {
  document.documentElement.classList.remove('dark')
}
}

document.addEventListener('DOMContentLoaded', function () {
  isDarkModeEnabled();
  const darkmodeBtn = document.querySelector('#darkmode-toggle-switch');
  darkmodeBtn?.addEventListener('click', function () {
    const currentTheme = localStorage.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', currentTheme);
    if(currentTheme === 'dark') {
      document.documentElement.classList.add('dark')
    } else if(currentTheme === 'light'){
      document.documentElement.classList.remove('dark')
    }
  });
});

</script>

<?php
include_once(G5_THEME_PATH."/tail.sub.php");