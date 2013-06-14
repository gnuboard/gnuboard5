<?php
include_once("./_common.php");
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

// 사용후기의 내용에 쓸수 있는 최대 글자수 (한글은 영문3자)
$is_content_max_length = 10000;

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$is_id = escape_trim($_REQUEST['is_id']);

if (!$is_member) {
    alert("사용후기는 회원만 평가가 가능합니다.", G4_BBS_URL."/login.php");
}

if ($w == "") {
    $is_score = 10;
} else if ($w == "u") {
    $use = sql_fetch(" select * from {$g4['shop_item_use_table']} where is_id = '$is_id' ");
    if (!$use) {
        alert_close("사용후기 정보가 없습니다.");
    }

    $it_id    = $use['it_id'];
    $is_score = $use['is_score'];

    if (!$is_admin && $use['mb_id'] != $member['mb_id']) {
        alert_close("자신의 사용후기만 수정이 가능합니다.");
    }
}

include_once(G4_PATH.'/head.sub.php');
?>
<style>
ul {list-style:none;margin:0px;padding:0px;}
label {width:130px;vertical-align:top;padding:3px 0;}
</style>

<div style="padding:10px;">
    <form name="fitemuse" method="post" action="./itemuseformupdate.php" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="is_id" value="<?php echo $is_id; ?>">
    <fieldset style="padding:0 10px 10px;">
    <legend><strong>사용후기 쓰기</strong></legend>
    <ul style="padding:10px;">
        <li>
            <label for="is_subject">제목</label>
            <input type="text" id="is_subject" name="is_subject" size="100" class="ed" minlength="2" maxlength="250" required itemname="제목" value="<?php echo get_text($use['is_subject']); ?>">
        </li>
        <li>
            <label for="" style="width:200px;">내용</label>
            <?php echo editor_html('is_content', $use['is_content']); ?>
        </li>
        <li>
            <label>평가</label>
            <input type=radio name=is_score value='10' <?php echo ($is_score==10)?"checked='checked'":""; ?>><img src='<?php echo G4_SHOP_URL; ?>/img/star5.gif' align=absmiddle>
            <input type=radio name=is_score value='8'  <?php echo ($is_score==8)?"checked='checked'":""; ?>><img src='<?php echo G4_SHOP_URL; ?>/img/star4.gif' align=absmiddle>
            <input type=radio name=is_score value='6'  <?php echo ($is_score==6)?"checked='checked'":""; ?>><img src='<?php echo G4_SHOP_URL; ?>/img/star3.gif' align=absmiddle>
            <input type=radio name=is_score value='4'  <?php echo ($is_score==4)?"checked='checked'":""; ?>><img src='<?php echo G4_SHOP_URL; ?>/img/star2.gif' align=absmiddle>
            <input type=radio name=is_score value='2'  <?php echo ($is_score==2)?"checked='checked'":""; ?>><img src='<?php echo G4_SHOP_URL; ?>/img/star1.gif' align=absmiddle>
        </li>
    </ul>
    <input type="submit" value="   확   인   ">
    </fieldset>
    </form>
</div>

<script type="text/javascript">
self.focus();

function fitemuse_submit(f)
{
    /*
    if (document.getElementById('tx_is_content')) {
        var len = ed_is_content.inputLength();
        if (len == 0) {
            alert('내용을 입력하십시오.');
            ed_is_content.returnFalse();
            return false;
        } else if (len > 1000) {
            alert('내용은 1000글자 까지만 입력해 주세요.');
            ed_is_content.returnFalse();
            return false;
        }
    }
    */

    <?php echo get_editor_js('is_content'); ?>

    if (is_content_editor_data.length > <?php echo $is_content_max_length; ?>) {
        alert("내용은 <?php echo $is_content_max_length; ?> 글자 이내에서 작성해 주세요. (한글은 영문 3자)\n\n현재 : "+is_content_editor_data.length+" 글자");
        CKEDITOR.instances.is_content.focus(); 
        return false;
    }

    return true;
}

$(function() {
    $("#is_subject").focus();
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>