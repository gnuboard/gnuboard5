<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;
?>

<?
ob_start();
?>
<div data-role="navbar">
<ul>
    <li><?
        preg_match("/<a href=\'([^\']+)\'>이전<\/a>/", $write_pages, $matches);
        $prev_href = $matches[1];

        preg_match("/<a href=\'([^\']+)\'>다음<\/a>/", $write_pages, $matches);
        $next_href = $matches[1];

        if ($prev_href)
            echo "<a href='$prev_href'>";
        else {
            /*
            define("_PREV_POPUP_", true);
            echo "<a href='#prev_popup' data-rel='dialog' data-transition='pop'>";
            */
            echo "<a href='javascript:;' onclick=\"alert('이전 페이지 없음');\">";
        }
        ?><&nbsp; 이전</a></li>
    <li><?
        if ($next_href)
            echo "<a href='$next_href'>";
        else {
            /*
            define("_NEXT_POPUP_", true);
            echo "<a href='#next_popup' data-rel='dialog' data-transition='pop'>";
            */
            echo "<a href='javascript:;' onclick=\"alert('다음 페이지 없음');\">";
        }
        ?>다음 &nbsp;></a></li>
    <!-- <li><a href="<?=$g4[path]?>/bbs/board.php?bo_table=<?=$bo_table?>&t=<?=time()?>">1페이지</a></li> -->
    <li><a href="./write.php?bo_table=<?=$bo_table?>" data-ajax="false">글쓰기</a></li>
</ul>
</div><!-- /navbar -->
<?
$navbar = ob_get_clean();

echo $navbar;
?>

<ul data-role="listview" data-inset="true">
<?
for ($i=0; $i<count($list); $i++) {
    echo "\n<li class='ui-body ui-body-b'><a href='{$list[$i]['href']}'>";
    echo "\n<p class='list_name'>{$list[$i]['wr_name']}</p>";
    echo "\n<p class='list_subject'><strong>{$list[$i]['subject']}</strong></p>";
    echo "\n<p class='ui-li-count'>{$list[$i]['wr_comment']}</p>";
    //echo "\n<p class='ui-li-aside'>{$list[$i]['wr_name']}</p>";
    echo "\n</a></li>";
}

/*
    <li><a href="index.html">
        <h3>jQuery Team</h3>
        <p><strong>Boston Conference Planning</strong></p>
        <p>In preparation for the upcoming conference in Boston, we need to start gathering a list of sponsors and speakers.</p>
        <p class="ui-li-aside"><strong>9:18</strong>AM</p>
    </a></li>
*/
?>
</ul>

<!-- <? if ($write_href) {   ?><a href="<?=$write_href?>" data-role="button" data-icon="plus" data-inline="true">글쓰기</a><? } ?> -->

<?
echo $navbar;
?>

