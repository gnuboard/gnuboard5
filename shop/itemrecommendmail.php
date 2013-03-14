<?
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸으로 인한 코드 수정 060809
//if (substr_count($to_email, "@") > 3) alert("최대 3명까지만 메일을 발송할 수 있습니다.");
if (substr_count($to_email, "@") > 1) alert('메일 주소는 하나씩만 입력해 주십시오.');

if ($_SESSION["ss_recommend_datetime"] >= ($g4[server_time] - 120))
    alert("너무 빠른 시간내에 메일을 연속해서 보낼 수 없습니다.");
set_session("ss_recommend_datetime", $g4[server_time]);

$recommendmail_count = (int)get_session('ss_recommendmail_count') + 1;
if ($recommendmail_count > 3)
    alert_close('한번 접속후 일정수의 메일만 발송할 수 있습니다.\n\n계속해서 메일을 보내시려면 다시 로그인 또는 접속하여 주십시오.');
set_session('ss_recommendmail_count', $recommendmail_count);

// 세션에 저장된 토큰과 폼값으로 넘어온 토큰을 비교하여 틀리면 메일을 발송할 수 없다.
if ($_POST["token"] && get_session("ss_token") == $_POST["token"]) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    alert_close("메일 발송시 오류가 발생하였습니다.");
    exit;
}

// 상품
$sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' ";
$it = sql_fetch($sql);
if (!$it[it_id])
    alert("등록된 상품이 아닙니다.");

$subject = stripslashes($subject);
$content = nl2br(stripslashes($content));

$from_name = $member[mb_name];
$from_email = $member[mb_email];
$it_id = $it[it_id];
$it_name = $it[it_name];
$it_mimg = $it[it_id]."_m";

ob_start();
include "./mail/itemrecommend.mail.php";
$content = ob_get_contents();
ob_end_clean();

mailer($from_name, $from_email, $to_email, $subject, $content, 1);

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
?>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<script language="JavaScript">
alert("메일을 전달하였습니다");
window.close();
</script>
