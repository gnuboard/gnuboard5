<?php
$sub_menu = '300600';
require_once './_common.php';
require_once G5_EDITOR_LIB;

auth_check_menu($auth, $sub_menu, "w");

$co_id = isset($_REQUEST['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['co_id']) : '';

// 상단, 하단 파일경로 필드 추가
if (!sql_query(" select co_include_head from {$g5['content_table']} limit 1 ", false)) {
    $sql = " ALTER TABLE `{$g5['content_table']}`  ADD `co_include_head` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `co_include_tail` VARCHAR( 255 ) NOT NULL ";
    sql_query($sql, false);
}

// html purifier 사용여부 필드
if (!sql_query(" select co_tag_filter_use from {$g5['content_table']} limit 1 ", false)) {
    sql_query(
        " ALTER TABLE `{$g5['content_table']}`
                    ADD `co_tag_filter_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `co_content` ",
        true
    );
    sql_query(" update {$g5['content_table']} set co_tag_filter_use = '1' ");
}

// 모바일 내용 추가
if (!sql_query(" select co_mobile_content from {$g5['content_table']} limit 1", false)) {
    sql_query(
        " ALTER TABLE `{$g5['content_table']}`
                    ADD `co_mobile_content` longtext NOT NULL AFTER `co_content` ",
        true
    );
}

// 스킨 설정 추가
if (!sql_query(" select co_skin from {$g5['content_table']} limit 1 ", false)) {
    sql_query(
        " ALTER TABLE `{$g5['content_table']}`
                    ADD `co_skin` varchar(255) NOT NULL DEFAULT '' AFTER `co_mobile_content`,
                    ADD `co_mobile_skin` varchar(255) NOT NULL DEFAULT '' AFTER `co_skin` ",
        true
    );
    sql_query(" update {$g5['content_table']} set co_skin = 'basic', co_mobile_skin = 'basic' ");
}

$html_title = "내용";
$g5['title'] = $html_title . ' 관리';
$readonly = '';

if ($w == "u") {
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g5['content_table']} where co_id = '$co_id' ";
    $co = sql_fetch($sql);
    if (!$co['co_id']) {
        alert('등록된 자료가 없습니다.');
    }
} else {
    $html_title .= ' 입력';
    $co = array(
        'co_id' => '',
        'co_subject' => '',
        'co_content' => '',
        'co_mobile_content' => '',
        'co_include_head' => '',
        'co_include_tail' => '',
        'co_tag_filter_use' => 1,
        'co_html' => 2,
        'co_skin' => 'basic',
        'co_mobile_skin' => 'basic'
    );
}

require_once G5_ADMIN_PATH . '/admin.head.php';
?>

<form name="frmcontentform" action="./contentformupdate.php" onsubmit="return frmcontentform_check(this);" method="post" enctype="MULTIPART/FORM-DATA">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="co_html" value="1">
    <input type="hidden" name="token" value="">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="co_id">ID</label></th>
                    <td>
                        <?php echo help('20자 이내의 영문자, 숫자, _ 만 가능합니다.'); ?>
                        <input type="text" value="<?php echo $co['co_id']; ?>" name="co_id" id="co_id" required <?php echo $readonly; ?> class="required <?php echo $readonly; ?> frm_input" size="20" maxlength="20">
                        <?php if ($w == 'u') { ?><a href="<?php echo get_pretty_url('content', $co_id); ?>" class="btn_frmline">내용확인</a><?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_subject">제목</label></th>
                    <td><input type="text" name="co_subject" value="<?php echo htmlspecialchars2($co['co_subject']); ?>" id="co_subject" required class="frm_input required" size="90"></td>
                </tr>
                <tr>
                    <th scope="row">내용</th>
                    <td><?php echo editor_html('co_content', get_text(html_purifier($co['co_content']), 0)); ?></td>
                </tr>
                <tr>
                    <th scope="row">모바일 내용</th>
                    <td><?php echo editor_html('co_mobile_content', get_text(html_purifier($co['co_mobile_content']), 0)); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_skin">스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
                    <td>
                        <?php echo get_skin_select('content', 'co_skin', 'co_skin', $co['co_skin'], 'required'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_mobile_skin">모바일스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
                    <td>
                        <?php echo get_mobile_skin_select('content', 'co_mobile_skin', 'co_mobile_skin', $co['co_mobile_skin'], 'required'); ?>
                    </td>
                </tr>
                <!--
    <tr>
        <th scope="row"><label for="co_tag_filter_use">태그 필터링 사용</label></th>
        <td>
            <?php echo help("내용에서 iframe 등의 태그를 사용하려면 사용안함으로 선택해 주십시오."); ?>
            <select name="co_tag_filter_use" id="co_tag_filter_use">
                <option value="1"<?php echo get_selected($co['co_tag_filter_use'], 1); ?>>사용함</option>
                <option value="0"<?php echo get_selected($co['co_tag_filter_use'], 0); ?>>사용안함</option>
            </select>
        </td>
    </tr>
    -->
                <tr>
                    <th scope="row"><label for="co_include_head">상단 파일 경로</label></th>
                    <td>
                        <?php echo help("설정값이 없으면 기본 상단 파일을 사용합니다."); ?>
                        <input type="text" name="co_include_head" value="<?php echo get_sanitize_input($co['co_include_head']); ?>" id="co_include_head" class="frm_input" size="60">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_include_tail">하단 파일 경로</label></th>
                    <td>
                        <?php echo help("설정값이 없으면 기본 하단 파일을 사용합니다."); ?>
                        <input type="text" name="co_include_tail" value="<?php echo get_sanitize_input($co['co_include_tail']); ?>" id="co_include_tail" class="frm_input" size="60">
                    </td>
                </tr>
                <tr id="admin_captcha_box" style="display:none;">
                    <th scope="row">자동등록방지</th>
                    <td>
                        <?php
                        echo help("파일 경로를 입력 또는 수정시 캡챠를 반드시 입력해야 합니다.");

                        require_once G5_CAPTCHA_PATH . '/captcha.lib.php';
                        $captcha_html = captcha_html();
                        $captcha_js   = chk_captcha_js();
                        echo $captcha_html;
                        ?>
                        <script>
                            jQuery("#captcha_key").removeAttr("required").removeClass("required");
                        </script>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_himg">상단이미지</label></th>
                    <td>
                        <input type="file" name="co_himg" id="co_himg">
                        <?php
                        $himg = G5_DATA_PATH . '/content/' . $co['co_id'] . '_h';
                        $himg_str = '';
                        if (file_exists($himg)) {
                            $size = @getimagesize($himg);
                            if ($size[0] && $size[0] > 750) {
                                $width = 750;
                            } else {
                                $width = $size[0];
                            }

                            echo '<input type="checkbox" name="co_himg_del" value="1" id="co_himg_del"> <label for="co_himg_del">삭제</label>';
                            $himg_str = '<img src="' . G5_DATA_URL . '/content/' . $co['co_id'] . '_h" width="' . $width . '" alt="">';
                        }
                        if ($himg_str) {
                            echo '<div class="banner_or_img">';
                            echo $himg_str;
                            echo '</div>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_timg">하단이미지</label></th>
                    <td>
                        <input type="file" name="co_timg" id="co_timg">
                        <?php
                        $timg = G5_DATA_PATH . '/content/' . $co['co_id'] . '_t';
                        $timg_str = '';
                        if (file_exists($timg)) {
                            $size = @getimagesize($timg);
                            if ($size[0] && $size[0] > 750) {
                                $width = 750;
                            } else {
                                $width = $size[0];
                            }

                            echo '<input type="checkbox" name="co_timg_del" value="1" id="co_timg_del"> <label for="co_timg_del">삭제</label>';
                            $timg_str = '<img src="' . G5_DATA_URL . '/content/' . $co['co_id'] . '_t" width="' . $width . '" alt="">';
                        }
                        if ($timg_str) {
                            echo '<div class="banner_or_img">';
                            echo $timg_str;
                            echo '</div>';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <a href="./contentlist.php" class="btn btn_02">목록</a>
        <input type="submit" value="확인" class="btn btn_submit" accesskey="s">
    </div>

</form>

<?php
// [KVE-2018-2089] 취약점 으로 인해 파일 경로 수정시에만 자동등록방지 코드 사용
?>
<script>
    var captcha_chk = false;

    function use_captcha_check() {
        $.ajax({
            type: "POST",
            url: g5_admin_url + "/ajax.use_captcha.php",
            data: {
                admin_use_captcha: "1"
            },
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {}
        });
    }

    function frm_check_file() {
        var co_include_head = "<?php echo $co['co_include_head']; ?>";
        var co_include_tail = "<?php echo $co['co_include_tail']; ?>";
        var head = jQuery.trim(jQuery("#co_include_head").val());
        var tail = jQuery.trim(jQuery("#co_include_tail").val());

        if (co_include_head !== head || co_include_tail !== tail) {
            // 캡챠를 사용합니다.
            jQuery("#admin_captcha_box").show();
            captcha_chk = true;

            use_captcha_check();

            return false;
        } else {
            jQuery("#admin_captcha_box").hide();
        }

        return true;
    }

    jQuery(function($) {
        if (window.self !== window.top) { // frame 또는 iframe을 사용할 경우 체크
            $("#co_include_head, #co_include_tail").on("change paste keyup", function(e) {
                frm_check_file();
            });

            use_captcha_check();
        }
    });

    function frmcontentform_check(f) {
        errmsg = "";
        errfld = "";

        <?php echo get_editor_js('co_content'); ?>
        <?php echo chk_editor_js('co_content'); ?>
        <?php echo get_editor_js('co_mobile_content'); ?>

        check_field(f.co_id, "ID를 입력하세요.");
        check_field(f.co_subject, "제목을 입력하세요.");
        check_field(f.co_content, "내용을 입력하세요.");

        if (errmsg != "") {
            alert(errmsg);
            errfld.focus();
            return false;
        }

        if (captcha_chk) {
            <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함 ?>
        }

        return true;
    }
</script>

<?php
require_once G5_ADMIN_PATH . '/admin.tail.php';
