<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
// 글자수 제한
var char_min = parseInt(<?=$write_min?>); // 최소
var char_max = parseInt(<?=$write_max?>); // 최대
</script>

<form name="fwrite" enctype="multipart/form-data" onsubmit="return fwrite_submit(this);" method="post">
<input type="hidden" name="null">
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

<table>
<caption><?=$board['bo_subject']?> 글쓰기</caption>
<tbody>
<? if ($is_name) { ?>
<tr>
    <th scope="row"><label for="wr_name">이름</label></th>
    <td><input type="text" id="wr_name" name="wr_name" maxlength="20" required value="<?=$name?>"></td>
</tr>
<? } ?>

<? if ($is_password) { ?>
<tr>
    <th scope="row"><label for="wr_password">패스워드</label></th>
    <td><input type="password" id="wr_password" name="wr_password" maxlength="20" <?=$password_required?>></td>
</tr>
<? } ?>

<? if ($is_email) { ?>
<tr>
    <th scope="row"><label for="wr_email">이메일</label></th>
    <td><input type="text" id="wr_email" name="wr_email" maxlength="100" value="<?=$email?>"></td>
</tr>
<? } ?>

<? if ($is_homepage) { ?>
<tr>
    <th scope="row"><label for="wr_homepage">홈페이지</label></th>
    <td><input type="text" id="wr_homepage" name="wr_homepage" value="<?=$homepage?>"></td>
</tr>
<? } ?>

<?
$option = '';
$option_hidden = '';
if ($is_notice || $is_html || $is_secret || $is_mail) {
    $option = '';
    if ($is_notice) {
        $option .= '<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'><label for="notice">공지</label>'.PHP_EOL;
    }

    if ($is_html) {
        if ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" value="html1" name="html">';
        } else {
            $option .= '<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'><label for="html">html</label>'.PHP_EOL;
        }
    }

    if ($is_secret) {
        if ($is_admin || $is_secret==1) {
            $option .= '<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'><label for="secret">비밀글</label>'.PHP_EOL;
        } else {
            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
        }
    }

    if ($is_mail) {
        $option .= '<input type="checkbox" name="mail" value="mail" '.$recv_email_checked.'><label for="mail">답변메일받기</label>'.PHP_EOL;
    }
}

echo $option_hidden;
if ($option) {
?>
<tr>
    <th scope="row">옵션</th>
    <td><?=$option?></td>
</tr>
<? } ?>

