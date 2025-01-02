<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/style.css">', 0);
?>

<section id="bo_w">
    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="qa_id" value="<?php echo $qa_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">
    <?php
    $option = '';
    $option_hidden = '';
    $option = '';

    if ($is_dhtml_editor) {
        $option_hidden .= '<input type="hidden" name="qa_html" value="1">';
    } else {
        $option .= "\n".'<input type="checkbox" id="qa_html" name="qa_html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.' class="selec_chk">'."\n".'<label for="qa_html"><span></span>html</label>';
    }

    echo $option_hidden;
    ?>           	
    <div class="form_01">
        <ul>
            <?php if ($category_option) { ?>
            <li class="bo_w_select">
                <label for="qa_category" class="sound_only">분류<strong>필수</strong></label>
                <select name="qa_category" id="qa_category" required class="required">
                    <option value="">선택하세요</option>
                    <?php echo $category_option ?>
                </select>
            </li>
            <?php } ?>

            <?php if ($option) { ?>
            <li>
                <span class="sound_only">옵션</span>
                <div class="chk_op chk_box">
                <?php echo $option; ?>
                </div>
            </li>
            <?php } ?>

            <?php if ($is_email) { ?>
            <li>
                <label for="qa_email" class="sound_only">이메일</label>
                <input type="email" name="qa_email" value="<?php echo get_text($write['qa_email']); ?>" id="qa_email" <?php echo $req_email; ?> class="<?php echo $req_email.' '; ?>frm_input full_input email" maxlength="100" placeholder="이메일">
                <div class="chk_op chk_box">
					<input type="checkbox" name="qa_email_recv" value="1" id="qa_email_recv" <?php if($write['qa_email_recv']) echo 'checked="checked"'; ?> class="selec_chk">
                	<label for="qa_email_recv"><span></span>답변받기</label>                  
                </div>
            </li>
            <?php } ?>

            <?php if ($is_hp) { ?>
            <li>
                <label for="qa_hp" class="sound_only">휴대폰</label>
                <input type="text" name="qa_hp" value="<?php echo get_text($write['qa_hp']); ?>" id="qa_hp" <?php echo $req_hp; ?> class="<?php echo $req_hp.' '; ?>frm_input full_input" size="30" placeholder="휴대폰">
                <?php if($qaconfig['qa_use_sms']) { ?>
                <div class="chk_op chk_box">
                	<input type="checkbox" name="qa_sms_recv" value="1" id="qa_sms_recv" <?php if($write['qa_sms_recv']) echo 'checked="checked"'; ?> class="selec_chk">
                	<label for="qa_sms_recv"><span></span>답변등록 SMS알림 수신</label>                  
                </div>
                <?php } ?>
            </li>
            <?php } ?>

            <li class="bo_w_tit">
                <label for="qa_subject" class="sound_only">제목<strong>필수</strong></label>
                <input type="text" name="qa_subject" value="<?php echo get_text($write['qa_subject']); ?>" id="qa_subject" required class="frm_input full_input required" maxlength="255" placeholder="제목">
            </li>

            <li>
               <label for="qa_content" class="sound_only">내용<strong>필수</strong></label>
                <div class="wr_content">
                    <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
                </div>
            </li>

            <li class="bo_w_flie">
                <div class="file_wr filebox">
                	<input type="text" class="fileName" readonly="readonly" placeholder="파일을 첨부하세요">
                    <label for="bf_file[1]"><i class="fa fa-download lb_icon" aria-hidden="true"></i><span class="sound_only">파일 #1</span><span class="btn_file">파일첨부</span></label>
                    <input type="file" name="bf_file[1]" id="bf_file[1]" title="파일첨부 1 :  용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file uploadBtn">
                	<?php if($w == 'u' && $write['qa_file1']) { ?>
                		<input type="checkbox" id="bf_file_del1" name="bf_file_del[1]" value="1">
                		<label for="bf_file_del1"><?php echo $write['qa_source1']; ?> 파일 삭제</label>
                	<?php } ?>
                </div>
				<br>
                <div class="file_wr filebox">
                	<input type="text" class="fileName" readonly="readonly" placeholder="파일을 첨부하세요">
                    <label for="bf_file[2]"><i class="fa fa-download lb_icon" aria-hidden="true"></i><span class="sound_only">파일 #2</span><span class="btn_file">파일첨부</span></label>
                    <input type="file" name="bf_file[2]" id="bf_file[2]" title="파일첨부 2 :  용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file uploadBtn">
                    <?php if($w == 'u' && $write['qa_file2']) { ?>
                    <input type="checkbox" id="bf_file_del2" name="bf_file_del[2]" value="1">
                    <label for="bf_file_del2"><?php echo $write['qa_source2']; ?> 파일 삭제</label>
                    <?php } ?>
                </div>
            </li>

        </ul>
    </div>

    <div class="btn_confirm">
        <a href="<?php echo $list_href; ?>" class="btn_cancel">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit">작성완료</button>
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

        <?php if ($is_hp) { ?>
        var hp = f.qa_hp.value.replace(/[0-9\-]/g, "");
        if(hp.length > 0) {
            alert("휴대폰번호는 숫자, - 으로만 입력해 주십시오.");
            return false;
        }
        <?php } ?>

        $.ajax({
            type: "POST",
            url: g5_bbs_url+"/ajax.write.token.php",
            data: { 'token_case' : 'qa_write' },
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                if (typeof data.token !== "undefined") {
                    token = data.token;

                    if(typeof f.token === "undefined")
                        $(f).prepend('<input type="hidden" name="token" value="">');

                    $(f).find("input[name=token]").val(token);
                }
            }
        });

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    var uploadFile = $('.filebox .uploadBtn');
	uploadFile.on('change', function(){
		if(window.FileReader){
			var filename = $(this)[0].files[0].name;
		} else {
			var filename = $(this).val().split('/').pop().split('\\').pop();
		}
		$(this).siblings('.fileName').val(filename);
	});
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->