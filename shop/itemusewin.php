<?
include_once("./_common.php");

$w     = substr($_REQUEST['w'],0,1);
$it_id = substr($_REQUEST['it_id'],0,10);
$is_id = (int)$_REQUEST['is_id'];

if (!$is_member) {
    alert_close("사용후기는 회원만 평가가 가능합니다.");
}

if ($w == "") {
    $is_score = 10;
} else if ($w == "u") {
    $ps = sql_fetch(" select * from {$g4['yc4_item_ps_table']} where is_id = '$is_id' ");
    if (!$ps) {
        alert_close("사용후기 정보가 없습니다.");
    }

    $it_id    = $ps['it_id'];
    $is_score = $ps['is_score'];
}

if ($w == "u") {
    if (!$is_admin && $ps['mb_id'] != $member['mb_id']) {
        alert_close("자신의 사용후기만 수정이 가능합니다.");
    }
}

include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');
include_once(G4_PATH.'/head.sub.php');

$captcha_html = captcha_html();
?>
<style>
ul {list-style:none;margin:0px;padding:0px;}
label {width:130px;vertical-align:top;padding:3px 0;}
</style>

<div style="padding:10px;">
    <form name="fitemuse" method="post" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?=$w?>">
    <input type="hidden" name="it_id" value="<?=$it_id?>">
    <input type="hidden" name="is_id" value="<?=$is_id?>">
    <fieldset style="padding:0 10px 10px;">
    <legend><strong>사용후기 쓰기</strong></legend>
    <ul style="padding:10px;">
        <li>
            <label for="is_subject">제목</label>
            <input type='text' id='is_subject' name='is_subject' size='100' class='ed' minlength='2' required itemname='제목' value='<?=get_text($ps['is_subject'])?>'>
        </li>
        <li>
            <label for="" style="width:200px;">내용</label>
            <?=editor_html('is_content', $ps['is_content']);?>
        </li>
        <li>
            <label>평가</label>
            <input type=radio name=is_score value='10' <?=($is_score==10)?"checked='checked'":"";?>><img src='<?=G4_SHOP_URL?>/img/star5.gif' align=absmiddle>
            <input type=radio name=is_score value='8'  <?=($is_score==8)?"checked='checked'":"";?>><img src='<?=G4_SHOP_URL?>/img/star4.gif' align=absmiddle>
            <input type=radio name=is_score value='6'  <?=($is_score==6)?"checked='checked'":"";?>><img src='<?=G4_SHOP_URL?>/img/star3.gif' align=absmiddle>
            <input type=radio name=is_score value='4'  <?=($is_score==4)?"checked='checked'":"";?>><img src='<?=G4_SHOP_URL?>/img/star2.gif' align=absmiddle>
            <input type=radio name=is_score value='2'  <?=($is_score==2)?"checked='checked'":"";?>><img src='<?=G4_SHOP_URL?>/img/star1.gif' align=absmiddle>
        </li>
        <li>
            <label style="vertical-align:middle;"></label>
            <?=$captcha_html?>
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
    if (document.getElementById('tx_is_content')) {
        var len = ed_is_content.inputLength();
        if (len == 0) {
            alert('내용을 입력하십시오.');
            ed_is_content.returnFalse();
            return false;
        } else if (len > 5000) {
            alert('내용은 5000글자 까지만 입력해 주세요.');
            ed_is_content.returnFalse();
            return false;
        }
    }

    <? echo get_editor_js('is_content'); ?>

    <? echo chk_captcha_js(); ?>

    f.action = "./itemusewinupdate.php";
}

$(function() {
    $("#is_subject").focus();
});
</script>
<?
include_once(G4_PATH.'/tail.sub.php');
?>