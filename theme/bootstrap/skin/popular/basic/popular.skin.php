<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$popular_skin_url.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.css">', 10);
?>

<!-- 인기검색어 시작 { -->
<section id="popular">
    <h2>인기검색어</h2>
    <div class="popular_inner">
	    <ul>
	    <?php
	    if( isset($list) && is_array($list) ){
	        for ($i=0; $i<count($list); $i++) {
	        ?>
	        <li class="item"><a href="<?php echo G5_BBS_URL ?>/search.php?sfl=wr_subject&amp;sop=and&amp;stx=<?php echo urlencode($list[$i]['pp_word']) ?>"><?php echo get_text($list[$i]['pp_word']); ?></a></li>
	        <?php
	        }   //end for
	    }   //end if
	    ?>
	    </ul>
        <span class="popular_btns">
            <a href="#" class="pp-next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
            <a href="#" class="pp-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
        </span>
    </div>
</section>

<?php if (isset($list) && $list && is_array($list)) { //게시물이 있다면 ?>
<script>
jQuery(function($){
    
    var popular_el = ".popular_inner ul",
        p_width = $(popular_el).width(),
        c_width = 0;

    $(popular_el).children().each(function() {
        c_width += $(this).outerWidth( true );
    });

    if( c_width > p_width ){
        var $popular_btns = $(".popular_inner .popular_btns");
        $popular_btns.show();

        var p_carousel = $(popular_el).addClass("owl-carousel").owlCarousel({
            items:5,
            loop:true,
            nav:false,
            dots:false,
            autoWidth:true,
            mouseDrag:false,
        });

        $popular_btns.on("click", ".pp-next", function(e) {
            e.preventDefault();
            p_carousel.trigger('next.owl.carousel');
        })
        .on("click", ".pp-prev", function(e) {
            e.preventDefault();
            p_carousel.trigger('prev.owl.carousel');
        });
    }

});
</script>
<?php } ?>
<!-- } 인기검색어 끝 -->