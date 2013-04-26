<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 검색 -->
<fieldset id="new_sch">
    <legend>상세검색</legend>
    <form name="fnew" method="get">
    <?php echo $group_select ?>
    <label for="view" class="sound_only">검색대상</label>
    <select name="view" id="view" onchange="select_change()">
        <option value="">전체게시물
        <option value="w">원글만
        <option value="c">코멘트만
    </select>
    <input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" placeholder="검색어(필수)" required class="required">
    <input type="submit" value="검색">
    </form>
    <script>
    function select_change()
    {
        document.fnew.submit();
    }
    document.getElementById("gr_id").value = "<?php echo $gr_id ?>";
    document.getElementById("view").value = "<?php echo $view ?>";
    </script>
</fieldset>
<!-- 검색 끝 -->

<!-- 제목 시작 -->
<table id="new_tbl" class="basic_tbl">
<thead>
<tr>
    <th scope="col">게시판</th>
    <th scope="col">제목</th>
    <th scope="col">일시</th>
</tr>
</thead>
<tbody>
<?php
for ($i=0; $i<count($list); $i++) 
{
    $gr_subject = cut_str($list[$i]['gr_subject'], 20);
    $bo_subject = cut_str($list[$i]['bo_subject'], 20);
    $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
?>
<tr>
    <td class="td_board"><a href="./board.php?bo_table=<?php echo $list[$i]['bo_table'] ?>"><?php echo $bo_subject ?></a></td>
    <td><a href="<?php echo $list[$i]['href'] ?>"><?php echo $list[$i]['comment'] ?><?php echo $wr_subject ?></a></td>
    <td class="td_date"><?php echo $list[$i]['datetime2'] ?></td>
</tr>
<?php } ?>

<?php if ($i == 0)
    echo "<tr><td colspan=\"5\" class=\"empty_table\">게시물이 없습니다.</td></tr>";
?>
</tbody>
</table>

<?php echo $write_pages ?>
