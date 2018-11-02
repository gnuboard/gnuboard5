<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$new_skin_url.'/style.css">', 0);
?>

<!-- 전체게시물 검색 시작 { -->
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
    <input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" placeholder="검색어(필수)" required class="frm_input ">
    <button type="submit" value="검색" class="btn_submit"><i class="fa fa-search" aria-hidden="true"></i></button>
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
<!-- } 전체게시물 검색 끝 -->

<!-- 전체게시물 목록 시작 { -->
<div class="list_01" id="new_list">
    <ul>

    <?php
    for ($i=0; $i<count($list); $i++)
    {
        $gr_subject = cut_str($list[$i]['gr_subject'], 20);
        $bo_subject = cut_str($list[$i]['bo_subject'], 20);
        $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
    ?>
    <li>
        <a href="<?php echo $list[$i]['href'] ?>" class="new_tit"><?php echo $list[$i]['comment'] ?><?php echo $wr_subject ?></a>
        <a href="./board.php?bo_table=<?php echo $list[$i]['bo_table'] ?>" class="new_board"><i class="fa fa-list-alt" aria-hidden="true"></i> <?php echo $bo_subject ?></a>
        <span class="new_date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $list[$i]['datetime2'] ?></span>
    </li>
    <?php } ?>

    <?php if ($i == 0)
        echo '<li class="empty_table">게시물이 없습니다.</li>';
    ?>
    </ul>
</div>

<?php echo $write_pages ?>
<!-- } 전체게시물 목록 끝 -->