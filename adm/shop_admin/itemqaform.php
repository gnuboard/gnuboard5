<?php
$sub_menu = '400660';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], "w");

$sql = " select *
           from {$g5['g5_shop_item_qa_table']} a
           left join {$g5['member_table']} b on (a.mb_id = b.mb_id)
          where iq_id = '$iq_id' ";
$iq = sql_fetch($sql);
if (!$iq['iq_id']) alert('등록된 자료가 없습니다.');

$name = get_sideview($is['mb_id'], get_text($iq['iq_name']), $is['mb_email'], $is['mb_homepage']);

$g5['title'] = '상품문의';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;
?>

<form name="fitemqaform" method="post" action="./itemqaformupdate.php" onsubmit="return fitemqaform_submit(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">

<div class="local_desc01 local_desc">
    <p>상품에 대한 문의에 답변하실 수 있습니다. 상품 문의 내용의 수정도 가능합니다.</p>
</div>

<form name="frmitemqaform" action="./itemqaformupdate.php" method="post">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 수정</caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">이름</th>
        <td><?php echo $name; ?></td>
    </tr>
    <?php if($iq['iq_email']) { ?>
    <tr>
        <th scope="row">이메일</th>
        <td><?php echo get_text($iq['iq_email']); ?></td>
    </tr>
    <?php } ?>
    <?php if($iq['iq_hp']) { ?>
    <tr>
        <th scope="row">휴대폰</th>
        <td><?php echo hyphen_hp_number($iq['iq_hp']); ?></td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="iq_subject">제목</label></th>
        <td><input type="text" name="iq_subject" value="<?php echo conv_subject($iq['iq_subject'],120); ?>" id="iq_subject" required class="frm_input required" size="95"></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_question">질문</label></th>
        <td><?php echo editor_html('iq_question', get_text($iq['iq_question'], 0)); ?></td>
    </tr>
    <tr>
        <th scope="row"><label for="iq_answer">답변</label></th>
        <td><?php echo editor_html('iq_answer', get_text($iq['iq_answer'], 0)); ?></td>
        <!-- <td><textarea name="iq_answer" id="iq_answer" rows="7"><?php echo get_text($iq['iq_answer']); ?></textarea></td> -->
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" accesskey='s' value="확인" class="btn_submit">
    <a href="./itemqalist.php?<?php echo $qstr; ?>">목록</a>
</div>
</form>

<script>
function fitemqaform_submit(f)
{
    <?php echo get_editor_js('iq_question'); ?>
    <?php echo get_editor_js('iq_answer'); ?>

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
