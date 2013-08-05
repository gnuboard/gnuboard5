<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/itemuseform.php');
    return;
}

include_once(G4_EDITOR_LIB);

// 사용후기의 내용에 쓸수 있는 최대 글자수 (한글은 영문3자)
$is_content_max_length = 10000;

$w     = escape_trim($_REQUEST['w']);
$it_id = escape_trim($_REQUEST['it_id']);
$is_id = escape_trim($_REQUEST['is_id']);

if (!$is_member) {
    alert_close("사용후기는 회원만 작성 가능합니다.");
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

<!-- 사용후기 쓰기 시작 { -->
<div id="sit_use_write" class="new_win">
    <h1 class="new_win_title">사용후기 쓰기</h1>

    <form name="fitemuse" method="post" action="./itemuseformupdate.php" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="is_id" value="<?php echo $is_id; ?>">

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_2">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="is_subject">제목</label></th>
        <td><input type="text" name="is_subject" value="<?php echo get_text($use['is_subject']); ?>" id="is_subject" required class="frm_input" minlength="2" maxlength="250"></td>
    </tr>
    <tr>
        <th scope="row"><label for="" style="width:200px;">내용</label></th>
        <td><?php echo editor_html('is_content', $use['is_content']); ?></td>
    </tr>
    <tr>
        <th scope="row">평가</th>
        <td>
            <ul id="sit_use_write_star">
                <li>
                    <input type="radio" name="is_score" value="10" id="is_score10" <?php echo ($is_score==10)?'checked="checked"':''; ?>>
                    <label for="is_score10">매우만족</label>
                    <img src="<?php echo G4_URL; ?>/shop/img/s_star5.png">
                </li>
                <li>
                    <input type="radio" name="is_score" value="8" id="is_score8" <?php echo ($is_score==8)?'checked="checked"':''; ?>>
                    <label for="is_score8">만족</label>
                    <img src="<?php echo G4_URL; ?>/shop/img/s_star4.png">
                </li>
                <li>
                    <input type="radio" name="is_score" value="6" id="is_score6" <?php echo ($is_score==6)?'checked="checked"':''; ?>>
                    <label for="is_score6">보통</label>
                    <img src="<?php echo G4_URL; ?>/shop/img/s_star3.png">
                </li>
                <li>
                    <input type="radio" name="is_score" value="4" id="is_score4" <?php echo ($is_score==4)?'checked="checked"':''; ?>>
                    <label for="is_score4">불만</label>
                    <img src="<?php echo G4_URL; ?>/shop/img/s_star2.png">
                </li>
                <li>
                    <input type="radio" name="is_score" value="2" id="is_score2" <?php echo ($is_score==2)?'checked="checked"':''; ?>>
                    <label for="is_score2">매우불만</label>
                    <img src="<?php echo G4_URL; ?>/shop/img/s_star1.png">
                </li>
            </ul>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_win">
        <input type="submit" value="작성완료" class="btn_submit">
    </div>

    </form>
</div>

<script type="text/javascript">
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
</script>
<!-- } 사용후기 쓰기 끝 -->

<?php
include_once(G4_PATH.'/tail.sub.php');
?>