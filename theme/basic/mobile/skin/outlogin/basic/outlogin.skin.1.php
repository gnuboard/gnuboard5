<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<aside id="ol_before" class="ol">
    <h2>회원로그인</h2>
    <a href="<?php echo G5_BBS_URL ?>/login.php" class="btn_b01">로그인</a>
	<a href="<?php echo G5_BBS_URL ?>/register.php" class="btn_b02">회원가입</a>
</aside>

<!-- 로그인 전 외부로그인 끝 -->
