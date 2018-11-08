<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v">
    <div class="bo_v_wr">
        <header>
            <h2 id="bo_v_title">
                <span><?php echo $view['category'] ; // 분류 출력 끝 ?></span>
                <?php echo $view['subject']; // 글제목 출력  ?>
            </h2>
        </header>
        <section id="bo_v_info">
            <h2>페이지 정보</h2>
            <span class="sound_only">작성자</span><strong><?php echo $view['name'] ?></strong>
            <span class="sound_only">작성일</span><strong class="info_date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $view['datetime']; ?></strong>
        </section>

        <?php if($view['email'] || $view['hp']) { ?>
        <section id="bo_v_contact">
            <h2>연락처정보</h2>
            <dl>
                <?php if($view['email']) { ?>
                <dt><i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">이메일</span></dt>
                <dd><?php echo $view['email']; ?></dd>
                <?php } ?>
                <?php if($view['hp']) { ?>
                <dt><i class="fa fa fa-phone" aria-hidden="true"></i><span class="sound_only">휴대폰</span></dt>
                <dd><?php echo $view['hp']; ?></dd>
                <?php } ?>
            </dl>
        </section>
        <?php } ?>

        <section id="bo_v_atc">
            <h2 id="bo_v_atc_title">본문</h2>

            <?php
            // 파일 출력
            if($view['img_count']) {
                echo "<div id=\"bo_v_img\">\n";

                for ($i=0; $i<$view['img_count']; $i++) {
                    //echo $view['img_file'][$i];
                    echo get_view_thumbnail($view['img_file'][$i], $qaconfig['qa_image_width']);
                }

                echo "</div>\n";
            }
             ?>

            <!-- 본문 내용 시작 { -->
            <div id="bo_v_con"><?php echo get_view_thumbnail($view['content'], $qaconfig['qa_image_width']); ?></div>
            <!-- } 본문 내용 끝 -->

            <?php if($view['qa_type']) { ?>
            <div><a href="<?php echo $rewrite_href; ?>" class="btn_b01">추가질문</a></div>
            <?php } ?>


            <?php if($view['download_count']) { ?>
            <!-- 첨부파일 시작 { -->
            <section id="bo_v_file">
                <h2>첨부파일</h2>
                <ul>
                <?php
                // 가변 파일
                for ($i=0; $i<$view['download_count']; $i++) {
                 ?>
                    <li>
                        <a href="<?php echo $view['download_href'][$i];  ?>" class="view_file_download">
                            <img src="<?php echo $qa_skin_url ?>/img/icon_file.gif" alt="첨부">
                            <strong><?php echo $view['download_source'][$i] ?></strong>
                        </a>
                    </li>
                <?php
                }
                 ?>
                </ul>
            </section>
            <!-- } 첨부파일 끝 -->
            <?php } ?>


        </section>
    </div>


    <div class="btn_top top">
        <ul>
             <li><a href="<?php echo $list_href ?>" class="btn_b01 "><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
    </div>
    <!-- 게시물 상단 버튼 시작 { -->
    <div id="bo_v_top">
        <?php
        ob_start();
         ?>
        <?php if ($prev_href || $next_href) { ?>
        <ul class="bo_v_nb">
            <?php if ($prev_href) { ?><li><a href="<?php echo $prev_href ?>" class="btn_b01 btn"><i class="fa fa-angle-left" aria-hidden="true"></i> 이전글</a></li><?php } ?>
            <?php if ($next_href) { ?><li><a href="<?php echo $next_href ?>" class="btn_b01 btn">다음글 <i class="fa fa-angle-right" aria-hidden="true"></i></a></li><?php } ?>
        </ul>
        <?php } ?>

        <ul class="bo_v_com">
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="btn_b01 btn" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
        </ul>
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
         ?>
    </div>
    <!-- } 게시물 상단 버튼 끝 -->

    <?php
    // 질문글에서 답변이 있으면 답변 출력, 답변이 없고 관리자이면 답변등록폼 출력
    if(!$view['qa_type']) {
        if($view['qa_status'] && $answer['qa_id'])
            include_once($qa_skin_path.'/view.answer.skin.php');
        else
            include_once($qa_skin_path.'/view.answerform.skin.php');
    }
    ?>

    <?php if($view['rel_count']) { ?>
    <section id="bo_v_rel">
        <h2>연관질문</h2>

        <div class="list_01">

            <ul>
            <?php
                for($i=0; $i<$view['rel_count']; $i++) {
                ?>
                <li>
                    <div class="li_title">
                        <strong><?php echo get_text($rel_list[$i]['category']); ?></strong>
                    
                        <a href="<?php echo $rel_list[$i]['view_href']; ?>" class="li_sbj">
                            <?php echo $rel_list[$i]['subject']; ?>
                        </a>
                    </div>
                    <div class="li_info">
                        <span class="li_stat <?php echo ($list[$i]['qa_status'] ? 'txt_done' : 'txt_rdy'); ?>"><?php echo ($rel_list[$i]['qa_status'] ? '답변완료' : '답변대기'); ?></span>
                        <span class="li_date"><?php echo $rel_list[$i]['date']; ?></span>
                    </div>
                </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </section>
    <?php } ?>



</article>
<!-- } 게시판 읽기 끝 -->

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();
});
</script>