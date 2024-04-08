<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨

$thumb_width = 297;
$thumb_height = 212;
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

<div class="pic_li_lt relative w-1/3 px-2.5 bg-white dark:bg-zinc-900">
    <h2 class="lat_title block leading-45 text-sm font-bold text-black dark:text-white"><a href="<?php echo get_pretty_url($bo_table); ?>" class="relative inline-block"><?php echo $bo_subject ?></a></h2>
    <ul>
    <?php
    for ($i=0; $i<$list_count; $i++) {
        
        $img_link_html = '';

        $wr_href = get_pretty_url($bo_table, $list[$i]['wr_id']);
        
        if( $i === 0 ) {
            $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);

            if(isset($thumb['src']) && $thumb['src']) {
                $img = $thumb['src'];
            } else {
                $img = G5_IMG_URL.'/no_img.png';
                $thumb['alt'] = '이미지가 없습니다.';
            }
            $img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
            $img_link_html = '<a href="'.$wr_href.'" class="lt_img block mb-2.5">'.run_replace('thumb_image_tag', $img_content, $thumb).'</a>';
        }
    ?>
        <li class="border-b border-solid border-gray-200 mb-2.5 dark:border-mainborder">
            <?php echo $img_link_html; ?>
            <?php
            if ($list[$i]['icon_secret']) echo "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i><span class=\"sound_only\">비밀글</span> ";
 
            echo "<a href=\"".$wr_href."\" class=\"pic_li_tit font-bold text-sm leading-5 align-middle hover:text-blue-500 dark:text-white\"> ";
            if ($list[$i]['is_notice'])
                echo "<strong>".$list[$i]['subject']."</strong>";
            else
                echo $list[$i]['subject'];

            echo "</a>";
			
			if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
            if ($list[$i]['icon_hot']) echo "<span class=\"hot_icon\">H<span class=\"sound_only\">인기글</span></span>";

            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

            //echo $list[$i]['icon_reply']." ";
            // if ($list[$i]['icon_file']) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ;
            //if ($list[$i]['icon_link']) echo " <i class=\"fa fa-link\" aria-hidden=\"true\"></i>" ;

            if ($list[$i]['comment_cnt'])  echo "
            <span class=\"lt_cmt\">".$list[$i]['wr_comment']."</span>";

            ?>

            <div class="lt_info py-2.5">
              <span class="lt_nick dark:text-white"><?php echo $list[$i]['name'] ?></span>
            	<span class="lt_date text-gray-400"><?php echo $list[$i]['datetime2'] ?></span>              
            </div>
        </li>
    <?php }  ?>
    <?php if ($list_count == 0) { //게시물이 없을 때  ?>
    <li class="empty_li">게시물이 없습니다.</li>
    <?php }  ?>
    </ul>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more absolute block top-2.5 right-2.5 leading-6 text-blue-500 hover:text-blue-950"><span class="sound_only"><?php echo $bo_subject ?></span>더보기</a>

</div>
