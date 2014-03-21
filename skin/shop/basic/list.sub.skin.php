<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>

<ul id="sct_lst">
    <li><button type="button" class="sct_lst_view sct_lst_list">리스트뷰<span></span></button></li>
    <li><button type="button" class="sct_lst_view sct_lst_gallery">갤러리뷰<span></span></button></li>
</ul>