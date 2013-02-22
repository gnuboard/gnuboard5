<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<h1 id="wrapper_title"><?=$g4['title']?></h1>

<form id="fwrite" name="fwrite" method="post" action="<?=$action_url?>" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data" autocomplete="off" style="width:<?=$width;?>">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="wr_id" value="<?=$wr_id?>">
<input type="hidden" name="sca" value="<?=$sca?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="spt" value="<?=$spt?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<?
$option = '';
$option_hidden = '';
if ($is_notice || $is_html || $is_secret || $is_mail) {
    $option = '';
    if ($is_notice) {
        $option .= PHP_EOL.'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice">공지</label>';
    }

    if ($is_html) {
        if ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" value="html1" name="html">';
        } else {
            $option .= PHP_EOL.'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html">html</label>';
        }
    }

    if ($is_secret) {
        if ($is_admin || $is_secret==1) {
            $option .= PHP_EOL.'<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret">비밀글</label>';
        } else {
            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
        }
    }

    if ($is_mail) {
        $option .= PHP_EOL.'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail">답변메일받기</label>';
    }
}

echo $option_hidden;
?>

<table id="bo_w" class="frm_tbl">
<tbody>
<? if ($is_name) { ?>
<tr>
    <th scope="row"><label for="wr_name">이름<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" id="wr_name" name="wr_name" class="frm_input required" size="10" maxlength="20" required value="<?=$name?>"></td>
</tr>
<? } ?>

<? if ($is_password) { ?>
<tr>
    <th scope="row"><label for="wr_password">패스워드<strong class="sound_only">필수</strong></label></th>
    <td><input type="password" id="wr_password" name="wr_password" class="frm_input" maxlength="20" <?=$password_required?>></td>
</tr>
<? } ?>

<? if ($is_email) { ?>
<tr>
    <th scope="row"><label for="wr_email">이메일</label></th>
    <td><input type="text" id="wr_email" name="wr_email" class="frm_input email" size="50" value="<?=$email?>" maxlength="100"></td>
</tr>
<? } ?>

<? if ($is_homepage) { ?>
<tr>
    <th scope="row"><label for="wr_homepage">홈페이지</label></th>
    <td><input type="text" id="wr_homepage" name="wr_homepage" class="frm_input" size="50" value="<?=$homepage?>"></td>
</tr>
<? } ?>

<? if ($option) { ?>
<tr>
    <th scope="row">옵션</th>
    <td><?=$option?></td>
</tr>
<? } ?>

<? if ($is_category) { ?>
<tr>
    <th scope="row"><label for="ca_name">분류<strong class="sound_only">필수</strong></label></th>
    <td>
        <select id="ca_name" name="ca_name" class="required" required>
            <option value="">선택하세요</option>
            <?=$category_option?>
        </select>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="wr_subject">제목<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" id="wr_subject" name="wr_subject" class="frm_input required" size="50" required value="<?=$subject?>"></td>
</tr>

<tr>
    <th scope="row"><label for="wr_content">내용<strong class="sound_only">필수</strong></label></th>
    <td class="wr_content"><?=editor_html("wr_content", $content, $is_dhtml_editor);?></td>
</tr>

<? for ($i=1; $is_link && $i<=G4_LINK_COUNT; $i++) { ?>
<tr>
    <th scope="row"><label for="wr_link<?=$i?>">링크 #<?=$i?></label></th>
    <td><input type="text" id="wr_link<?=$i?>" name="wr_link<?=$i?>" class="frm_input" size="50" value="<?if($w=="u"){echo$write['wr_link'.$i];}?>"></td>
</tr>
<? } ?>

<? for ($i=0; $is_file && $i<$file_count; $i++) { ?>
<tr>
    <th scope="row">파일 #<?=$i+1?></th>
    <td>
        <input type="file" name="bf_file[]" class="frm_file frm_input" title="파일첨부 <?=$i+1?> :  용량 <?=$upload_max_filesize?> 이하만 업로드 가능">
        <? if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" class="frm_file frm_input" value="<? echo $file[$i]['bf_content']; ?>" size="50" title="파일 설명을 입력해주세요.">
        <?}?>
        <? if($w == 'u' && $file[$i]['file']) { ?>
        <input type="checkbox" id="bf_file_del<?=$i?>" name="bf_file_del[<? echo $i; ?>]" value="1"> <label for="bf_file_del<?=$i?>"><? echo $file[$i]['source'].'('.$file[$i]['size'].')'; ?> 파일 삭제</label>
        <? } ?>
    </td>
</tr>
<?}?>

<? if ($is_guest) { //자동등록방지 ?>
<tr>
    <th scope="row">자동등록방지</th>
    <td>
        <?=$captcha_html?>
    </td>
</tr>
<? } ?>

</tbody>
</table>

<div class="btn_confirm">
    <input type="submit" id="btn_submit" class="btn_submit" value="글쓰기" accesskey="s">
    <a href="./board.php?bo_table=<?=$bo_table?>" class="btn_cancel">취소</a>
</div>
</form>

<script>
<?
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
            ca_name.value = "<?=isset($write['ca_name'])?$write['ca_name']:'';?>";
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
    <? echo get_editor_js('wr_content', $is_dhtml_editor); ?>
    <? echo chk_editor_js('wr_content', $is_dhtml_editor); ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: g4_bbs_url+"/filter.ajax.php",
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

    <? if ($is_guest) { echo chk_captcha_js(); } ?>

    return true;
}
</script>
