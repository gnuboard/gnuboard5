<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<div class="lat">
    <strong class="lat_title"><i class="fa fa-list-ul" aria-hidden="true"></i> <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"><?php echo $bo_subject ?></a></strong>
    <ul>
    <?php for ($i=0; $i<count($list); $i++) {  ?>
        <li>
            <?php
            //echo $list[$i]['icon_reply']." ";
            echo "<a href=\"".$list[$i]['href']."\">";
            if ($list[$i]['is_notice'])
                echo "<strong>".$list[$i]['subject']."</strong>";
            else
                echo $list[$i]['subject'];

            if ($list[$i]['comment_cnt'])
                echo $list[$i]['comment_cnt'];

            echo "</a>";

            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

            if (isset($list[$i]['icon_new']) && $list[$i]['icon_new']) echo " <span class=\"new_icon\">NEW</span>";
            //if (isset($list[$i]['icon_hot'])) echo " <i class=\"fa fa-heart\" aria-hidden=\"true\"></i>" ;
            //if (isset($list[$i]['icon_file'])) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ;
            //if (isset($list[$i]['icon_link'])) echo " <i class=\"fa fa-link\" aria-hidden=\"true\"></i>" ;
            //if (isset($list[$i]['icon_secret'])) echo " <i class=\"fa fa-lock\" aria-hidden=\"true\"></i>" ;
             ?>
        </li>
    <?php }  ?>
    <?php if (count($list) == 0) { //게시물이 없을 때  ?>
    <li>게시물이 없습니다.</li>
    <?php }  ?>
    </ul>
</div>
