<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sct_sort_href = $_SERVER['PHP_SELF'].'?ca_id='.$ca_id;
if($skin)
    $sct_sort_href .= '&amp;skin='.$skin;
$sct_sort_href .= '&amp;ev_id='.$ev_id.'&amp;sort=';
?>

<!-- 상품 정렬 선택 시작 { -->
<section id="sct_sort">
    <h2>상품 정렬</h2>

    <ul>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=asc" class="btn01">낮은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=desc" class="btn01">높은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_name&amp;sortodr=asc" class="btn01">상품명순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type1&amp;sortodr=desc" class="btn01">히트상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type2&amp;sortodr=desc" class="btn01">추천상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type3&amp;sortodr=desc" class="btn01">최신상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type4&amp;sortodr=desc" class="btn01">인기상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type5&amp;sortodr=desc" class="btn01">할인상품</a></li>
    </ul>
    <button type="button" class="sct_lst_view sct_lst_list">리스트뷰</button>
    <button type="button" class="sct_lst_view sct_lst_gallery">갤러리뷰</button>
</section>

<script>
$(function() {
    $("button.sct_lst_view").on("click", function() {
        if($(this).hasClass("sct_lst_gallery")) {
            $("ul.sct").removeClass("sct_13");
            set_cookie("ck_itemlist_type", "gallery", 1, g4_cookie_domain);
        } else {
            $("ul.sct").addClass("sct_13");
            set_cookie("ck_itemlist_type", "list", 1, g4_cookie_domain);
        }
    });
});
</script>
<!-- } 상품 정렬 선택 끝 -->