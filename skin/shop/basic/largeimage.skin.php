<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<div id="sit_pvi_nw" class="new_win">
    <h1 id="win_title">상품 이미지 새창 보기</h1>
    <div id="sit_pvi_nwbig">
        <?php
        $thumbnails = array();
        $images = get_item_images_info($row, $size, 60, 60);       // 숫자값은 썸네일 width, height
        
        foreach((array) $images as $i=>$imgs){
            $thumbnails[$i] = $imgs['thumb'];
        ?>
        <span>
            <a href="javascript:window.close();">
                <?php echo $imgs['imagehtml']; ?>
            </a>
        </span>
        <?php } ?>
    </div>

    <?php
    $total_count = count($thumbnails);
    $thumb_count = 0;
    if($total_count > 0) {
        echo '<ul>';
        foreach($thumbnails as $key=>$val) {
            echo '<li><a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it_id.'&amp;no='.$key.'" class="img_thumb">'.$val.'</a></li>';
        }
        echo '</ul>';
    }
    ?>
    <div class="win_btn">
        <button type="button" onclick="javascript:window.close();" class="btn_close">창닫기</button>
    </div>
</div>

<script>
jQuery(function($){

$.fn.imgLoad = function(callback) {
    return this.each(function() {
        if (callback) {
            if (this.complete || /*for IE 10-*/ $(this).height() > 0) {
                callback.apply(this);
            }
            else {
                $(this).on('load', function(){
                    callback.apply(this);
                });
            }
        }
    });
};

function largeimage_load(image_width){
    var w = image_width + 50;
    var h = $("#sit_pvi_nw").outerHeight(true) + $("#sit_pvi_nw h1").outerHeight(true);
    window.resizeTo(w, h);
}

    $("#sit_pvi_nwbig span:eq("+<?php echo ($no - 1); ?>+")").addClass("visible");

    // 창 사이즈 조절
    <?php if( isset($size[0]) && $size[0] ){ ?>
    $(window).on("load", function() {
        largeimage_load(<?php echo $size[0]; ?>);
    });
    <?php } else { ?>
        var is_load_end = false;
        $("#sit_pvi_nwbig img").imgLoad(function(){
            $(this).css({'max-width': '800px', 'height':'auto'});
            var image_width = $(this).width();

            if( image_width && ! is_load_end ){
                largeimage_load( image_width );
                is_load_end = true;
            }
        });
    <?php } ?>

    // 이미지 미리보기
    $(".img_thumb").bind("mouseover focus", function(){
        var idx = $(".img_thumb").index($(this));
        $("#sit_pvi_nwbig span.visible").removeClass("visible");
        $("#sit_pvi_nwbig span:eq("+idx+")").addClass("visible");
    });
});
</script>