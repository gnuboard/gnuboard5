<<<<<<< HEAD
<?php
include_once("./_common.php");
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

// 상품문의의 내용에 쓸수 있는 최대 글자수 (한글은 영문3자)
$iq_question_max_length = 10000;

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$iq_id = escape_trim($_REQUEST['iq_id']);

if (!$is_member) {
    alert_login("상품문의는 회원만 작성 가능합니다.", urlencode($_SERVER['REQUEST_URI']));
}

if ($w == "u") 
{
    $qa = sql_fetch(" select * from {$g4['shop_item_qa_table']} where iq_id = '$iq_id' ");
    if (!$qa) {
        alert_close("상품문의 정보가 없습니다.");
    }

    $it_id    = $qa['it_id'];

    if (!$iq_admin && $qa['mb_id'] != $member['mb_id']) {
        alert_close("자신의 상품문의만 수정이 가능합니다.");
    }
}

include_once(G4_PATH.'/head.sub.php');
?>
<style>
ul {list-style:none;margin:0px;padding:0px;}
label {width:130px;vertical-align:top;padding:3px 0;}
</style>

<div style="padding:10px;">
    <form name="fitemqa" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">
    <fieldset style="padding:0 10px 10px;">
    <legend><strong>상품문의 쓰기</strong></legend>
    <ul style="padding:10px;">
        <li>
            <label for="iq_subject">제목</label>
            <input type="text" id="iq_subject" name="iq_subject" size="100" class="ed" minlength="2" maxlength="250" required itemname="제목" value="<?php echo get_text($qa['iq_subject']); ?>">
        </li>
        <li>
            <label for="" style="width:200px;">질문</label>
            <?php echo editor_html('iq_question', $qa['iq_question']); ?>
        </li>
    </ul>
    <input type="submit" value="   확   인   ">
    </fieldset>
    </form>
</div>

<script type="text/javascript">
self.focus();

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

$(function() {
    $("#iq_subject").focus();
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
=======
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
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
?>