<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 상품 사용후기 시작 { -->
<section id="sit_use_list">
    <h3>등록된 사용후기</h3>

    <div class="sit_use_top">
        <?php if ($star_score) { ?>
        <h4>구매고객 총평점 <span>(총 <strong><?php echo $total_count; ?></strong> 건 상품평 기준)</span></h4>
        <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star_score?>.png" alt="" class="sit_star">
        <?php } ?>
        <div id="sit_use_wbtn">
            <a href="<?php echo $itemuse_form; ?>" class="btn02 itemuse_form">사용후기 쓰기<span class="sound_only"> 새 창</span></a>
            <a href="<?php echo $itemuse_list; ?>" class="btn01 itemuse_list">더보기</a>
        </div>
    </div>
    
    <?php
    $thumbnail_width = 500;

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $is_num     = $total_count - ($page - 1) * $rows - $i;
        $is_star    = get_star($row['is_score']);
        $is_name    = get_text($row['is_name']);
        $is_subject = conv_subject($row['is_subject'],50,"…");
        $is_content = get_view_thumbnail(conv_content($row['is_content'], 1), $thumbnail_width);
        $is_reply_name = !empty($row['is_reply_name']) ? get_text($row['is_reply_name']) : '';
        $is_reply_subject = !empty($row['is_reply_subject']) ? conv_subject($row['is_reply_subject'],50,"…") : '';
        $is_reply_content = !empty($row['is_reply_content']) ? get_view_thumbnail(conv_content($row['is_reply_content'], 1), $thumbnail_width) : '';
        $is_time    = substr($row['is_time'], 2, 8);

        $hash = md5($row['is_id'].$row['is_time'].$row['is_ip']);

        if ($i == 0) echo '<ol id="sit_use_ol">';
    ?>

        <li class="sit_use_li">
			<span class="sit_thum"><?php echo get_itemuselist_thumbnail($row['it_id'], $row['is_content'], 100, 100); ?></span> 
            <dl class="sit_use_dl">
                <dt>평점<dt>
                <dd class="sit_use_star"><img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $is_star; ?>.png" alt="별<?php echo $is_star; ?>개" width="85"></dd>
                <dt></dt>
                <dd class="sit_use_tit"><?php echo $is_subject; ?></dd>
                <dt>작성자/작성일</dt>
                <dd><?php echo $is_name; ?><span class="st_bg"></span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $is_time; ?></dd>
            </dl>
			<button type="button" class="sit_use_li_title">내용보기 <i class="fa fa-caret-down" aria-hidden="true"></i></button>

            <div id="sit_use_con_<?php echo $i; ?>" class="sit_use_con">
                <div class="sit_use_p">
                    <?php echo $is_content; // 사용후기 내용 ?> 
                </div>

                <?php if ($is_admin || $row['mb_id'] == $member['mb_id']) { ?>
                <div class="sit_use_cmd">
                    <a href="<?php echo $itemuse_form."&amp;is_id={$row['is_id']}&amp;w=u"; ?>" class="itemuse_form btn01" onclick="return false;">수정</a>
                    <a href="<?php echo $itemuse_formupdate."&amp;is_id={$row['is_id']}&amp;w=d&amp;hash={$hash}"; ?>" class="itemuse_delete btn01">삭제</a>
                </div>
                <?php } ?>

                <?php if( $is_reply_subject ){  //  사용후기 답변 내용이 있다면 ?>
                <div class="sit_use_reply">
                    <div class="use_reply_icon">답변</div>
                    <div class="use_reply_tit">
                        <?php echo $is_reply_subject; // 답변 제목 ?>
                    </div>
                    <div class="use_reply_name">
                        <?php echo $is_reply_name; // 답변자 이름 ?>
                    </div>
                    <div class="use_reply_p">
                        <?php echo $is_reply_content; // 답변 내용 ?>
                    </div>
                </div>
                <?php } //end if ?>
            </div>
        </li>

    <?php }

    if ($i > 0) echo '</ol>';

    if (!$i) echo '<p class="sit_empty">사용후기가 없습니다.</p>';
    ?>
</section>

<?php
echo itemuse_page($config['cf_write_pages'], $page, $total_page, G5_SHOP_URL."/itemuse.php?it_id=$it_id&amp;page=", "");
?>

<script>
$(function(){
    $(".itemuse_form").click(function(){
        window.open(this.href, "itemuse_form", "width=810,height=680,scrollbars=1");
        return false;
    });

    $(".itemuse_delete").click(function(){
        if (confirm("정말 삭제 하시겠습니까?\n\n삭제후에는 되돌릴수 없습니다.")) {
            return true;
        } else {
            return false;
        }
    });

    $(".sit_use_li_title").click(function(){
        var $con = $(this).siblings(".sit_use_con");
        if($con.is(":visible")) {
            $con.slideUp();
        } else {
            $(".sit_use_con:visible").hide();
            $con.slideDown(
                function() {
                    // 이미지 리사이즈
                    $con.viewimageresize2();
                }
            );
        }
    });

    $(".pg_page").click(function(){
        $("#itemuse").load($(this).attr("href"));
        return false;
    });
});
</script>
<!-- } 상품 사용후기 끝 -->