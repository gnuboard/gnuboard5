<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 138;
$thumb_height = 80;
?>
<!-- 삭제가능 <script src="<?php echo G5_JS_URL; ?>/jquery.bxslider.js"></script> -->

<div class="lt">
    <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>" class="lt_title"><strong><?php echo $bo_subject ?></strong></a>
    <ul>
	    <?php // for ($i=0; $i<count($list); $i++) { ?>
	    <?php
	    for ($i=0; $i<count($list); $i++) {
	    $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);
	
	    if($thumb['src']) {
	        $img = $thumb['src'];
		}
	    $img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
	    ?>
	    	
        <li>
            <?php
            //echo $list[$i]['icon_reply']." ";
            
            // ************* 이미지가 없을 경우 코드 자체 표시 안되게 해주세요 (적용된 css 때문에 레이아웃이 틀어짐) *************
            echo "<a href=\"".$list[$i]['href']."\" class=\"lt_thumb\">".$img_content."</a> "; 
			// ************* 이미지가 없을 경우 코드 자체 표시 안되게 해주세요 (적용된 css 때문에 레이아웃이 틀어짐) *************
			
			
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
    <?php } ?>
    <?php if (count($list) == 0) { //게시물이 없을 때 ?>
    <li class="empty_li">게시물이 없습니다.</li>
    <?php } ?>
    </ul>
	<div class="lt_page">
		<button class="lt_page_prev"><span class="sound_only">이전페이지</span><i class="fa fa-caret-left" aria-hidden="true"></i></button>
		<span><b>1</b>/3</span>
		<button class="lt_page_next"><span class="sound_only">다음페이지</span><i class="fa fa-caret-right" aria-hidden="true"></i></button>
	</div>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more"><span class="sound_only"><?php echo $bo_subject ?></span>전체보기</a>
</div>

<!-- 삭제가능
	<script>
    $(document).ready(function(){
      $('.lt').bxSlider();
    });
</script> -->
