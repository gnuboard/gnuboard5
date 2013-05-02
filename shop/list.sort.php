<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sct_sort_href = $_SERVER['PHP_SELF'].'?ca_id='.$ca_id.'&amp;skin='.$skin.'&amp;ev_id='.$ev_id.'&amp;sort=';
?>

<section id="sct_sort">
    <h2>상품 정렬</h2>
    <div>
        상품 <b><?php echo number_format($total_count); ?></b>개
    </div>

    <ul>
        <li><a href="<?php echo $sct_sort_href; ?>it_amount desc" class="btn01">낮은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_amount asc" class="btn01">높은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_name asc" class="btn01">상품명순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type1 desc" class="btn01">히트상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type2 desc" class="btn01">추천상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type3 desc" class="btn01">최신상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type4 desc" class="btn01">인기상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type5 desc" class="btn01">할인상품</a></li>
    </ul>
</section>