<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="bo_v_ans">
    <h2><span class="bo_v_reply"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i> 답변</span> <?php echo get_text($answer['qa_subject']); ?></h2>
    <a href="<?php echo $rewrite_href; ?>" class="btn add_qa"><i class="fa fa-plus-circle" aria-hidden="true"></i> 추가질문</a>

    <div id="ans_datetime">
        <i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $answer['qa_datetime']; ?>
    </div>

    <div id="ans_con">
        <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']); ?>
    </div>

    <div id="ans_add">
        <?php if($answer_update_href) { ?>
        <a href="<?php echo $answer_update_href; ?>" class="btn_b03 btn">답변수정</a>
        <?php } ?>
        <?php if($answer_delete_href) { ?>
        <a href="<?php echo $answer_delete_href; ?>" class="btn_b03 btn" onclick="del(this.href); return false;">답변삭제</a>
        <?php } ?>
    </div>
</section>