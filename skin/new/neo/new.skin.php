<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 검색 -->
<form name="fnew" method="get">
<fieldset>
    <legend>사이트 상세검색</legend>
    <label for="gr_id">검색대상</label>
    <?=$group_select?>
    <label for="view">검색종류</label>
    <select id="view" name="view" onchange="select_change()">
        <option value="">전체게시물
        <option value="w">원글만
        <option value="c">코멘트만
    </select>
    <label for="mb_id">회원아이디</label>
    <input type="text" id="mb_id" name="mb_id" value="<?=$mb_id?>">
    <input type="submit" class="fieldset_submit" value="검색">
    <script>
    function select_change()
    {
        document.fnew.submit();
    }
    document.getElementById("gr_id").value = "<?=$gr_id?>";
    document.getElementById("view").value = "<?=$view?>";
    </script>
</fieldset>
</form>
<!-- 검색 끝 -->

<!-- 제목 시작 -->
<table>
<caption>최근게시물 목록</caption>
<thead>
<tr>
    <th scope="col">그룹</th>
    <th scope="col">게시판</th>
    <th scope="col">제목</th>
    <th scope="col">이름</th>
    <th scope="col">일시</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $i<count($list); $i++) 
{
    $gr_subject = cut_str($list[$i][gr_subject], 20);
    $bo_subject = cut_str($list[$i][bo_subject], 20);
    $wr_subject = get_text(cut_str($list[$i][wr_subject], 80));
?>
<tr>
    <td class="td_group"><a href="./new.php?gr_id=<?=$list[$i][gr_id]?>"><?=$gr_subject?></a></td>
    <td class="td_board"><a href="./board.php?bo_table=<?=$list[$i][bo_table]?>"><?=$bo_subject?></a></td>
    <td><a href="<?=$list[$i][href]?>"><?=$list[$i][comment]?><?=$wr_subject?></a></td>
    <td class="td_name"><div><?=$list[$i][name]?></div></td>
    <td class="td_datetime"><?=$list[$i][datetime2]?></td>
</tr>
<? } ?>

<? if ($i == 0)
    echo "<tr><td colspan=\"5\" class=\"empty_table\">게시물이 없습니다.</td></tr>";
?>
</tbody>
</table>

<div class="pg">
    <?=$write_pages?>
</div>
