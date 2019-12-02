<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품문의 쓰기 시작 { -->
<div id="sit_qa_write" class="new_win">
    <h1 id="win_title">상품문의 쓰기</h1>

    <form name="fitemqa" method="post" action="<?php echo G5_SHOP_URL;?>/itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">

    <div class="form_01 new_win_con">
        <ul>
            <li class="chk_box">
                <strong class="sound_only">옵션</strong>
                <input type="checkbox" name="iq_secret" id="iq_secret" value="1" <?php echo $chk_secret; ?> class="selec_chk">
                <label for="iq_secret"><span></span>비밀글</label> 
            </li>
            <li>
                <div class="form_left">
                    <label for="iq_email" class="sound_only">이메일</label>
                    <input type="text" name="iq_email" id="iq_email" value="<?php echo get_text($qa['iq_email']); ?>" class="frm_input full_input" size="30" placeholder="이메일"><br>
                    <span class="frm_info">이메일을 입력하시면 답변 등록 시 답변이 이메일로 전송됩니다.</span>
                </div>
                <div class="form_right">
                    <label for="iq_hp" class="sound_only">휴대폰</label>
                    <input type="text" name="iq_hp" id="iq_hp" value="<?php echo get_text($qa['iq_hp']); ?>" class="frm_input full_input" size="20" placeholder="휴대폰"><br>
                    <span class="frm_info">휴대폰번호를 입력하시면 답변 등록 시 답변등록 알림이 SMS로 전송됩니다.</span>
                </div>
            </li>
            <li>
                <label for="iq_subject" class="sound_only">제목<strong> 필수</strong></label>
                <input type="text" name="iq_subject" value="<?php echo get_text($qa['iq_subject']); ?>" id="iq_subject" required class="required frm_input" maxlength="250" placeholder="제목">
            </li>
            <li>
                <label for="iq_question" class="sound_only">질문</label>
                <?php echo $editor_html; ?>
            </li>
        </ul>
        
        <div class="win_btn">
            <button type="submit" class="btn_submit">작성완료</button>
            <button type="button" onclick="self.close();" class="btn_close">닫기</button>
        </div>
    </div>
    </form>
</div>

<script type="text/javascript">
function fitemqa_submit(f)
{
    <?php echo $editor_js; ?>

    return true;
}
</script>
<!-- } 상품문의 쓰기 끝 -->