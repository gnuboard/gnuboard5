<?php
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

$captcha_html = captcha_html();

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$iq_id = escape_trim($_REQUEST['iq_id']);

if($w == 'u') {
    $sql = " select * from {$g4['shop_item_qa_table']} where it_id = '$it_id' and iq_id = '$iq_id' ";
    $qa = sql_fetch($sql);
}

include_once(G4_PATH.'/head.sub.php');
?>
<div>
    <form name="fitemqa" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <?php if (!$is_member) { ?>
    <tr>
        <th scope="row"><label for="iq_name">이름</label></th>
        <td><input type="text" name="iq_name" id="iq_name" value="<?php echo $qa['iq_name']; ?>" required class="frm_input" maxlength="20" minlength="2"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_password">패스워드</label></th>
        <td>
            <span class="frm_info">패스워드는 최소 3글자 이상 입력하십시오.</span>
            <input type="password" name="iq_password" id="iq_password" required class="frm_input" maxlength="20" minlength="3">
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="iq_subject">제목</label></th>
        <td><input type="text" name="iq_subject" id="iq_subject" value="<?php echo $qa['iq_subject']; ?>" required class="frm_input" size="71" maxlength="100"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_question">내용</label></th>
        <td><textarea name="iq_question" id="iq_question" required><?php echo $qa['iq_question']; ?></textarea></td>
    </tr>
    <tr>
        <th scope="row">자동등록방지</th>
        <td><?php echo $captcha_html; ?></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="작성완료" class="btn_submit">
    </div>
    </form>
</div>

<script>
function fitemqa_submit(f)
{
    <?php echo chk_captcha_js(); ?>

    return true;
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>