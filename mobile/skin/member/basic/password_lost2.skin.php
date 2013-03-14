<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G4_LIB_PATH.'/mailer.lib.php');

$email = trim($_POST['mb_email']);

if (!$email)
    alert_close('메일주소 오류입니다.');

$sql = " select count(*) as cnt from {$g4['member_table']} where mb_email = '$email' ";
$row = sql_fetch($sql);
if ($row['cnt'] > 1)
    alert('동일한 메일주소가 2개 이상 존재합니다.\\n\\n관리자에게 문의하여 주십시오.');

$sql = " select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime from {$g4['member_table']} where mb_email = '$email' ";
$mb = sql_fetch($sql);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원입니다.');
else if (is_admin($mb['mb_id']))
    alert('관리자 아이디는 접근 불가합니다.');

// 난수 발생
srand(time());
$randval = rand(4, 6);

$change_password = substr(md5(get_microtime()), 0, $randval);

$mb_lost_certify = sql_password($change_password);
$mb_datetime     = sql_password($mb['mb_datetime']);

// 회원테이블에 필드를 추가
sql_query(" ALTER TABLE `{$g4['member_table']}` ADD `mb_lost_certify` VARCHAR( 255 ) NOT NULL AFTER `mb_memo` ", false);

$sql = " update {$g4['member_table']}
            set mb_lost_certify = '$mb_lost_certify'
            where mb_id = '{$mb['mb_id']}' ";
sql_query($sql);

$href = G4_BBS_URL.'/password_lost_certify.php?mb_no='.$mb['mb_no'].'&amp;mb_datetime='.$mb_datetime.'&amp;mb_lost_certify='.$mb_lost_certify;

$subject = "[".$config['cf_title']."] 요청하신 회원 아이디/패스워드 정보입니다.";

$content = "";

$content .= "<div style=\"margin:30px auto;width:600px;border:10px solid #f7f7f7\">";
$content .= "<div style=\"border:1px solid #dedede\">";
$content .= "<h1 style=\"padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em\">";
$content .= "회원 패스워드가 변경되었습니다.";
$content .= "</h1>";
$content .= "<span style=\"display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right\">";
$content .= "<a href=\"".G4_URL."\" target=\"_blank\">".$config['cf_title']."</a>";
$content .= "</span>";
$content .= "<p style=\"margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em\">";
$content .= addslashes($mb['mb_name'])." (".addslashes($mb['mb_nick']).")"." 회원님은 ".G4_TIME_YMDHIS." 에 회원정보 찾기 요청을 하셨습니다.<br>";
$content .= "저희 사이트는 관리자라도 회원님의 비밀번호를 알 수 없기 때문에, 비밀번호를 알려드리는 대신 새로운 비밀번호를 생성하여 안내 해드리고 있습니다.<br>";
$content .= "다음에서 변경될 패스워드를 확인하신 후, <span style=\"color:#ff3061\"><strong>패스워드 변경</strong> 링크를 클릭 하십시오.</span><br>";
$content .= "패스워드가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 패스워드를 입력하시고 로그인 하십시오.<br>";
$content .= "로그인 후에는 정보수정 메뉴에서 새 패스워드로 변경하십시오.";
$content .= "</p>";
$content .= "<p style=\"margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em\">";
$content .= "<span style=\"display:inline-block;width:100px\">회원아이디</span> ".$mb['mb_id']."<br>";
$content .= "<span style=\"display:inline-block;width:100px\">변경될 패스워드</span> <strong style=\"color:#ff3061\">".$change_password."</strong>";
$content .= "</p>";
$content .= "<a href=\"".$href."/\" style=\"display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center\">패스워드 변경</a>";
$content .= "</div>";
$content .= "</div>";

$admin = get_admin('super');
mailer($admin['mb_nick'], $admin['mb_email'], $mb['mb_email'], $subject, $content, 1);

alert_close($email.' 메일로 회원아이디와 패스워드를 인증할 수 있는 메일이 발송 되었습니다.\\n\\n메일을 확인하여 주십시오.');
?>