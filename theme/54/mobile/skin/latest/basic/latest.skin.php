<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.css">', 10);

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 138;
$thumb_height = 80;
$list_count = count($list);
$divisor_count = 4;
?>

<div class="lt owl-carousel-wrap">
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_title"><strong><?php echo $bo_subject; ?></strong></a>
    <div class="<?php echo $list_count ? 'owl-carousel' : ''; ?>">
        <ul class="item">
            <?php
            for ($i=0; $i<$list_count; $i++) {
            $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);
            $img = $thumb['src'] ? $thumb['src'] : '';
            $img_content = $img ? '<img src="'.$img.'" alt="'.$thumb['alt'].'" >' : '';
            
            $echo_ul = ( $i && (($i % $divisor_count) === 0) ) ? '</ul><ul class="item">'.PHP_EOL : '';

            echo $echo_ul;
            ?>
            <li>
                <?php
                //echo $list[$i]['icon_reply']." ";
                
                if( $img_content ){
                    echo "<a href=\"".$list[$i]['href']."\" class=\"lt_thumb\">".$img_content."</a> ";
                }
                
                echo "<a href=\"".$list[$i]['href']."\" class=\"lt_tit\">";
                if ($list[$i]['icon_secret']) echo "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i> ";
                if ($list[$i]['is_notice'])
                    echo "<strong>".$list[$i]['subject']."</strong>";
                else
                    echo $list[$i]['subject'];

                    // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
                    // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

                if ($list[$i]['icon_new']) echo " <span class=\"new_icon\">N</span>";
                if ($list[$i]['icon_file']) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ;
                if ($list[$i]['icon_link']) echo " <i class=\"fa fa-link\" aria-hidden=\"true\"></i>" ;
                if ($list[$i]['icon_hot']) echo " <i class=\"fa fa-heart\" aria-hidden=\"true\"></i>";
                
                if ($list[$i]['comment_cnt'])  echo "
                <span class=\"lt_cmt\"><span class=\"sound_only\">댓글</span>".$list[$i]['comment_cnt']."</span>";
                echo "</a>";
                ?>
               
                <div class="lt_info">
                    <?php echo $list[$i]['name'] ?>
                    <span class="lt_date">
                        <?php echo $list[$i]['datetime'] ?>
                    </span>
                </div>
            </li>
            <?php }     //end for ?>
            <?php if ($list_count == 0) { //게시물이 없을 때 ?>
            <li class="empty_li">게시물이 없습니다.</li>
            <?php }     //end if ?>
        </ul>
    </div>
	<div class="lt_page">
		<button class="lt_page_prev"><span class="sound_only">이전페이지</span><i class="fa fa-caret-left" aria-hidden="true"></i></button>
		<span class="page_print"></span>
		<button class="lt_page_next"><span class="sound_only">다음페이지</span><i class="fa fa-caret-right" aria-hidden="true"></i></button>
	</div>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more"><span class="sound_only"><?php echo $bo_subject ?></span>전체보기</a>
</div>

<?php
static $is_mobile_latest_script = 0;

if( ! $is_mobile_latest_script ){
?>
<script>
jQuery(function($){
    $(document).ready(function(){

        function owl_show_page(event){

            if (event.item) {
                var count = event.item.count,
                    item_index = event.item.index,
                    index = 1;

                if( item_index >= count ){
                    index = ( 1 + ( event.property.value - Math.ceil( event.item.count / 2 ) ) % event.item.count || 0 ) || 1;
                } else {
                    index = event.item.index ? event.item.index + 1 : 1;
                }

                var str = "<b>"+index+"</b>/"+count;

                $(event.target).next(".lt_page").find(".page_print").html(str);
            }
        }
        
        var carousels = [];

        $(".lt.owl-carousel-wrap").each(function(index, value) {

            var $this = $(this);

            carousels['sel' + index] = $this.find('.owl-carousel').owlCarousel({
                items:1,
                loop:true,
                center:true,
                autoHeight:true,
                dots:false,
                onChanged:function(event){
                    owl_show_page(event);
                },
            });

            $this.on("click", ".lt_page_next", function(e) {
                e.preventDefault();
                carousels['sel' + index].trigger('next.owl.carousel');
            });

            $this.on("click", ".lt_page_prev", function(e) {
                e.preventDefault();
                carousels['sel' + index].trigger('prev.owl.carousel');
            });

        });     // each
    });
});
</script>
<?php
$is_mobile_latest_script = 1;
} //end if
?>