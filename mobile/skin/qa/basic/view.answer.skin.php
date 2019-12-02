<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="bo_v_ans" class="bo_v_wr">
    <h2>
    	<span class="tit_rpl">답변</span>
    	<span class="tit_cnt"><?php echo get_text($answer['qa_subject']); ?></span>
    	<div id="ans_datetime">
	        <i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $answer['qa_datetime']; ?>
	    </div>
    </h2>
    <div id="ans_con">
        <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']); ?>
    </div>
	
	<button id="btn_ans_btn" class="btn_b03 btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
    <div id="ans_add" class="ans_more_opt">
        <?php if($answer_update_href) { ?>
        <a href="<?php echo $answer_update_href; ?>">답변수정</a>
        <?php } ?>
        <?php if($answer_delete_href) { ?>
        <a href="<?php echo $answer_delete_href; ?>" onclick="del(this.href); return false;">답변삭제</a>
        <?php } ?>
    </div>
</section>
<a href="<?php echo $rewrite_href; ?>" class="add_qu">추가질문</a>

<script>
// 답변 글쓰기 관리자 옵션
$("#btn_ans_btn").on("click", function() {
    $(".ans_more_opt").toggle();
})
</script>