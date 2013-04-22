<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?=$board_skin_url?>/style.css">

<h1 id="wrapper_title"><?=$g4['title']?></h1>

<form name="fwrite" id="fwrite" action="<?=$action_url?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?=$width;?>">
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
<? if ($is_name) { ?>
<tr>
    <th scope="row"><label for="wr_name">이름<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" name="wr_name" value="<?=$name?>" id="wr_name" required class="frm_input required" size="10" maxlength="20"></td>
</tr>
<? } ?>

<? if ($is_password) { ?>
<tr>
    <th scope="row"><label for="wr_password">패스워드<strong class="sound_only">필수</strong></label></th>
    <td><input type="password" name="wr_password" id="wr_password" <?=$password_required?> class="frm_input <?=$password_required?>" maxlength="20"></td>
</tr>
<? } ?>

<? if ($is_email) { ?>
<tr>
    <th scope="row"><label for="wr_email">이메일</label></th>
    <td><input type="text" name="wr_email" value="<?=$email?>" id="wr_email" class="frm_input email" size="50" maxlength="100"></td>
</tr>
<? } ?>

<? if ($is_homepage) { ?>
<tr>
    <th scope="row"><label for="wr_homepage">홈페이지</label></th>
    <td><input type="text" name="wr_homepage" value="<?=$homepage?>" id="wr_homepage" class="frm_input" size="50"></td>
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
        <select name="ca_name" id="ca_name" required class="required" >
            <option value="">선택하세요</option>
            <?=$category_option?>
        </select>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="wr_subject">제목<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" name="wr_subject" value="<?=$subject?>" id="wr_subject" required class="frm_input required" size="50"></td>
</tr>

<tr>
    <th scope="row"><label for="wr_content">내용<strong class="sound_only">필수</strong></label></th>
    <td class="wr_content"><? echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?></td>
</tr>

<? for ($i=1; $is_link && $i<=G4_LINK_COUNT; $i++) { ?>
<tr>
    <th scope="row"><label for="wr_link<?=$i?>">링크 #<?=$i?></label></th>
    <td><input type="text" name="wr_link<?=$i?>" value="<?if($w=="u"){echo$write['wr_link'.$i];}?>" id="wr_link<?=$i?>" class="frm_input" size="50"></td>
</tr>
<? } ?>

<? for ($i=0; $is_file && $i<$file_count; $i++) { ?>
<tr>
    <th scope="row">파일 #<?=$i+1?></th>
    <td>
        <input type="file" name="bf_file[]" title="파일첨부 <?=$i+1?> :  용량 <?=$upload_max_filesize?> 이하만 업로드 가능" class="frm_file frm_input">
        <? if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" value="<? echo $file[$i]['bf_content']; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
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
    <p>
       작성하신 내용을 제출하시려면 <strong>글쓰기</strong> 버튼을, 작성을 취소하고 목록으로 돌아가시려면 <strong>취소</strong> 링크를 누르세요.
    </p>
    <input type="submit" value="글쓰기" id="btn_submit" accesskey="s" class="btn_submit">
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
    <? echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함  ?>

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

    <? echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함 ?>

    return true;
}
</script>
