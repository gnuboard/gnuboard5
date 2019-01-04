<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 폼메일 시작 { -->
<div id="formmail" class="new_win">
    <h1 id="win_title"><?php echo $name ?>님께 메일보내기</h1>

    <form name="fformmail" action="./formmail_send.php" onsubmit="return fformmail_submit(this);" method="post" enctype="multipart/form-data" style="margin:0px;">
    <input type="hidden" name="to" value="<?php echo $email ?>">
    <input type="hidden" name="attach" value="2">
    <?php if ($is_member) { // 회원이면  ?>
    <input type="hidden" name="fnick" value="<?php echo get_text($member['mb_nick']) ?>">
    <input type="hidden" name="fmail" value="<?php echo $member['mb_email'] ?>">
    <?php }  ?>

    <div class="form_01 new_win_con">
        <h2 class="sound_only">메일쓰기</h2>
        <ul>
            <?php if (!$is_member) {  ?>
            <li>
                <label for="fnick" class="sound_only">이름<strong>필수</strong></label>
                <input type="text" name="fnick" id="fnick" required class="frm_input full_input required" placeholder="이름">
            </li>
            <li>
                <label for="fmail" class="sound_only">E-mail<strong>필수</strong></label>
                <input type="text" name="fmail"  id="fmail" required class="frm_input full_input required" placeholder="E-mail">
            </li>
            <?php }  ?>
            <li>
                <label for="subject" class="sound_only">제목<strong>필수</strong></label>
                <input type="text" name="subject" id="subject" required class="frm_input full_input required"  placeholder="제목">
            </li>
            <li class="chk_box">
                <span class="sound_only">형식</span>
                <input type="radio" name="type" value="0" id="type_text" checked>
                <label for="type_text"><span></span>TEXT</label>
                
                <input type="radio" name="type" value="1" id="type_html">
                <label for="type_html"><span></span>HTML</label>
                
                <input type="radio" name="type" value="2" id="type_both">
                <label for="type_both"><span></span>TEXT+HTML</label>
            </li>
            <li>
                <label for="content" class="sound_only">내용<strong>필수</strong></label>
                <textarea name="content" id="content" required class="required"></textarea>
            </li>
            <li class="formmail_flie">
                <div class="file_wr">
                    <label for="file1" class="lb_icon"><i class="fa fa-download" aria-hidden="true"></i><span class="sound_only"> 첨부 파일 1</span></label>
                    <input type="file" name="file1"  id="file1"  class="frm_file full_input">
               </div>
               <div class="frm_info">첨부 파일은 누락될 수 있으므로 메일을 보낸 후 파일이 첨부 되었는지 반드시 확인해 주시기 바랍니다.</div>   
            </li>
            <li class="formmail_flie">
                <div class="file_wr">
                    <label for="file2" class="lb_icon"><i class="fa fa-download" aria-hidden="true"></i><span class="sound_only"> 첨부 파일 2</span></label>
                    <input type="file" name="file2" id="file2" class="frm_file full_input">
                </div>
            </li>
            <li>
                <span class="sound_only">자동등록방지</span>
                <?php echo captcha_html(); ?>
            </li>
        </ul>
        <div class="win_btn">
        	<button type="submit" id="btn_submit" class="btn_b02 reply_btn">메일발송</button>
            <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
        </div>
    </div>


    </form>
</div>

<script>
with (document.fformmail) {
    if (typeof fname != "undefined")
        fname.focus();
    else if (typeof subject != "undefined")
        subject.focus();
}

function fformmail_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    if (f.file1.value || f.file2.value) {
        // 4.00.11
        if (!confirm("첨부파일의 용량이 큰경우 전송시간이 오래 걸립니다.\n\n메일보내기가 완료되기 전에 창을 닫거나 새로고침 하지 마십시오."))
            return false;
    }

    document.getElementById('btn_submit').disabled = true;

    return true;
}
</script>
<!-- } 폼메일 끝 -->