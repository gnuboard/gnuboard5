<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 쪽지 보내기 시작 { -->
<div id="memo_write" class="new_win">
    <h1 id="win_title">쪽지 보내기</h1>
    <div class="new_win_con2">
        <ul class="win_ul">
            <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
            <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
            <li class="selected"><a href="./memo_form.php">쪽지쓰기</a></li>
        </ul>

        <form name="fmemoform" action="<?php echo $memo_action_url; ?>" onsubmit="return fmemoform_submit(this);" method="post" autocomplete="off">
        <div class="form_01">
            <h2 class="sound_only">쪽지쓰기</h2>
            <ul>
                <li>
                    <label for="me_recv_mb_id" class="sound_only">받는 회원아이디<strong>필수</strong></label>
                    
                    <input type="text" name="me_recv_mb_id" value="<?php echo $me_recv_mb_id; ?>" id="me_recv_mb_id" required class="frm_input full_input required" size="47" placeholder="받는 회원닉네임">
                    <span class="frm_info">여러 회원에게 보낼때는 컴마(,)로 구분하세요.
                    	<?php if ($config['cf_memo_send_point']) { ?><br>쪽지 보낼때 회원당 <?php echo number_format($config['cf_memo_send_point']); ?>점의 포인트를 차감합니다.<?php } ?>
                    </span>
                </li>
                <li>
                    <label for="me_memo" class="sound_only">내용</label>
                    <textarea name="me_memo" id="me_memo" required class="required"><?php echo $content ?></textarea>
                </li>
                <li>
                    <span class="sound_only">자동등록방지</span>
                    
                    <?php echo captcha_html(); ?>
                    
                </li>
            </ul>
        </div>

        <div class="win_btn">
        	<button type="submit" id="btn_submit" class="btn btn_b02 reply_btn">보내기</button>
        	<button type="button" onclick="window.close();" class="btn_close">창닫기</button>
        </div>
    </div>
    </form>
</div>

<script>
function fmemoform_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    return true;
}
</script>
<!-- } 쪽지 보내기 끝 -->