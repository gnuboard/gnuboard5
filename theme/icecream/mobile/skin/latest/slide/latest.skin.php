<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 805;
$thumb_height = 605;
?>

<div class="slide">
    <h2 class="sound_only"><a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a></h2>
    <ul class="lt_slide">
    <?php
    for ($i=0; $i<count($list); $i++) {
    $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);

    if($thumb['src']) {
        $img = $thumb['src'];
    } else {
        $img = G5_IMG_URL.'/no_img.png';
        $thumb['alt'] = '이미지가 없습니다.';
    }
    $img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
    ?>
        <li>
            <a href="<?php echo $list[$i]['href'] ?>" class="lt_img"><?php echo $img_content; ?></a>
            <div class="lt_txt">
                <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_cate <?php echo $list[$i]['bo_table'] ?>"><?php echo $bo_subject ?></a>
                <?php
                echo "<a href=\"".$list[$i]['href']."\" class=\"lt_tit\"> ";
                if ($list[$i]['is_notice'])
                    echo "<strong>".$list[$i]['subject']."</strong>";
                else
                    echo $list[$i]['subject'];
                echo "</a>";
                ?>
                <div class="lt_detail pc_view"> <?php echo get_text(cut_str(strip_tags($list[$i]['wr_content']), 80), 1); ?></div>
            </div>
        </li>
    <?php }  ?>
    <?php if (count($list) == 0) { //게시물이 없을 때  ?>
    <li class="empty_li">게시물이 없습니다.</li>
    <?php }  ?>
    </ul>
    <ul class="bx-pager">
        <?php
        for ($i=0; $i<count($list); $i++) {
       
        ?>
            <li>
                <?php
     
                echo "<a data-slide-index=\"".$i."\"><span class=\"txt_wr\"><span class=\"txt\"> ";

                echo $list[$i]['subject'];


                echo "</span></span></a>";

                ?>

            </li>
        <?php }  ?>
        <?php if (count($list) == 0) { //게시물이 없을 때  ?>
        <li class="empty_li">게시물이 없습니다.</li>
        <?php }  ?>
    </ul>


</div>

<script>
$(document).ready(function(){
    $('.lt_slide').bxSlider({
        speed:800,
        mode: 'fade',
        controls:true,
        pagerCustom: '.bx-pager',

    });
});

</script>
