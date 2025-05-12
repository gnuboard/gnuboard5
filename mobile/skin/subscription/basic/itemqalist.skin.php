<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 전체 상품 문의 목록 시작 { -->
<form method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
<div id="sqa_sch">
    <div class="sch_wr">
        <label for="sfl" class="sound_only">검색항목</label>
        <select name="sfl" required id="sfl">
            <option value="">선택</option>
            <option value="b.it_name"    <?php echo get_selected($sfl, "b.it_name", true); ?>>상품명</option>
            <option value="a.it_id"      <?php echo get_selected($sfl, "a.it_id"); ?>>상품코드</option>
            <option value="a.iq_subject" <?php echo get_selected($sfl, "a.iq_subject"); ?>>문의제목</option>
            <option value="a.iq_question"<?php echo get_selected($sfl, "a.iq_question"); ?>>문의내용</option>
            <option value="a.iq_name"    <?php echo get_selected($sfl, "a.it_id"); ?>>작성자명</option>
            <option value="a.mb_id"      <?php echo get_selected($sfl, "a.mb_id"); ?>>작성자아이디</option>
        </select>

        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="sch_input">
        <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
    </div>
    <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">전체보기</a>
</div>
</form>

<div id="sqa">

    <!-- <p><?php echo $config['cf_title']; ?> 전체 상품문의 목록입니다.</p> -->

    <?php
    $thumbnail_width = 500;
    $num = $total_count - ($page - 1) * $rows;

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $iq_subject = conv_subject($row['iq_subject'],50,"…");

        $is_secret = false;
        if($row['iq_secret']) {
            $iq_subject .= ' <i class="fa fa-lock" aria-hidden="true"></i>';

            if($is_admin || $member['mb_id' ] == $row['mb_id']) {
                $iq_question = get_view_thumbnail(conv_content($row['iq_question'], 1), $thumbnail_width);
            } else {
                $iq_question = '비밀글로 보호된 문의입니다.';
                $is_secret = true;
            }
        } else {
            $iq_question = get_view_thumbnail(conv_content($row['iq_question'], 1), $thumbnail_width);
        }

        $it_href = shop_item_url($row['it_id']);

        if ($row['iq_answer'])
        {
            $iq_answer = get_view_thumbnail(conv_content($row['iq_answer'], 1), $thumbnail_width);
            $iq_stats = '답변완료';
            $iq_style = 'sit_qaa_done';
            $is_answer = true;
        } else {
            $iq_stats = '답변대기';
            $iq_style = 'sit_qaa_yet';
            $iq_answer = '답변이 등록되지 않았습니다.';
            $is_answer = false;
        }

        if ($i == 0) echo '<ol>';
    ?>
    <li>
        <div class="sqa_img">
            <a href="<?php echo $it_href; ?>">
                <?php echo get_it_image($row['it_id'], 60, 60); ?>
                <span><?php echo $row['it_name']; ?></span>
            </a>
        </div>

        <section class="sqa_section">
            <h2><span class="<?php echo $iq_style; ?>"><?php echo $iq_stats; ?></span> <?php echo $iq_subject; ?></h2>
            <div class="sqa_info">
                <span class="sound_only">작성자</span>
                <span class="sqa_if_wt"><?php echo get_text($row['iq_name']); ?></span>
                <span class="sound_only">작성일</span>
                <span><?php echo substr($row['iq_time'],2,8); ?></span>
            </div>
        </section>
  
        <div id="sqa_con_<?php echo $i; ?>" class="sqa_con" style="display:none;">
            <div class="sit_qa_qaq">
                <strong class="sound_only">문의내용</strong>
                <span class="qa_alp">Q</span>
                <?php echo $iq_question; // 상품 문의 내용 ?>
            </div>
            <?php if(!$is_secret) { ?>
            <div class="sit_qa_qaa">
                 <strong class="sound_only">답변</strong>
                <span class="qa_alp">A</span>
                <?php echo $iq_answer; ?>
            </div>
            <?php } ?>
        </div>
        
		<div class="sqa_con_btn"><button class="sqa_con_<?php echo $i; ?>"><span class="sound_only">내용보기</span><i class="fa fa-chevron-down" aria-hidden="true"></i></button></div>

    </li>
    <?php
        $num--;
    }

    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sps_empty">자료가 없습니다.</p>';
    ?>
</div>

<?php echo get_paging($config['cf_mobile_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 상품문의 더보기
    $(".sqa_con_btn button").click(function(){
        var $con = $(this).parent().prev();
        if($con.is(":visible")) {
            $con.slideUp();
            $(this).html("<span class=\"sound_only\">내용보기</span> <i class=\"fa fa-chevron-down\" aria-hidden=\"true\"></i>");
        } else {
            $("div[id^=sqa_con]:visible").hide();
            $con.slideDown(
                function() {
                    // 이미지 리사이즈
                    $con.viewimageresize2();
                }
            );
            $(this).html("<span class=\"sound_only\">내용닫기</span><i class=\"fa fa-chevron-up\" aria-hidden=\"true\"></i>");
        }
    });
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->