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
?>