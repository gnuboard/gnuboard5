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
        <?php
        // 파일 출력
        if(isset($answer['img_count']) && $answer['img_count']) {
            echo "<div id=\"bo_v_img\">\n";

            for ($i=0; $i<$answer['img_count']; $i++) {
                echo get_view_thumbnail($answer['img_file'][$i], $qaconfig['qa_image_width']);
            }

            echo "</div>\n";
        }
        ?>

        <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']); ?>

        <?php if(isset($answer['download_count']) && $answer['download_count']) { ?>
        <!-- 첨부파일 시작 { -->
        <section id="bo_v_file">
            <h2>첨부파일</h2>
            <ul>
            <?php
            // 가변 파일
            for ($i=0; $i<$answer['download_count']; $i++) {
             ?>
                <li>
                    <i class="fa fa-download" aria-hidden="true"></i>
                    <a href="<?php echo $answer['download_href'][$i];  ?>" class="view_file_download" download>
                        <strong><?php echo $answer['download_source'][$i] ?></strong>
                    </a>
                </li>
            <?php
            }
             ?>
            </ul>
        </section>
        <!-- } 첨부파일 끝 -->
        <?php } ?>
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