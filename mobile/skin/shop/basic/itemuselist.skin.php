<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 전체 상품 사용후기 목록 시작 { -->
<form method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
<div id="sps_sch">
    <div class="sch_wr">
        <label for="sfl" class="sound_only">검색항목</label>
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
        <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="sch_input" size="10">
        <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
    </div>
    <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">전체보기</a>

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

        $is_content = get_view_thumbnail(conv_content($row['is_content'], 1), $thumbnail_width);

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
            <h2><?php echo get_text($row['is_subject']); ?></h2>

            <dl class="sps_dl">
                <dt>평가점수</dt>
                <dd class="sps_star"><img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개" width="80"></dd> 
                <dt>작성자</dt>
                <dd><i class="fa fa-user" aria-hidden="true"></i> <?php echo get_text($row['is_name']); ?></dd>
                <dt>작성일</dt>
                <dd><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo substr($row['is_time'],2,8); ?></dd>

            </dl>

            <div id="sps_con_<?php echo $i; ?>" style="display:none;">
                <?php echo $is_content; // 사용후기 내용 ?>

                <?php
                if( !empty($row['is_reply_subject']) ){     //사용후기 답변이 있다면
                    $is_reply_content = get_view_thumbnail(conv_content($row['is_reply_content'], 1), $thumbnail_width);
                ?>
                <div class="sps_reply">
                    <div class="sps_img">
                        <a href="<?php echo $it_href; ?>">
                            <?php echo get_itemuselist_thumbnail($row['it_id'], $row['is_reply_content'], 50, 50); ?>
                            <span><?php echo $row2['it_name']; ?></span>
                        </a>
                    </div>

                    <section class="sps_section">
                        <h2 class="is_use_reply"><?php echo get_text($row['is_reply_subject']); ?></h2>

                        <dl class="sps_dl">
                            <dt>작성자</dt>
                            <dd><?php echo $row['is_reply_name']; ?></dd>
                        </dl>

                        <div id="sps_con_<?php echo $i; ?>_reply" style="display:none;">
                            <?php echo $is_reply_content; // 사용후기 답변 내용 ?>
                        </div>

                    </section>
                </div>
                <?php }     //end if ?>

            </div>

            <div class="sps_con_btn"><button class="sps_con_<?php echo $i; ?>">내용보기 <i class="fa fa-caret-down" aria-hidden="true"></i></button></div>
        </section>

    </li>

    
    <?php }
    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sps_empty">자료가 없습니다.</p>';
    ?>
</div>

<?php echo get_paging($config['cf_mobile_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 사용후기 더보기
    $(".sps_con_btn button").click(function(){
        var $con = $(this).parent().prev();
        if($con.is(":visible")) {
            $con.slideUp();
            $(this).html("내용보기 <i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i>");
        } else {
            $(".sps_con_btn button").html("내용보기 <i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i>");
            $("div[id^=sps_con]:visible").hide();
            $con.slideDown(
                function() {
                    // 이미지 리사이즈
                    $con.viewimageresize2();
                }
            );
            $(this).html("내용닫기 <i class=\"fa fa-caret-up\" aria-hidden=\"true\"></i>");
        }
    });
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->