<? if ($is_category) { ?>
<tr>
    <th scope="row"><label for="ca_name">분류</label></th>
    <td>
        <select id="ca_name" name="ca_name" required>
            <option value="">선택하세요<?=$category_option?>
        </select>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="wr_subject">제목</label></th>
    <td><input id="wr_subject" name="wr_subject" required value="<?=$subject?>"></td>
</tr>

<tr>
    <th scope="row"><label for="wr_content">내용</label></th>
    <td>
        <textarea id="wr_content" name="wr_content" required <? if ($write_min || $write_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?>><?=$content?></textarea>
        <? if ($write_min || $write_max) { ?><span id="char_count"></span>글자<?}?>
        <? if ($write_min || $write_max) { ?><script> check_byte('wr_content', 'char_count'); </script><?}?>
    </td>
</tr>

<? if ($is_link) { ?>
<? for ($i=1; $i<=$g4['link_count']; $i++) { ?>
<tr>
    <th scope="row"><label for="wr_link<?=$i?>">링크 #<?=$i?></label></th>
    <td><input type="text" id="wr_link<?=$i?>" name="wr_link<?=$i?>" value="<?=$write["wr_link{$i}"]?>"></td>
</tr>
<? } ?>
<? } ?>

<? if ($is_file) { ?>
<tr>
    <th scope="row"><label for="">파일첨부</label></th>
    <td>
        <table id="variableFiles"></table><?// print_r2($file); ?>
        <script>
        var flen = 0;
        function add_file(delete_code)
        {
            var upload_count = <?=(int)$board['bo_upload_count']?>;
            if (upload_count && flen >= upload_count)
            {
                alert("이 게시판은 "+upload_count+"개 까지만 파일 업로드가 가능합니다.");
                return;
            }

            var objTbl;
            var objRow;
            var objCell;
            if (document.getElementById)
                objTbl = document.getElementById("variableFiles");
            else
                objTbl = document.all["variableFiles"];

            objRow = objTbl.insertRow(objTbl.rows.length);
            objCell = objRow.insertCell(0);

            objCell.innerHTML = "<input type='file' name='bf_file[]' title='파일 용량 <?=$upload_max_filesize?> 이하만 업로드 가능'>";
            if (delete_code)
                objCell.innerHTML += delete_code;
            else
            {
                <? if ($is_file_content) { ?>
                objCell.innerHTML += "<br><input type='text' size=50 name='bf_content[]' title='업로드 이미지 파일에 해당 되는 내용을 입력하세요.'>";
                <? } ?>
                ;
            }

            flen++;
        }

        <?=$file_script; //수정시에 필요한 스크립트?>

        function del_file()
        {
            // file_length 이하로는 필드가 삭제되지 않아야 합니다.
            var file_length = <?=(int)$file_length?>;
            var objTbl = document.getElementById("variableFiles");
            if (objTbl.rows.length - 1 > file_length)
            {
                objTbl.deleteRow(objTbl.rows.length - 1);
                flen--;
            }
        }
        </script>
    </td>
</tr>

<tr>
    <td colspan="2">
        <span onclick="add_file();" style="cursor:pointer;">파일증가</span>
        <span onclick="del_file();" style="cursor:pointer;">파일감소</span>
    </td>
</tr>

<? } ?>

<? if ($is_trackback) { ?>
<tr>
    <th scope="row"><label for="wr_trackback">트랙백주소</label></th>
    <td>
        <input id="wr_trackback" name="wr_trackback" value="<?=$trackback?>">
        <? if ($w=='u') { ?><input type="checkbox" id="re_trackback" name="re_trackback" value="1">핑 보냄<? } ?>
    </td>
</tr>
<? } ?>
</tbody>
</table>

<? if ($is_guest) { ?>
<fieldset id="captcha">
    <legend>자동등록방지</legend>
    <div><img id="kcaptcha_image" alt="" /></div>
    <label for="wr_key">자동등록방지</label>
    <input type="text" id="wr_key" name="wr_key" required>
    왼쪽의 글자를 입력하세요.
</fieldset>
<? } ?>

<div class="btn_confirm">
    <input type="submit" id="btn_submit" value="글쓰기" accesskey="s">
    <a href="./board.php?bo_table=<?=$bo_table?>" title="글쓰기를 취소하고 목록으로 돌아가기">목록</a>
</div>
</form>

<script src="<?=$g4['path']?>/js/jquery.kcaptcha.js"></script>
<script>
<?
// 관리자라면 분류 선택에 '공지' 옵션을 추가함
if ($is_admin)
{
    echo "
    if (typeof(document.fwrite.ca_name) != 'undefined')
    {
        document.fwrite.ca_name.options.length += 1;
        document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].value = '공지';
        document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].text = '공지';
    }";
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
        if (w.value == "u")
            ca_name.value = "<?=$write['ca_name']?>";
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
    /*
    var s = "";
    if (s = word_filter_check(f.wr_subject.value)) {
        alert("제목에 금지단어('"+s+"')가 포함되어있습니다");
        return false;
    }

    if (s = word_filter_check(f.wr_content.value)) {
        alert("내용에 금지단어('"+s+"')가 포함되어있습니다");
        return false;
    }
    */

    if (document.getElementById('char_count')) {
        if (char_min > 0 || char_max > 0) {
            var cnt = parseInt(document.getElementById('char_count').innerHTML);
            if (char_min > 0 && char_min > cnt) {
                alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                return false;
            }
            else if (char_max > 0 && char_max < cnt) {
                alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                return false;
            }
        }
    }

    if (document.getElementById('tx_wr_content')) {
        if (!ed_wr_content.outputBodyText()) {
            alert('내용을 입력하십시오.');
            ed_wr_content.returnFalse();
            return false;
        }
    }

    <?
    if ($is_dhtml_editor) echo cheditor3('wr_content');
    ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: "<?=$board_skin_path?>/ajax.filter.php",
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

    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    //document.getElementById('btn_submit').disabled = true;
    //document.getElementById('btn_list').disabled = true;

    <?
    if ($g4['https_url'])
        echo "f.action = '{$g4['https_url']}/{$g4['bbs']}/write_update.php';";
    else
        echo "f.action = './write_update.php';";
    ?>

    return true;
}
</script>

<script src="<?=$g4['path']?>/js/board.js"></script>
<script> window.onload=function() { drawFont(); } </script>
