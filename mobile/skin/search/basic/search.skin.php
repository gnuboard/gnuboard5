<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<form name="fsearch" method="get" onsubmit="return fsearch_submit(this);">
<input type="hidden" name="srows" value="<?=$srows?>">
<fieldset id="sch_result_detail">
    <legend class="sound_only">상세검색</legend>
    <?=$group_select?>
    <script>document.getElementById("gr_id").value = "<?=$gr_id?>";</script>

    <select name="sfl" title="검색조건">
        <option value="wr_subject||wr_content"<?=get_selected($_GET['sfl'], "wr_subject||wr_content")?>>제목+내용</option>
        <option value="wr_subject"<?=get_selected($_GET['sfl'], "wr_subject")?>>제목</option>
        <option value="wr_content"<?=get_selected($_GET['sfl'], "wr_content")?>>내용</option>
        <option value="mb_id"<?=get_selected($_GET['sfl'], "mb_id")?>>회원아이디</option>
        <option value="wr_name"<?=get_selected($_GET['sfl'], "wr_name")?>>이름</option>
    </select>

    <input type="text" name="stx" class="fs_input" class="required" value="<?=$text_stx?>" maxlength="20" required title="검색어(필수)">
    <input type="submit" class="fs_submit" value="검색">

    <script>
    function fsearch_submit(f)
    {
        if (f.stx.value.length < 2) {
            alert("검색어는 두글자 이상 입력하십시오.");
            f.stx.select();
            f.stx.focus();
            return false;
        }

        // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
        var cnt = 0;
        for (var i=0; i<f.stx.value.length; i++) {
            if (f.stx.value.charAt(i) == ' ')
                cnt++;
        }

        if (cnt > 1) {
            alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
            f.stx.select();
            f.stx.focus();
            return false;
        }
        
        f.action = "";
        return true;
    }
    </script>
    <input type="radio" id="sop_or" name="sop" value="or" <?=($sop == "or") ? "checked" : "";?>>
    <label for="sop_or">OR</label>
    <input type="radio" id="sop_and" name="sop" value="and" <?=($sop == "and") ? "checked" : "";?>>
    <label for="sop_and">AND</label>
</fieldset>
</form>

<div id="sch_result">

    <? if ($stx) { ?>
    <? if ($board_count) { ?>
    <dl id="sch_result_hd">
        <dt><strong><?=$stx?></strong>에 대한 검색 결과입니다.</dt>
        <dd>
            <ul>
                <li><span style="color:<?=$config['cf_search_color']?>"><?=$board_count?></span>개의 게시판</li>
                <li><span style="color:<?=$config['cf_search_color']?>"><?=number_format($total_count)?></span>개의 게시물</li>
                <li>현재 <?=number_format($page)?>/<?=number_format($total_page)?> 페이지 열람 중</li>
            </ul>
        </dd>
    </dl>
    <? } ?>
    <? } ?>

    <?
    if ($stx) {
        if ($board_count) {
    ?>
    <dl id="sch_result_bo">
        <dt>검색결과가 있는 게시판 목록</dt>
        <dd>
            <ul>
                <li><a href="?<?=$search_query?>&amp;gr_id=<?=$gr_id?>" <?=$sch_all?>>전체게시판</a></li>
                <?=$str_board_list;?>
            </ul>
        </dd>
    </dl>
    <?
        } else {
    ?>
    <p>검색된 자료가 하나도 없습니다.</p>
    <? } } ?>

    <hr>

    <? if ($stx && $board_count) { ?><dl id="sch_result_atc"><? } ?>
    <?
    $k=0;
    for ($idx=$table_index, $k=0; $idx<count($search_table) && $k<$rows; $idx++) {
        $comment_def = "";
        $comment_href = "";
    ?>
        <dt><a href="./board.php?bo_table=<?=$search_table[$idx]?>&amp;<?=$search_query?>"><?=$bo_subject[$idx]?>에서</a></dt>
        <dd>
            <ul>
            <?
            for ($i=0; $i<count($list[$idx]) && $k<$rows; $i++, $k++) {
                if ($list[$idx][$i][wr_is_comment]) 
                {
                    $comment_def = "<span class=\"cmt_def\">댓글</span>";
                    $comment_href = "#c_".$list[$idx][$i][wr_id];
                }
            ?>
                <li>
                    <a href="<?=$list[$idx][$i][href]?><?=$comment_href?>" class="sch_result_title"><?=$comment_def?><?=$list[$idx][$i][subject]?></a>
                    <a href="<?=$list[$idx][$i][href]?><?=$comment_href?>" target="_blank">새창</a>
                    <p><?=$list[$idx][$i][content]?></p>
                    <div class="sch_sideview"><?=$list[$idx][$i][name]?></div>
                    <span class="sch_datetime"><?=$list[$idx][$i][wr_datetime]?></span>
                </li>
            <? } ?>
            </ul>
            <div class="sch_more"><a href="./board.php?bo_table=<?=$search_table[$idx]?>&amp;<?=$search_query?>"><?=$bo_subject[$idx]?> 더보기</a></div>
        </dd>
    <? } ?>
    <? if ($stx && $board_count) { ?></dl><? } ?>

    <?=$write_pages?>

</div>
