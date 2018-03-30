<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 스크랩 시작 { -->
<div id="scrap_do" class="new_win">
    <h1 id="win_title">스크랩하기</h1>
    <form name="f_scrap_popin" action="./scrap_popin_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <div class="new_win_con">

        <div class="form_01">
            <h2 class="sound_only">제목 확인 및 댓글 쓰기</h2>
            <ul>
                <li class="scrap_tit">
                    <span class="sound_only">제목</span>
                    <?php echo get_text(cut_str($write['wr_subject'], 255)) ?>
                </li>
                <li>
                    <label for="wr_content">댓글작성</label>
                    <td><textarea name="wr_content" id="wr_content"></textarea></td>
                </li>
            </ul>
        </div>

        <p class="win_desc">
            스크랩을 하시면서 감사 혹은 격려의 댓글을 남기실 수 있습니다.
        </p>

        <div class="win_btn">
            <input type="submit" value="스크랩 확인" class="btn_submit">
        </div>
    </div>
    </form>
</div>
<!-- } 스크랩 끝 -->