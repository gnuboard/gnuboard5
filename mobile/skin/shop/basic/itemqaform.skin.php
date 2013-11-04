<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<!-- 상품문의 쓰기 시작 { -->
<div id="sit_qa_write" class="new_win">
    <h1 class="new_win_title">상품문의 쓰기</h1>

    <form name="fitemqa" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_2">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="iq_subject">제목</label></th>
            <td><input type="text" name="iq_subject" value="<?php echo get_text($qa['iq_subject']); ?>" id="iq_subject" required class="required frm_input" minlength="2" maxlength="250"></td>
        </tr>
        <tr>
            <th scope="row"><label for="iq_question">질문</label></th>
            <td><?php echo editor_html('iq_question', $qa['iq_question']); ?></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_win">
        <input type="submit" value="작성완료" class="btn_submit">
    </div>
    </form>
</div>

<script type="text/javascript">
function fitemqa_submit(f)
{
    <?php echo get_editor_js('iq_question'); ?>

    <?php echo chk_editor_js('iq_question'); ?>

    return true;
}
</script>
<!-- } 상품문의 쓰기 끝 -->