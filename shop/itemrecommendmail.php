<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

// 스팸으로 인한 코드 수정 060809
//if (substr_count($to_email, "@") > 3) alert("최대 3명까지만 메일을 발송할 수 있습니다.");
if (substr_count($to_email, "@") > 1) alert('메일 주소는 하나씩만 입력해 주십시오.');

if ($_SESSION["ss_recommend_datetime"] >= (G5_SERVER_TIME - 120))
    alert("너무 빠른 시간내에 메일을 연속해서 보낼 수 없습니다.");
set_session("ss_recommend_datetime", G5_SERVER_TIME);

$recommendmail_count = (int)get_session('ss_recommendmail_count') + 1;
if ($recommendmail_count > 3)
    alert_close('한번 접속후 일정수의 메일만 발송할 수 있습니다.\\n\\n계속해서 메일을 보내시려면 다시 로그인 또는 접속하여 주십시오.');
set_session('ss_recommendmail_count', $recommendmail_count);

// 세션에 저장된 토큰과 폼값으로 넘어온 토큰을 비교하여 틀리면 메일을 발송할 수 없다.
if (isset($_POST["token"]) && get_session("ss_token") === $_POST["token"]) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    alert_close("메일 발송시 오류가 발생하였습니다.");
    exit;
}

// 상품
$it = get_shop_item($it_id, true);
if (! (isset($it['it_id']) && $it['it_id']))
    alert("등록된 상품이 아닙니다.");

$subject = isset($_POST['subject']) ? stripslashes($_POST['subject']) : '';
$content = isset($_POST['content']) ? nl2br(stripslashes($_POST['content'])) : '';

$from_name = get_text($member['mb_name']);
$from_email = $member['mb_email'];
$it_id = $it['it_id'];
$it_name = $it['it_name'];
$it_mimg = get_it_image($it_id, $default['de_mimg_width'], $default['de_mimg_height']);

ob_start();
include G5_SHOP_PATH.'/mail/itemrecommend.mail.php';
$content = ob_get_contents();
ob_end_clean();

mailer($from_name, $from_email, $to_email, $subject, $content, 1);

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script language="JavaScript">
alert("메일을 전달하였습니다");
window.close();
</script>
