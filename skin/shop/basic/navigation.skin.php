<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$is_item_view = (isset($it_id, $it['it_id']) && $it_id === $it['it_id']);
$navigation_ca_id = $is_item_view ? $it['ca_id'] : $ca_id;
$navi_datas = $navigation_ca_id ? get_shop_navigation_data(true, $navigation_ca_id) : array();

$location_class = array();
if($is_item_view){
    $location_class[] = 'view_location';    // view_location는 리스트 말고 상품보기에서만 표시
} else {
	$location_class[] = 'is_list is_right';    // view_location는 리스트 말고 상품보기에서만 표시
}

// add_stylesheet('css 구문', 출력순서);
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/shop.category.navigation.js"></script>', 10);
?>

<div id="sct_location" class="<?php echo implode(' ', $location_class);?>"> <!-- class="view_location" --> <!-- view_location는 리스트 말고 상품보기에서만 표시 -->
    <a href='<?php echo G5_SHOP_URL; ?>/' class="go_home"><span class="sound_only">메인으로</span><i class="fa fa-home" aria-hidden="true"></i></a>
    <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
    <?php if ( is_array($navi_datas) && $navi_datas ){ ?>

        <?php foreach ($navi_datas as $depth => $categories) {
            $selected_ca_id = substr($navigation_ca_id, 0, ($depth + 1) * 2);
            if ($depth > 0) { ?>
        <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
        <?php } ?>
        <select class="shop_hover_selectbox category<?php echo $depth + 1; ?>" aria-label="<?php echo $depth + 1; ?>단계 분류">
            <?php foreach ($categories as $data) { ?>
            <option value="<?php echo $data['ca_id']; ?>" data-url="<?php echo $data['url']; ?>"<?php if ($selected_ca_id === $data['ca_id']) echo ' selected'; ?>><?php echo get_text($data['ca_name']); ?></option>
            <?php } ?>
        </select>
        <?php } ?>
    <?php } else { ?>
        <?php echo get_text($g5['title']); ?>
    <?php } ?>
</div>
<script>
jQuery(function($){
    $(document).ready(function() {
        $("#sct_location select").on("change", function(e){
            var url = $(this).find(':selected').attr("data-url");
            
            if (typeof itemlist_ca_id != "undefined" && itemlist_ca_id === this.value) {
                return false;
            }

            window.location.href = url;
        });

		$("select.shop_hover_selectbox").shop_select_to_html();
    });
});
</script>