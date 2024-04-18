<?php
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.', G5_URL);
}

$content = '';
$editor_html = editor_html('po_content', $content, 1);
$editor_js = '';
$editor_js .= get_editor_js('po_content', 1);
$editor_js .= chk_editor_js('po_content', 1);


include_once(G5_BBS_PATH.'/board_head.php');
?>

<form name="fpost" id="fpost" action="<?php echo G5_BBS_URL; ?>/post_update.php" onsubmit="return fpost_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
<input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="spt" value="<?php echo $spt; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">

<div id="bo_w" class="tbl_frm01">
    <table>
    <caption><?php echo $board['bo_subject'] ?> 글쓰기</caption>
    <tbody>
    <tr>
        <th scope="row"><label for="po_subject">제목</label></th>
        <td><input type="text" name="po_subject" value="<?php echo $subject; ?>" id="po_subject" required class="frm_input required" size="50"></td>
    </tr>
    <tr>
        <th scope="row"><label for="wr_content">내용</label></th>
        <td>
            <?php echo $editor_html; ?>
        </td>
    </tr>
    </table>
    <button type="submit" id="btn_submit" accesskey="s" class="btn_submit">작성완료</button>
</div>

<script>
function fpost_submit(f)
{
    <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>

<?php
include_once(G5_BBS_PATH.'/board_tail.php');
?>