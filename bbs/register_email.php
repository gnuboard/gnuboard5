<?php
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

$sql = " select mb_email, mb_datetime, mb_email_certify from {$g4['member_table']} where mb_id = '{$mb_id}' ";
$mb = sql_fetch($sql);
if (substr($mb['mb_email_certify'],0,1)!=0) {
    alert("이미 메일인증 하신 회원입니다.", G4_URL);
}

$g4['title'] = '메일인증 메일주소 변경';
include_once('./_head.php');
?>

<p>메일인증을 받지 못한 경우 회원정보의 메일주소를 변경 할 수 있습니다.</p>

<form method="post" name="fregister_email" onsubmit="return fregister_email_submit(this);">
<input type="hidden" name="mb_id" value="<?php echo $mb_id; ?>">
<table class="frm_tbl">
<caption>사이트 이용정보 입력</caption>
<tr>
    <th scope="row"><label for="reg_mb_email">E-mail<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" name="mb_email" id="reg_mb_email" required class="frm_input email required" size="50" maxlength="100" value="<?php echo $mb['mb_email']; ?>"></td>
</tr>
<tr>
    <th scope="row">자동등록방지</th>
    <td><?php echo captcha_html(); ?></td>
</tr>
</table>
<input type="submit" id="btn_submit" class="btn_submit" value="인증메일변경">
<a href="<?php echo G4_URL ?>" class="btn_cancel">취소</a>
</form>

<script>
function fregister_email_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    f.action = "<?php echo G4_HTTPS_BBS_URL.'/register_email_update.php'; ?>";
    return true;
}
</script>
<?
include_once('./_tail.php');
?>
