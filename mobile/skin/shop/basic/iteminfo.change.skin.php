<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<h1 id="win_title">교환/반품</h1>

<div class="win_desc">
    <?php echo conv_content($default['de_change_content'], 1); ?>
</div>