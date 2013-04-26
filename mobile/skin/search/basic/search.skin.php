<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<form name="fsearch" onsubmit="return fsearch_submit(this);" method="get">
<input type="hidden" name="srows" value="<?php echo $srows ?>">
<fieldset id="sch_result_detail">
    <legend>상세검색</legend>
    <?php echo $group_select ?>
    <script>document.getElementById("gr_id").value = "<?php echo $gr_id ?>";</script>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="wr_subject||wr_content"<?php echo get_selected($_GET['sfl'], "wr_subject||wr_content") ?>>제목+내용</option>
        <option value="wr_subject"<?php echo get_selected($_GET['sfl'], "wr_subject") ?>>제목</option>
        <option value="wr_content"<?php echo get_selected($_GET['sfl'], "wr_content") ?>>내용</option>
        <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id") ?>>회원아이디</option>
        <option value="wr_name"<?php echo get_selected($_GET['sfl'], "wr_name") ?>>이름</option>
    </select>

    <input type="text" name="stx" value="<?php echo $text_stx ?>" id="stx" required class="required" placeholder="검색어(필수)" maxlength="20">
    <input type="submit" value="검색">

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
    <input type="radio" id="sop_or" name="sop" value="or" <?php echo ($sop == "or") ? "checked" : ""; ?>>
    <label for="sop_or">OR</label>
    <input type="radio" id="sop_and" name="sop" value="and" <?php echo ($sop == "and") ? "checked" : ""; ?>>
    <label for="sop_and">AND</label>
</fieldset>
</form>

<div id="sch_result">

    <?php if ($stx) { ?>
    <?php if ($board_count) { ?>
    <dl id="sch_result_hd">
        <dt><strong><?php echo $stx ?></strong>에 대한 검색 결과입니다.</dt>
        <dd>
            <ul>
                <li>게시판 <span style="color:<?php echo $config['cf_search_color'] ?>"><?php echo $board_count ?></span>개</li>
                <li>게시물 <span style="color:<?php echo $config['cf_search_color'] ?>"><?php echo number_format($total_count) ?></span>개</li>
                <li><?php echo number_format($page) ?>/<?php echo number_format($total_page) ?> 페이지</li>
            </ul>
        </dd>
    </dl>
    <?php } ?>
    <?php } ?>

    <?php
    if ($stx) {
        if ($board_count) {
    ?>
    <dl id="sch_result_bo">
        <dt>검색결과가 있는 게시판 목록</dt>
        <dd>
            <ul>
                <li><a href="?<?php echo $search_query ?>&amp;gr_id=<?php echo $gr_id ?>" <?php echo $sch_all ?>>전체게시판</a></li>
                <?php echo $str_board_list; ?>
            </ul>
        </dd>
    </dl>
    <?php
        } else {
    ?>
    <p>검색된 자료가 하나도 없습니다.</p>
    <?php } } ?>

    <hr>

    <?php if ($stx && $board_count) { ?><dl id="sch_result_atc"><?php } ?>
    <?php
    $k=0;
    for ($idx=$table_index, $k=0; $idx<count($search_table) && $k<$rows; $idx++) {
        $comment_def = "";
        $comment_href = "";
    ?>
        <dt><a href="./board.php?bo_table=<?php echo $search_table[$idx] ?>&amp;<?php echo $search_query ?>"><?php echo $bo_subject[$idx] ?>에서</a></dt>
        <dd>
            <ul>
            <?php
            for ($i=0; $i<count($list[$idx]) && $k<$rows; $i++, $k++) {
                if ($list[$idx][$i][wr_is_comment]) 
                {
                    $comment_def = "<span class=\"cmt_def\">댓글</span>";
                    $comment_href = "#c_".$list[$idx][$i][wr_id];
                }
            ?>
                <li>
                    <a href="<?php echo $list[$idx][$i][href] ?><?php echo $comment_href ?>" class="sch_result_title"><?php echo $comment_def ?><?php echo $list[$idx][$i][subject] ?></a>
                    <a href="<?php echo $list[$idx][$i][href] ?><?php echo $comment_href ?>" target="_blank">새창</a>
                    <p><?php echo $list[$idx][$i][content] ?></p>
                    <?php echo $list[$idx][$i][name] ?>
                    <span class="sch_datetime"><?php echo $list[$idx][$i][wr_datetime] ?></span>
                </li>
            <?php } ?>
            </ul>
            <div class="sch_more"><a href="./board.php?bo_table=<?php echo $search_table[$idx] ?>&amp;<?php echo $search_query ?>"><?php echo $bo_subject[$idx] ?> 더보기</a></div>
        </dd>
    <?php } ?>
    <?php if ($stx && $board_count) { ?></dl><?php } ?>

    <?php echo $write_pages ?>

</div>
