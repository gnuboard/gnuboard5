<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 전체 상품 사용후기 목록 시작 { -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div id="sps_sch">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>">전체보기</a>
    <label for="sfl" class="sound_only">검색항목<strong class="sound_only"> 필수</strong></label>
    <select name="sfl" id="sfl" required>
        <option value="">선택</option>
        <option value="b.it_name"   <?php echo get_selected($sfl, "b.it_name"); ?>>상품명</option>
        <option value="a.it_id"     <?php echo get_selected($sfl, "a.it_id"); ?>>상품코드</option>
        <option value="a.is_subject"<?php echo get_selected($sfl, "a.is_subject"); ?>>후기제목</option>
        <option value="a.is_content"<?php echo get_selected($sfl, "a.is_content"); ?>>후기내용</option>
        <option value="a.is_name"   <?php echo get_selected($sfl, "a.is_name"); ?>>작성자명</option>
        <option value="a.mb_id"     <?php echo get_selected($sfl, "a.mb_id"); ?>>작성자아이디</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div id="sps">

    <!-- <p><?php echo $config['cf_title']; ?> 전체 사용후기 목록입니다.</p> -->

    <?php
    $thumbnail_width = 500;

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count - ($page - 1) * $rows - $i;
        $star = get_star($row['is_score']);

        $is_content = get_view_thumbnail($row['is_content'], $thumbnail_width);

        $row2 = sql_fetch(" select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['it_id']}' ");
        $it_href = G5_SHOP_URL."/item.php?it_id={$row['it_id']}";

        if ($i == 0) echo '<ol>';
    ?>
    <li>

        <div class="sps_img">
            <a href="<?php echo $it_href; ?>">
                <?php echo get_itemuselist_thumbnail($row['it_id'], $row['is_content'], 70, 70); ?>
                <span><?php echo $row2['it_name']; ?></span>
            </a>
        </div>

        <section class="sps_section">
            <h2><?php echo $row['is_subject']; ?></h2>

            <dl class="sps_dl">
                <dt>작성자</dt>
                <dd><?php echo $row['is_name']; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo substr($row['is_time'],0,10); ?></dd>
                <dt>평가점수</dt>
                <dd><img src="<?php echo G5_URL; ?>/shop/img/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개"></dd>
            </dl>

            <div id="sps_con_<?php echo $i; ?>" style="display:none;">
                <?php echo $is_content; // 사용후기 내용 ?>
            </div>

            <div class="sps_con_btn"><button class="sps_con_<?php echo $i; ?>">보기</button></div>
        </section>

    </li>
    <?php }
    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sps_empty">자료가 없습니다.</p>';
    ?>
</div>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 사용후기 더보기
    $(".sps_con_btn button").click(function(){
        var $con = $(this).parent().prev();
        if($con.is(":visible")) {
            $con.slideUp();
            $(this).text("보기");
        } else {
            $(".sps_con_btn button").text("보기");
            $("div[id^=sps_con]:visible").hide();
            $con.slideDown(
                function() {
                    // 이미지 리사이즈
                    $con.viewimageresize2();
                }
            );
            $(this).text("닫기");
        }
    });
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->