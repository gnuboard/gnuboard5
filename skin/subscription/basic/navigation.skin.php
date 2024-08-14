<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$navi_datas = $sc_ids = array();
$is_item_view = (isset($it_id) && isset($it) && isset($it['it_id']) && $it_id === $it['it_id']) ? true : false;

if( !$is_item_view && $sc_id ){
    $navi_datas = get_shop_navigation_data(true, $sc_id);
    $sc_ids = array(
        'sc_id' => substr($sc_id,0,2),
        'sc_id2' => substr($sc_id,0,4),
        'sc_id3' => substr($sc_id,0,6),
        );
} else if( $is_item_view && isset($it) && is_array($it) ) {
    $navi_datas = get_shop_navigation_data(true, $it['sc_id']);
    $sc_ids = array(
        'sc_id' => substr($it['sc_id'],0,2),
        'sc_id2' => substr($it['sc_id'],0,4),
        'sc_id3' => substr($it['sc_id'],0,6)
        );
}

$location_class = array();
if($is_item_view){
    $location_class[] = 'view_location';    // view_location는 리스트 말고 상품보기에서만 표시
} else {
	$location_class[] = 'is_list is_right';    // view_location는 리스트 말고 상품보기에서만 표시
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SUBSCRIPTION_CSS_URL.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/shop.category.navigation.js"></script>', 10);
?>

<div id="sct_location" class="<?php echo implode(' ', $location_class);?>"> <!-- class="view_location" --> <!-- view_location는 리스트 말고 상품보기에서만 표시 -->
    <a href='<?php echo G5_SUBSCRIPTION_URL; ?>/' class="go_home"><span class="sound_only">메인으로</span><i class="fa fa-home" aria-hidden="true"></i></a>
    <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
    <?php if ( is_array($navi_datas) && $navi_datas ){ ?>

        <?php if( isset($navi_datas[0]) && $navi_datas[0] ){ ?>
        <select class="shop_hover_selectbox category1">
            <?php foreach((array) $navi_datas[0] as $data ){ ?>
                <option value="<?php echo $data['sc_id']; ?>" data-url="<?php echo $data['url']; ?>" <?php if($sc_ids['sc_id'] === $data['sc_id']) echo 'selected'; ?>><?php echo $data['sc_name']; ?></option>
            <?php } ?>
        </select>
        <?php } ?>
        <?php if( isset($navi_datas[1]) && $navi_datas[1] ){ ?>
        <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
        <select class="shop_hover_selectbox category2">
            <?php foreach((array) $navi_datas[1] as $data ){ ?>
                <option value="<?php echo $data['sc_id']; ?>" data-url="<?php echo $data['url']; ?>" <?php if($sc_ids['sc_id2'] === $data['sc_id']) echo 'selected'; ?>><?php echo $data['sc_name']; ?></option>
            <?php } ?>
        </select>
        <?php } ?>
        <?php if( isset($navi_datas[2]) && $navi_datas[2] ){ ?>
        <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
        <select class="shop_hover_selectbox category3">
            <?php foreach((array) $navi_datas[2] as $data ){ ?>
                <option value="<?php echo $data['sc_id']; ?>" data-url="<?php echo $data['url']; ?>" <?php if($sc_ids['sc_id3'] === $data['sc_id']) echo 'selected'; ?>><?php echo $data['sc_name']; ?></option>
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
            
            if (typeof itemlist_sc_id != "undefined" && itemlist_sc_id === this.value) {
                return false;
            }

            window.location.href = url;
        });

		$("select.shop_hover_selectbox").shop_select_to_html();
    });
});
</script>