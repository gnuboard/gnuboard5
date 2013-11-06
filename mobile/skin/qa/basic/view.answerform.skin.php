<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="bo_v_ans">
    <?php
    if($is_admin) // 관리자이면 답변등록
    {
    ?>
    <h2>답변등록</h2>

    <form name="fanswer" method="post" action="./qawrite_update.php" autocomplete="off">
    <input type="hidden" name="qa_id" value="<?php echo $view['qa_id']; ?>">
    <input type="hidden" name="w" value="a">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <?php
    $option = '';
    $option_hidden = '';
    $option = '';

    if ($is_dhtml_editor) {
        $option_hidden .= '<input type="hidden" name="qa_html" value="1">';
    } else {
        $option .= "\n".'<input type="checkbox" id="qa_html" name="qa_html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="qa_html">html</label>';
    }

    echo $option_hidden;
    ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <tbody>
        <?php if ($option) { ?>
        <tr>
            <th scope="row">옵션</th>
            <td><?php echo $option; ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th><label for="qa_subject">제목</label></th>
            <td><input type="text" name="qa_subject" value="" id="qa_subject" required class="frm_input required" size="50" maxlength="255"></td>
        </tr>
        <tr>
        <th scope="row"><label for="qa_content">내용<strong class="sound_only">필수</strong></label></th>
            <td class="wr_content">
                <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm">
        <input type="submit" value="답변쓰기" id="btn_submit" accesskey="s" class="btn_submit">
    </div>
    </form>

    <script>
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "2";
            else
                obj.value = "1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.qa_subject.value,
                "content": f.qa_content.value
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
            f.qa_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_qa_content) != "undefined")
                ed_qa_content.returnFalse();
            else
                f.qa_content.focus();
            return false;
        }

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
    <?php
    }
    else
    {
    ?>
    <p id="ans_msg">고객님의 문의에 대한 답변을 준비 중입니다.</p>
    <?php
    }
    ?>
</section>