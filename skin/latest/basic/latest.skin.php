<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<!-- <?php echo $bo_subject; ?> 최신글 시작 { -->
<div class="lt">
    <h2 class="lt_title"><i class="fa fa-list-alt" aria-hidden="true"></i> <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"><?php echo $bo_subject; ?></a></h2>
    <ul>
    <?php for ($i=0; $i<count($list); $i++) {  ?>
        <li>
            <div class="lt_left">
                <?php
                //echo $list[$i]['icon_reply']." ";
                echo "<a href=\"".$list[$i]['href']."\">";
                if ($list[$i]['is_notice'])
                    echo "<strong>".$list[$i]['subject']."</strong>";
                else
                    echo $list[$i]['subject'];

                echo "</a>";
                 ?>
                 <?php if ($list[$i]['comment_cnt']) echo $list[$i]['comment_cnt'];  ?>
            </div>
            <span class="lt_date"><?php echo $list[$i]['datetime2'] ?></span>
        </li>
    <?php }  ?>
    <?php if (count($list) == 0) { //게시물이 없을 때  ?>
    <li class="empty_li">게시물이 없습니다.</li>
    <?php }  ?>
    </ul>
</div>
<!-- } <?php echo $bo_subject; ?> 최신글 끝 -->