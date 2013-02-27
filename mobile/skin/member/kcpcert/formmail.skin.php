<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div id="formmail" class="new_win">
    <h1><?=$name?>님께 메일보내기</h1>

    <form name="fformmail" method="post" action="./formmail_send.php" onsubmit="return fformmail_submit(this);" enctype="multipart/form-data" style="margin:0px;">
    <input type="hidden" name="to" value="<?=$email?>">
    <input type="hidden" name="attach" value="2">
    <input type="hidden" name="token" value="<?=$token?>">
    <? if ($is_member) { // 회원이면 ?>
    <input type="hidden" name="fnick" value="<?=$member['mb_nick']?>">
    <input type="hidden" name="fmail" value="<?=$member['mb_email']?>">
    <? } ?>
    <table class="frm_tbl">
    <caption>메일쓰기</caption>
    <tbody>
    <? if (!$is_member) { ?>
    <tr>
        <th scope="row"><label for="fnick">이름<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="fnick" name="fnick" class="frm_input required" required></td>
    </tr>
    <tr>
        <th scope="row"><label for="fmail">E-mail<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="fmail" name="fmail" class="frm_input required" required></td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row"><label for="subject">제목<strong class="sound_only">필수</strong></label></th>
        <td><input type=text id="subject" name="subject" class="frm_input required" required></td>
    </tr>
    <tr>
        <th scope="row">형식</th>
        <td>
            <input type="radio" id="type_text" name="type" value="0" checked> <label for="type_text">TEXT</label>
            <input type="radio" id="type_html" name="type" value="1" > <label for="type_html">HTML</label>
            <input type="radio" id="type_both" name="type" value="2" > <label for="type_both">TEXT+HTML</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="content">내용<strong class="sound_only">필수</strong></label></th>
        <td><textarea id="content" name="content" class="required" required></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="file1">첨부 1</label></th>
        <td><input type="file" id="file1" name="file1" class="frm_input"></td>
    </tr>
    <tr>
        <th scope="row"><label for="file2">첨부 2</label></th>
        <td><input type="file" id="file2" name="file2" class="frm_input"></td>
    </tr>
    <tr>
        <th scope="row">자동등록방지</th>
        <td><?=captcha_html();?></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_win">
        <input type="submit" id="btn_submit" class="btn_submit" value="메일발송">
        <a href="javascript:window.close();">창닫기</a>
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
    <? echo chk_captcha_js(); ?>

    if (f.file1.value || f.file2.value) {
        // 4.00.11
        if (!confirm("첨부파일의 용량이 큰경우 전송시간이 오래 걸립니다.\n\n메일보내기가 완료되기 전에 창을 닫거나 새로고침 하지 마십시오."))
            return false;
    }

    document.getElementById('btn_submit').disabled = true;

    return true;
}
</script>
