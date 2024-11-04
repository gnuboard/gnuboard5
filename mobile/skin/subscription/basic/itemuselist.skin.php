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

        $row2 = get_shop_item($row['it_id'], true);
        $it_href = shop_item_url($row['it_id']);

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
			<div class="sps_star">
				<span class="sound_only">평가점수</span><img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개" width="80">
			</div>

			<div class="sps_con_btn">
				<button class="sps_con_<?php echo $i; ?> review_detail"><?php echo get_text($row['is_subject']); ?></button>
				<!-- 사용후기 자세히 시작 -->
				<div class="review_detail_cnt">
	            	<div class="review_detail_in">
	            		<div class="review_detail_inner">
	            		<h3>사용후기</h3>
	            		<div class="review_cnt">
	            			<div class="review_tp_cnt">
	            				<span><?php echo get_text($row['is_subject']); ?></span>
	            				<dl class="sps_dl">
					                <dt class="sound_only">작성자</dt>
					                <dd class="sps_dd_wt"><?php echo $row['is_name']; ?></dd>
					                <dt class="sound_only">작성일</dt>
					                <dd><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo substr($row['is_time'],0,10); ?></dd>
					            </dl>
	            			</div>
	            			
	            			<div class="review_summ">
	            				<?php echo get_itemuselist_thumbnail($row['it_id'], $row['is_content'], 50, 50); ?>
	            				<p>
	            					<span><?php echo get_text($row['is_subject']); ?></span>
	            					<span class="sound_only">평가점수</span><img src="<?php echo G5_URL; ?>/shop/img/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개" width="80">
	            				</p>
	            			</div>
	            			
	            			<div id="sps_con_<?php echo $i; ?>" class="review_bt_cnt">
				                <?php echo $is_content; // 사용후기 내용 ?>
				                <?php
				                if( !empty($row['is_reply_subject']) ){     //사용후기 답변이 있다면
				                    $is_reply_content = get_view_thumbnail(conv_content($row['is_reply_content'], 1), $thumbnail_width);
				                ?>
				                <div class="sps_reply">
				                    <section>
				                        <h2 class="is_use_reply"><?php echo get_text($row['is_reply_subject']); ?></h2>
				                        <div class="sps_dl">
				                            <?php echo $row['is_reply_name']; ?>
				                        </div>
				                        <div id="sps_con_<?php echo $i; ?>_reply">
				                            <?php echo $is_reply_content; // 사용후기 답변 내용 ?>
				                        </div>
				                    </section>
				                </div>
				                <?php } //end if ?>
				            </div>
	            		</div>
	            		<button class="rd_cls"><span class="sound_only">후기 상세보기 팝업 닫기</span><i class="fa fa-times" aria-hidden="true"></i></button>
	            		</div>
	            	</div>
				</div>
				<!-- 사용후기 자세히 끝 -->
			</div>
			
			<div class="sps_info">
    			<span class="sound_only">작성자</span>
                <span class="sps_if_wt"><?php echo get_text($row['is_name']); ?></span>
                <span class="sound_only">작성일</span>
                <span><?php echo substr($row['is_time'],2,8); ?></span>
    		</div>
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
    // 사용후기 열기
    $(".review_detail").on("click", function(){
        $(this).parent("div").children(".review_detail_cnt").show();
    });

    // 사용후기 닫기
    $(document).mouseup(function (e){
        var container = $(".review_detail_cnt");
        if( container.has(e.target).length === 0)
        container.hide();
    });   
});

// 후기 상세 글쓰기 옵션
$(".sps_opt_btn_more").on("click", function() {
    $(".sps_opt_li").toggle();
})

// 후기 상세 글쓰기 닫기
$('.rd_cls').click(function(){
    $('.review_detail_cnt').hide();
});
</script>
<!-- } 전체 상품 사용후기 목록 끝 -->