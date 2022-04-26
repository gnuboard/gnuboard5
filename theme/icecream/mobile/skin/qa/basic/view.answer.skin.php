<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="bo_v_ans" class="bo_v_wr">
    <h2><span class="tit_rpl">답변</span><span><?php echo get_text($answer['qa_subject']); ?></span></h2>

    <div id="ans_datetime">
        <i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $answer['qa_datetime']; ?>
    </div>
    <div id="ans_con">
        <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']); ?>
    </div>

    <div id="ans_add">
        <?php if($answer_update_href) { ?>
        <a href="<?php echo $answer_update_href; ?>" class="btn_b01 btn">답변수정</a>
        <?php } ?>
        <?php if($answer_delete_href) { ?>
        <a href="<?php echo $answer_delete_href; ?>" class="btn_b01 btn" onclick="del(this.href); return false;">답변삭제</a>
        <?php } ?>
        <a href="<?php echo $rewrite_href; ?>" class="btn_b02 btn">추가질문</a>

    </div>
</section>