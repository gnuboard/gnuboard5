<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<section id="popular">
    <div>
        <h2>인기검색어</h2>
        <ul>
        <? for ($i=0; $i<count($list); $i++) { ?>
            <li><a href="<?=G4_BBS_URL?>/search.php?sfl=wr_subject&amp;sop=and&amp;stx=<?=urlencode($list[$i]['pp_word'])?>"><?=$list[$i]['pp_word']?></a></li>
        <? } ?>
        </ul>
    </div>
</section>