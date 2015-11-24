<?php
$sub_menu = "200300";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'r');

$html_title = '회원메일';

if ($w == 'u') {
    $html_title .= '수정';
    $readonly = ' readonly';

    $sql = " select * from {$g5['mail_table']} where ma_id = '{$ma_id}' ";
    $ma = sql_fetch($sql);
    if (!$ma['ma_id'])
        alert('등록된 자료가 없습니다.');
} else {
    $html_title .= '입력';
}

$g5['title'] = $html_title;
include_once('./admin.head.php');
?>

<p>메일 내용에 {이름} , {닉네임} , {회원아이디} , {이메일} 처럼 내용에 삽입하면 해당 내용에 맞게 변환하여 메일을 발송합니다.</p>

<form name="fmailform" id="fmailform" action="./mail_update.php" onsubmit="return fmailform_check(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>" id="w">
<input type="hidden" name="ma_id" value="<?php echo $ma['ma_id'] ?>" id="ma_id">
<input type="hidden" name="token" value="" id="token">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="ma_subject">메일 제목<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="ma_subject" value="<?php echo $ma['ma_subject'] ?>" id="ma_subject" required class="required frm_input" size="100"></td>
    </tr>
    <tr>
        <th scope="row"><label for="ma_content">메일 내용<strong class="sound_only">필수</strong></label></th>
        <td><?php echo editor_html("ma_content", get_text($ma['ma_content'], 0)); ?></td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" class="btn_submit" accesskey="s" value="확인">
</div>
</form>

<script>
function fmailform_check(f)
{
    errmsg = "";
    errfld = "";

    check_field(f.ma_subject, "제목을 입력하세요.");
    //check_field(f.ma_content, "내용을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    <?php echo get_editor_js("ma_content"); ?>
    <?php echo chk_editor_js("ma_content"); ?>

    return true;
}

document.fmailform.ma_subject.focus();
</script>

<?php
include_once('./admin.tail.php');
?>
