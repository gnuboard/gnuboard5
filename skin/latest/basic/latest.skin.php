<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div class="lt">
    <strong class="lt_title"><a href="<?=G4_BBS_URL?>/board.php?bo_table=<?=$bo_table?>"><?=$bo_subject?></a></strong>
    <ul>
    <? for ($i=0; $i<count($list); $i++) { ?>
        <li>
            <?
            //echo $list[$i]['icon_reply']." ";
            echo "<a href=\"".$list[$i]['href']."\" title=\"".$list[$i]['wr_subject']."\">";
            if ($list[$i]['is_notice'])
                echo "<strong>".$list[$i]['subject']."</strong>";
            else
                echo $list[$i]['subject'];

            if ($list[$i]['comment_cnt'])
                echo " <span class=\"cnt_cmt\">".$list[$i]['comment_cnt']."</span>";

            echo "</a>";

            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

            if (isset($list[$i]['icon_new']))    echo " " . $list[$i]['icon_new'];
            if (isset($list[$i]['icon_hot']))    echo " " . $list[$i]['icon_hot'];
            if (isset($list[$i]['icon_file']))   echo " " . $list[$i]['icon_file'];
            if (isset($list[$i]['icon_link']))   echo " " . $list[$i]['icon_link'];
            if (isset($list[$i]['icon_secret'])) echo " " . $list[$i]['icon_secret'];
            ?>
        </li>
    <? } ?>
    </ul>
    <? if (count($list) == 0) { //게시물이 없을 때 ?>
    <p>게시물이 없습니다.</p>
    <? } ?>
    <div class="lt_more"><a href="<?=G4_BBS_URL?>/board.php?bo_table=<?=$bo_table?>">더보기</a></div>
</div>
