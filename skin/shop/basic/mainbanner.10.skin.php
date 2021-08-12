<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.css">', 10);

$max_width = $max_height = 0;
$bn_first_class = ' class="bn_first"';
$bn_slide_btn = '';
$bn_sl = ' class="bn_sl"';
$main_banners = array();

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $main_banners[] = $row;

    // 테두리 있는지
    $bn_border  = ($row['bn_border']) ? ' class="sbn_border"' : '';;
    // 새창 띄우기인지
    $bn_new_win = ($row['bn_new_win']) ? ' target="_blank"' : '';

    $bimg = G5_DATA_PATH.'/banner/'.$row['bn_id'];
    $item_html = '';

    if (file_exists($bimg))
    {
        $banner = '';
        $size = getimagesize($bimg);

        if($size[2] < 1 || $size[2] > 16)
            continue;

        if($max_width < $size[0])
            $max_width = $size[0];

        if($max_height < $size[1])
            $max_height = $size[1];

        $item_html .= '<div class="item">';
        if ($row['bn_url'][0] == '#')
            $banner .= '<a href="'.$row['bn_url'].'">';
        else if ($row['bn_url'] && $row['bn_url'] != 'http://') {
            $banner .= '<a href="'.G5_SHOP_URL.'/bannerhit.php?bn_id='.$row['bn_id'].'"'.$bn_new_win.'>';
        }
        $item_html .= $banner.'<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'?'.preg_replace('/[^0-9]/i', '', $row['bn_time']).'" width="'.$size[0].'" alt="'.get_text($row['bn_alt']).'"'.$bn_border.'>';
        if($banner)
            $item_html .= '</a>';
        $item_html .= '</div>';
    }
    
    $banner_style = $max_height ? 'style="min-height:'.($max_height + 25).'px"' : '';
    if ($i==0) echo '<div id="main_bn" '.$banner_style.'><div class="main_banner_owl owl-carousel">'.PHP_EOL;
    
    echo $item_html;
}

if ($i > 0) {
    echo '</div>'.PHP_EOL;
	
	echo '<div class="btn_wr"><a href="#" class="pager-prev"><i class="fa fa-angle-left"></i></a><div id="slide-counter"></div><a href="#" class="pager-next"><i class="fa fa-angle-right"></i></a> </div>'.PHP_EOL;
        echo '<div class="owl_pager">
    <ul class="carousel-custom-dots owl-dots">';
		$k = 0;
		foreach( $main_banners as $row ){
			echo '<li class="owl-dot"><a data-slide-index="'.$k.'" href="#">'.get_text($row['bn_alt']).'</a></li>'.PHP_EOL;
			$k++;
			}
		
    echo '</ul>
    </div>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
?>

<script>
jQuery(function($){

    function owl_show_page(event){

        if (event.item) {
            var count = event.item.count,
                item_index = event.item.index,
                index = 1;

            if( is_loop ){
                index = ( 1 + ( event.property.value - Math.ceil( event.item.count / 2 ) ) % event.item.count || 0 ) || 1;
            } else {
                index = event.item.index ? event.item.index + 1 : 1;
            }
            
            $(event.target).next(".btn_wr").find(".slide-index").text(index);
        }
    }

    var is_loop = true,
        item_totals = $('.main_banner_owl .item').length;

    if( item_totals ){
        $('#slide-counter').prepend('<strong class="slide-index current-index"></strong> / ')
        .append('<span class="total-slides">'+item_totals+'</span>');
    }

    var owl = $('.main_banner_owl').owlCarousel({
        items:1,
        loop:is_loop,
        margin:0,
        nav:false,
        autoHeight:true,
        autoplay:true,
        autoplayTimeout:5000,   // 5000은 5초
        autoplayHoverPause:true,
        dotsContainer: '.carousel-custom-dots',
        onChanged:function(event){
            owl_show_page(event);
        },
    });

    // Custom Navigation Events
    $(document).on("click", ".carousel-custom-dots a", function(e){
        e.preventDefault();
        owl.trigger('to.owl.carousel', [$(this).parent().index(), 300]);
    });

    $(document).on("click", ".btn_wr .pager-next", function(e){
        e.preventDefault();
        owl.trigger('next.owl.carousel');
    });

    $(document).on("click", ".btn_wr .pager-prev", function(e){
        e.preventDefault();
        owl.trigger('prev.owl.carousel');
    });
});
</script>
<?php
}