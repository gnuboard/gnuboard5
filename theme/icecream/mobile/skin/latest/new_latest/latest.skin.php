<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 140;
$thumb_height = 140;
?>
<ul class="new_latest">
<?php
$count = count($list);
for ($i=0; $i<$count; $i++) {
$bo_subject = mb_substr($list[$i]['bo_subject'],0,8,"utf-8"); // 게시판명 글자수
$thumb = get_list_thumbnail($list[$i]['bo_table'], $list[$i]['wr_id'], $thumb_width, $thumb_height);
if($thumb['src']) {
    $img = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$thumb_width.' hegiht="'.$thumb_hegiht.'">';
}
?>
<li class="<?php if ($thumb['src']) { ?>lt_liimg<?php } ?> ">

        <a href="<?php echo get_pretty_url($list[$i]['bo_table']); ?>" class="lt_cate <?php echo $list[$i]['bo_table'] ?>"><?php echo $bo_subject; ?></a>
        <a href="<?php echo $list[$i]['href']; ?>" class="lt_tit"><?php echo $list[$i]['subject']; ?></a>

    <div class="lt_info">
        <span class="lt_pf_img"><?php echo get_member_profile_img($list[$i]['mb_id']); ?></span>
        <span class="lt_name"><?php echo $list[$i]['wr_name'] ?></span>
        <span class="lt_date"><?php echo $list[$i]['datetime'] ?></span>
    </div>
    <?php if ($thumb['src']) { ?> <a href="<?php echo $list[$i]['href'] ?>" class="lt_img"><?php echo $img; ?></a>  <?php } ?>

</li>
<?php } ?>
<?php if ($i == 0) echo '<li class="empty_lt">게시물이 없습니다.</li>'; ?>
</ul>
