<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo $board_skin_url ?>/style.css">

<h2 id="wrapper_title"><?php echo $g4['title'] ?></h2>

<form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="spt" value="<?php echo $spt ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<?php
$option = '';
$option_hidden = '';
if ($is_notice || $is_html || $is_secret || $is_mail) {
    $option = '';
    if ($is_notice) {
        $option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
    }

    if ($is_html) {
        if ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" value="html1" name="html">';
        } else {
            $option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">html</label>';
        }
    }

    if ($is_secret) {
        if ($is_admin || $is_secret==1) {
            $option .= "\n".'<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'<label for="secret">비밀글</label>';
        } else {
            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
        }
    }

    if ($is_mail) {
        $option .= "\n".'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'<label for="mail">답변메일받기</label>';
    }
}

echo $option_hidden;
?>

<table id="bo_w" class="frm_tbl">
<tbody>
<?php if ($is_name) { ?>
<tr>
    <th scope="row"><label for="wr_name">이름<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" name="wr_name" value="<?php echo $name ?>"id="wr_name" required class="frm_input required" size="10" maxlength="20"></td>
</tr>
<?php } ?>

<?php if ($is_password) { ?>
<tr>
    <th scope="row"><label for="wr_password">패스워드<strong class="sound_only">필수</strong></label></th>
    <td><input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" maxlength="20"></td>
</tr>
<?php } ?>

<?php if ($is_email) { ?>
<tr>
    <th scope="row"><label for="wr_email">이메일</label></th>
    <td><input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email" size="50" maxlength="100"></td>
</tr>
<?php } ?>

<?php if ($is_homepage) { ?>
<tr>
    <th scope="row"><label for="wr_homepage">홈페이지</label></th>
    <td><input type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="frm_input" size="50"></td>
</tr>
<?php } ?>

<?php if ($option) { ?>
<tr>
    <th scope="row">옵션</th>
    <td><?php echo $option ?></td>
</tr>
<?php } ?>

<?php if ($is_category) { ?>
<tr>
    <th scope="row"><label for="ca_name">분류<strong class="sound_only">필수</strong></label></th>
    <td>
        <select name="ca_name" id="ca_name" required class="required">
            <option value="">선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </td>
</tr>
<?php } ?>

<tr>
    <th scope="row"><label for="wr_subject">제목<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" name="wr_subject"  value="<?php echo $subject ?>" id="wr_subject" required class="frm_input required" size="50"></td>
</tr>

<tr>
    <th scope="row"><label for="wr_content">내용<strong class="sound_only">필수</strong></label></th>
    <td class="wr_content"><?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출  ?></td>
</tr>

<?php for ($i=1; $is_link && $i<=G4_LINK_COUNT; $i++) { ?>
<tr>
    <th scope="row"><label for="wr_link<?php echo $i ?>">링크 #<?php echo $i ?></label></th>
    <td><input type="text" name="wr_link<?php echo $i ?>" value="<?php if($w=="u"){echo$write['wr_link'.$i];} ?>" id="wr_link<?php echo $i ?>" class="frm_input" size="50"></td>
</tr>
<?php } ?>

<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
<tr>
    <th scope="row">파일 #<?php echo $i+1 ?></th>
    <td>
        <input type="file" name="bf_file[]" title="파일첨부 <?php echo $i+1 ?> :  용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
        <?php if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" value="<?php echo $file[$i]['bf_content'];  ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
        <?php } ?>
        <?php if($w == 'u' && $file[$i]['file']) { ?>
        <input type="checkbox" name="bf_file_del[<?php echo $i;  ?>]" value="1" id="bf_file_del<?php echo $i ?>"> <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일 삭제</label>
        <?php } ?>
    </td>
</tr>
<?php } ?>

<?php if ($is_guest) { //자동등록방지  ?>
<tr>
    <th scope="row">자동등록방지</th>
    <td>
        <?php echo $captcha_html ?>
    </td>
</tr>
<?php } ?>

</tbody>
</table>

<div class="btn_confirm">
    <p>
        작성하신 내용을 제출하시려면 <strong>글쓰기</strong> 버튼을, 작성을 취소하고 목록으로 돌아가시려면 <strong>취소</strong> 링크를 누르세요.
    </p>
    <input type="submit" value="글쓰기" id="btn_submit" accesskey="s" class="btn_submit">
    <a href="./board.php?bo_table=<?php echo $bo_table ?>" class="btn_cancel">취소</a>
</div>
</form>

<script>
<?php
// 관리자라면 분류 선택에 '공지' 옵션을 추가함
if ($is_admin)
{
    echo '
    if (ca_name_select = document.getElementById("ca_name")) {
        ca_name_select.options.length += 1;
        ca_name_select.options[ca_name_select.options.length-1].value = "공지";
        ca_name_select.options[ca_name_select.options.length-1].text = "공지";
    }';
}
?>

with (document.fwrite)
{
    if (typeof(wr_name) != "undefined")
        wr_name.focus();
    else if (typeof(wr_subject) != "undefined")
        wr_subject.focus();
    else if (typeof(wr_content) != "undefined")
        wr_content.focus();

    if (typeof(ca_name) != "undefined")
        if (w.value == "u") {
            ca_name.value = "<?php echo isset($write['ca_name'])?$write['ca_name']:''; ?>";
    }
}

function html_auto_br(obj)
{
    if (obj.checked) {
        result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
        if (result)
            obj.value = "html2";
        else
            obj.value = "html1";
    }
    else
        obj.value = "";
}

function fwrite_submit(f)
{
    <?php echo get_editor_js('wr_content', $is_dhtml_editor);  ?>
    <?php echo chk_editor_js('wr_content', $is_dhtml_editor);  ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: g4_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": f.wr_subject.value,
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data, textStatus) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (subject) {
        alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
        f.wr_subject.focus();
        return false;
    }

    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        if (typeof(ed_wr_content) != "undefined")
            ed_wr_content.returnFalse();
        else
            f.wr_content.focus();
        return false;
    }

    <?php if ($is_guest) { echo chk_captcha_js(); } ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>
