<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<form name="fsearch" method="get" onsubmit="return fsearch_submit(this);">
<input type="hidden" name="srows" value="<?=$srows?>">
    <fieldset>
        <legend>상세검색</legend>
        <?=$group_select?>
        <script>document.getElementById("gr_id").value = "<?=$gr_id?>";</script>

        <select name="sfl">
            <option value="wr_subject||wr_content">제목+내용</option>
            <option value="wr_subject">제목</option>
            <option value="wr_content">내용</option>
            <option value="mb_id">회원아이디</option>
            <option value="wr_name">이름</option>
        </select>

        <input type="text" name="stx" class="fieldset_input" value="<?=$text_stx?>" maxlength="20" required>

        <input type="submit" class="fieldset_submit" value="검색">

        <script>
        document.fsearch.sfl.value = "<?=$sfl?>";
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
        <input type="radio" id="sop_or" name="sop" value="or" <?=($sop == "or") ? "checked" : "";?>> <label for="sop_or">OR</label>
        <input type="radio" id="sop_and" name="sop" value="and" <?=($sop == "and") ? "checked" : "";?>> <label for="sop_and">AND</label>
        <? if ($stx) { ?>
        <p>
            <? if ($board_count) { ?>
            <? } else { ?>
            <? } ?>
        </p>
        <? } ?>
    </fieldset>
</form>

<? 
if ($stx) {
    echo "검색된 게시판 리스트 (".$board_count."개의 게시판, ".number_format($total_count)."개의 게시글, ".number_format($page)."/".number_format($total_page)." 페이지)";
    if ($board_count) {
?>
<ul>
    <? if ($onetable) { ?>
    <li><a href="?<?=$search_query?>&amp;gr_id=<?=$gr_id?>">전체게시판 검색</a>
    <? } ?>
    <?=$str_board_list;?>
</ul>
<?
    } else {
?>
<p>검색된 자료가 하나도 없습니다.</p>
<? } } ?>

<?
$k=0;
for ($idx=$table_index, $k=0; $idx<count($search_table) && $k<$rows; $idx++) {
   $comment_href = "";
?>
<ul>
    <li>
        <a href="./board.php?bo_table=<?=$search_table[$idx]?>&amp;<?=$search_query?>"><?=$bo_subject[$idx]?></a>에서의 검색결과
        <?
        for ($i=0; $i<count($list[$idx]) && $k<$rows; $i++, $k++) {
            if ($list[$idx][$i][wr_is_comment]) 
            {
                echo "댓글 ";
                $comment_href = "#c_".$list[$idx][$i][wr_id];
            }
        ?>
         <ul>
            <li>
                <a href="<?=$list[$idx][$i][href]?><?=$comment_href?>"><?=$list[$idx][$i][subject]?></a>
                <a href="<?=$list[$idx][$i][href]?><?=$comment_href?>" target="_blank">새창</a>
                <p><?=$list[$idx][$i][content]?></p>
                <?=$list[$idx][$i][wr_datetime]?>
                <?=$list[$idx][$i][name]?>
            </li>
        </ul>
        <? } ?>
    </li>
</ul>
<? } ?>

<div id="pg">
    <?=$write_pages?>
</div>