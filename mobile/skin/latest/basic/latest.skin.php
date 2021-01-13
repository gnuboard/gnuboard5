<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.css">', 10);

add_javascript('<script src="'.G5_JS_URL.'/tooltipster/tooltipster.bundle.min.js"></script>', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/tooltipster/tooltipster.bundle.min.css">', 11);
add_javascript('<script src="'.$latest_skin_url.'/latest.carousel.js?v2"></script>', 12);

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css?v2">', 1);
$thumb_width = 138;
$thumb_height = 80;
$list_count = (is_array($list) && $list) ? count($list) : 0;
$divisor_count = 4;
$start_page_num = $list_count ? '1' : '0';
$is_show_next_prev = ($list_count > 4) ? 1 : 0;
?>

<div class="lt owl-carousel-wrap">
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_title"><strong><?php echo $bo_subject; ?></strong></a>
    <div class="<?php echo $list_count ? 'latest-sel' : ''; ?>">
        <ul class="item">
            <?php
            for ($i=0; $i<$list_count; $i++) {
            $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);
            $img = $thumb['src'] ? $thumb['src'] : '';
            $img_content = $img ? '<img src="'.$img.'" alt="'.$thumb['alt'].'" >' : '';
            $wr_href = get_pretty_url($bo_table, $list[$i]['wr_id']);

            $echo_ul = ( $i && (($i % $divisor_count) === 0) ) ? '</ul><ul class="item">'.PHP_EOL : '';

            echo $echo_ul;
            ?>
            <li>
                <?php
                //echo $list[$i]['icon_reply']." ";
                
                if( $img_content ){
                    echo "<a href=\"".$wr_href."\" class=\"lt_thumb\">".run_replace('thumb_image_tag', $img_content, $thumb)."</a> ";
                }
                
                echo "<a href=\"".$wr_href."\" class=\"lt_tit\">";
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
    <?php if ($is_show_next_prev){  // $divisor_count 이상의 값이 있을경우에만 출력 ?>
	<div class="lt_page">
		<button class="lt_page_prev"><span class="sound_only">이전페이지</span><i class="fa fa-caret-left" aria-hidden="true"></i></button>
		<span class="page_print"><b><?php echo $start_page_num; ?></b>/<?php echo $start_page_num; ?></span>
		<button class="lt_page_next"><span class="sound_only">다음페이지</span><i class="fa fa-caret-right" aria-hidden="true"></i></button>
	</div>
    <?php } ?>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more"><span class="sound_only"><?php echo $bo_subject ?></span>전체보기</a>
</div>