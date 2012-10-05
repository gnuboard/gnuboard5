<?
include_once("./_common.php");

if (!$config[cf_email_use])
    alert("환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.\\n\\n관리자에게 문의하시기 바랍니다.");

if (!$is_member && $config[cf_formmail_is_member])  
    alert_close("회원만 이용하실 수 있습니다.");

if ($is_member && !$member[mb_open] && $is_admin != "super" && $member[mb_id] != $mb_id) 
    alert_close("자신의 정보를 공개하지 않으면 다른분에게 메일을 보낼 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.");

if ($mb_id) 
{
    $mb = get_member($mb_id);
    if (!$mb[mb_id]) 
        alert_close("회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.");

    if (!$mb[mb_open] && $is_admin != "super")
        alert_close("정보공개를 하지 않았습니다.");
}

$sendmail_count = (int)get_session('ss_sendmail_count') + 1;
if ($sendmail_count > 3)
    alert_close('한번 접속후 일정수의 메일만 발송할 수 있습니다.\n\n계속해서 메일을 보내시려면 다시 로그인 또는 접속하여 주십시오.');

$g4[title] = "메일 쓰기";
include_once("$g4[path]/head.sub.php");

if (!$name)
    $name = base64_decode($email);

if (!isset($type)) 
    $type = 0;

$type_checked[0] = $type_checked[1] = $type_checked[2] = "";
$type_checked[$type] = "checked";

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/formmail.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
