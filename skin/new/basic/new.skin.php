<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<link rel="stylesheet" href="<?php echo $new_skin_url ?>/style.css">

<!-- 전체게시물 검색 시작 { -->
<fieldset id="new_sch">
    <legend>상세검색</legend>
    <form name="fnew" method="get">
    <?php echo $group_select ?>
    <label for="view" class="sound_only">검색대상</label>
    <select name="view" id="view">
        <option value="">전체게시물
        <option value="w">원글만
        <option value="c">코멘트만
    </select>
    <label for="mb_id" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">
    </form>
    <script>
    /* 셀렉트 박스에서 자동 이동 해제
    function select_change()
    {
        document.fnew.submit();
    }
    */
    document.getElementById("gr_id").value = "<?php echo $gr_id ?>";
    document.getElementById("view").value = "<?php echo $view ?>";
    </script>
</fieldset>
<!-- } 전체게시물 검색 끝 -->

<!-- 전체게시물 목록 시작 { -->
<table class="basic_tbl">
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
<?php
for ($i=0; $i<count($list); $i++) 
{
    $gr_subject = cut_str($list[$i]['gr_subject'], 20);
    $bo_subject = cut_str($list[$i]['bo_subject'], 20);
    $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
?>
<tr>
    <td class="td_group"><a href="./new.php?gr_id=<?php echo $list[$i]['gr_id'] ?>"><?php echo $gr_subject ?></a></td>
    <td class="td_board"><a href="./board.php?bo_table=<?php echo $list[$i]['bo_table'] ?>"><?php echo $bo_subject ?></a></td>
    <td><a href="<?php echo $list[$i]['href'] ?>"><?php echo $list[$i]['comment'] ?><?php echo $wr_subject ?></a></td>
    <td class="td_name"><div><?php echo $list[$i]['name'] ?></div></td>
    <td class="td_date"><?php echo $list[$i]['datetime2'] ?></td>
</tr>
<?php }  ?>

<?php if ($i == 0)
    echo "<tr><td colspan=\"5\" class=\"empty_table\">게시물이 없습니다.</td></tr>";
?>
</tbody>
</table>
<!-- } 전체게시물 목록 끝 -->

<?php echo $write_pages ?>
