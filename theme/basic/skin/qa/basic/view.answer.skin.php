<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="bo_v_ans">
    <h2><span class="bo_v_reply">답변</span> <?php echo get_text($answer['qa_subject']); ?></h2>
    <header>
	    <div id="ans_datetime">
	        <i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $answer['qa_datetime']; ?>
	    </div>
	    
        <?php if ( $answer_update_href || $answer_delete_href ){ ?>
	    <div id="ans_add">
	    	<button type="button" class="btn_more_add btn_more_opt btn_b01 btn" title="답변 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">답변 옵션</span></button>
			<ul class="more_add">
				<?php if($answer_update_href) { ?>
				<li><a href="<?php echo $answer_update_href; ?>" class="btn_b01 btn" title="답변수정">답변수정</a></li>
				<?php } ?>
				<?php if($answer_delete_href) { ?>
				<li><a href="<?php echo $answer_delete_href; ?>" class="btn_b01 btn" onclick="del(this.href); return false;" title="답변삭제">답변삭제</a></li>
				<?php } ?>	
			</ul>
			<script>
				// 답변하기 옵션
				$(".btn_more_add").on("click", function() {
				    $(".more_add").toggle();
				})
			</script>
	    </div>
        <?php } ?>
	</header>
	
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

</section>
<div class="bo_v_btn">
	<a href="<?php echo $rewrite_href; ?>" class="add_qa" title="추가질문">추가질문</a>  
</div>
