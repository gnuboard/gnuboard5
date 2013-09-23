<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 상품문의의 내용에 쓸수 있는 최대 글자수 (한글은 영문3자)
$iq_question_max_length = 10000;

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$iq_id = escape_trim($_REQUEST['iq_id']);
?>

<link rel="stylesheet" href="<?php echo G5_SHOP_SKIN_URL; ?>/style.css">

<!-- 상품문의 쓰기 시작 { -->
<div id="sit_qa_write" class="new_win">
    <h1 class="new_win_title">상품문의 쓰기</h1>

    <form name="fitemqa" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_2">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="iq_subject">제목</label></th>
        <td><input type="text" name="iq_subject" value="<?php echo get_text($qa['iq_subject']); ?>" id="iq_subject" required class="frm_input" minlength="2" maxlength="250"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_question">질문</label></th>
        <td><?php echo editor_html('iq_question', get_text($qa['iq_question'])); ?></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_win">
        <input type="submit" value="작성완료" class="btn_submit">
    </div>
    </form>
</div>

<script type="text/javascript">
function fitemqa_submit(f)
{
    <?php echo get_editor_js('iq_question'); ?>

    if (iq_question_editor_data.length > <?php echo $iq_question_max_length; ?>) {
        alert("내용은 <?php echo $iq_question_max_length; ?> 글자 이내에서 작성해 주세요. (한글은 영문 3자)\n\n현재 : "+iq_question_editor_data.length+" 글자");
        CKEDITOR.instances.iq_question.focus();
        return false;
    }

    return true;
}
</script>
<!-- } 상품문의 쓰기 끝 -->