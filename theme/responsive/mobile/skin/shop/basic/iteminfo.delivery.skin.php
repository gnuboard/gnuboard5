<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<h2 class="if_tit">배송정보</h2>

<div class="win_desc_if">
    <?php echo conv_content($default['de_baesong_content'], 1); ?>
</div>

<h2 class="if_tit">교환/반품</h2>

<div class="win_desc_if">
    <?php echo conv_content($default['de_change_content'], 1); ?>
</div>