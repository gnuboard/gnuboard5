<?
$sub_menu = "200300";
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$html_title = '회원메일';

if ($w == 'u') {
    $html_title .= '수정';
    $readonly = ' readonly';

    $sql = " select * from {$g4['mail_table']} where ma_id = '{$ma_id}' ";
    $ma = sql_fetch($sql);
    if (!$ma['ma_id'])
        alert('등록된 자료가 없습니다.');
} else {
    $html_title .= '입력';
}

$g4['title'] = $html_title;
include_once('./admin.head.php');
?>

<div class="cbox">
    <p>메일 내용에 {이름} , {별명} , {회원아이디} , {이메일} 처럼 내용에 삽입하면 해당 내용에 맞게 변환하여 메일을 발송합니다.</p>

    <form id="fmailform" name="fmailform" method="post" action="./mail_update.php" onsubmit="return fmailform_check(this);">
    <input type="hidden" id="w" name="w" value="<?=$w?>">
    <input type="hidden" id="ma_id" name="ma_id" value="<?=$ma['ma_id']?>">
    <input type="hidden" id="token" name="token" value="<?=$token?>">
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="ma_subject">메일 제목<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="ma_subject" name="ma_subject" value="<?=$ma['ma_subject']?>" size="100" class="required frm_input" required></td>
    </tr>
    <tr>
        <th scope="row"><label for="ma_content">메일 내용<strong class="sound_only">필수</strong></label></th>
        <td><?=editor_html("ma_content", $ma['ma_content'], '400');?></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" class="btn_submit" accesskey="s" value="확인">
    </div>
    </form>
</div>

<script>
function fmailform_check(f)
{
    errmsg = "";
    errfld = "";

    check_field(f.ma_subject, "제목을 입력하세요.");
    check_field(f.ma_content, "내용을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }
    return true;
}

document.fmailform.ma_subject.focus();
</script>

<?
include_once('./admin.tail.php');
?>
