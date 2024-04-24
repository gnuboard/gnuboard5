<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
?>

<aside id="ol_before" class="ol relative bg-zinc-800 text-left px-3 py-4 dark:bg-zinc-900">
    <h2 class="blind">회원로그인</h2>
    <a href="<?php echo G5_BBS_URL ?>/login.php" class="btn_b01 inline-block !bg-blue-500 !text-white rounded no-underline align-middle p-3 py-1.5">로그인</a>
	<a href="<?php echo G5_BBS_URL ?>/register.php" class="btn_b02 inline-block !bg-transparent !text-white rounded no-underline align-middle p-3 py-1.5">회원가입</a>
</aside>

<!-- 로그인 전 외부로그인 끝 -->
