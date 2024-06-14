<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sct_sort_href = $_SERVER['SCRIPT_NAME'].'?';

if($ca_id) {
	$shop_category_url = shop_category_url($ca_id);
    $sct_sort_href = (strpos($shop_category_url, '?') === false) ? $shop_category_url.'?1=1' : $shop_category_url;
} else if($ev_id) {
    $sct_sort_href .= 'ev_id='.$ev_id;
}

if($skin)
    $sct_sort_href .= '&amp;skin='.$skin;
$sct_sort_href .= '&amp;sort=';

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품 정렬 선택 시작 { -->
<section id="sct_sort">
    <h2>상품 정렬</h2>
    <button type="button" class="btn_sort">상품정렬 <i class="fa fa-caret-down" aria-hidden="true"></i></button>
    <ul>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=asc" >낮은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=desc">높은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_name&amp;sortodr=asc">상품명순</a></li>
    </ul>
</section>
<!-- } 상품 정렬 선택 끝 -->

<script>
$(".btn_sort").click(function(){
    $("#sct_sort ul").show();
});
$(document).mouseup(function (e){
    var container = $("#sct_sort ul");
    if( container.has(e.target).length === 0)
    container.hide();
});
</script>