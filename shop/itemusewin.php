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
    $ps = sql_fetch(" select * from $g4[yc4_item_ps_table] where is_id = '$is_id' ");
    if (!$ps) {
        alert_close("사용후기 정보가 없습니다.");
    }

    $it_id    = $ps[it_id];
    $is_score = $ps[is_score];
}

if ($w == "u") {
    if (!$is_admin && $ps[mb_id] != $member[mb_id]) {
        alert_close("자신의 사용후기만 수정이 가능합니다.");
    }
}

include_once("$g4[path]/lib/cheditor4.lib.php");
include_once("$g4[path]/head.sub.php");

echo "<script src='$g4[cheditor4_path]/cheditor.js'></script>";
echo cheditor1('is_content', '100%', '250');
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
            <input type='text' id='is_subject' name='is_subject' size='100' class='ed' minlength='2' required itemname='제목' value='<?=get_text($ps[is_subject])?>'>
        </li>
        <li>
            <label for="" style="width:200px;">내용</label>
            <?=cheditor2('is_content', $ps[is_content]);?>
        </li>
        <li>
            <label>평가</label>
            <input type=radio name=is_score value='10' <?=($is_score==10)?"checked='checked'":"";?>><img src='<?=$g4[shop_img_path]?>/star5.gif' align=absmiddle>
            <input type=radio name=is_score value='8'  <?=($is_score==8)?"checked='checked'":"";?>><img src='<?=$g4[shop_img_path]?>/star4.gif' align=absmiddle>
            <input type=radio name=is_score value='6'  <?=($is_score==6)?"checked='checked'":"";?>><img src='<?=$g4[shop_img_path]?>/star3.gif' align=absmiddle>
            <input type=radio name=is_score value='4'  <?=($is_score==4)?"checked='checked'":"";?>><img src='<?=$g4[shop_img_path]?>/star2.gif' align=absmiddle>
            <input type=radio name=is_score value='2'  <?=($is_score==2)?"checked='checked'":"";?>><img src='<?=$g4[shop_img_path]?>/star1.gif' align=absmiddle>
        </li>
        <li>
            <label style="vertical-align:middle;"><img id='kcaptcha_image_use' /></label>
            <input type='text' name='is_key' class='ed' required itemname='자동등록방지용 코드'>
            &nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.
        </li>
    </ul>
    <input type="submit" value="   확   인   ">
    </fieldset>
    </form>
</div>

<script type="text/javascript" src="<?=$g4[path]?>/js/jquery.kcaptcha.js"></script>
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

    <? echo cheditor3('is_content'); ?>

    f.action = "./itemusewinupdate.php";
}

$(function() {
    $("#is_subject").focus();
    $("#kcaptcha_image_use").bind("click", function() {
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
            cache: false,
            async: false,
            success: function(text) {
                $("#kcaptcha_image_use, #kcaptcha_image_qa").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
            }
        });
    })
    .css('cursor', 'pointer')
    .attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
    .attr('width', '120')
    .attr('height', '60')
    .trigger('click');
});
</script>
<?
include_once("$g4[path]/tail.sub.php");
?>