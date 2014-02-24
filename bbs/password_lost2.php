<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if ($is_member) {
    alert('이미 로그인중입니다.');
}

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$email = trim($_POST['mb_email']);

if (!$email)
    alert_close('메일주소 오류입니다.');

$sql = " select count(*) as cnt from {$g5['member_table']} where mb_email = '$email' ";
$row = sql_fetch($sql);
if ($row['cnt'] > 1)
    alert('동일한 메일주소가 2개 이상 존재합니다.\\n\\n관리자에게 문의하여 주십시오.');

$sql = " select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime from {$g5['member_table']} where mb_email = '$email' ";
$mb = sql_fetch($sql);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원입니다.');
else if (is_admin($mb['mb_id']))
    alert('관리자 아이디는 접근 불가합니다.');

// 난수 발생
srand(time());
$change_password = rand(100000, 999999);
$mb_lost_certify = sql_password($change_password);
$mb_datetime     = sql_password($mb['mb_datetime']);

$sql = " update {$g5['member_table']} set mb_lost_certify = '$mb_lost_certify' where mb_id = '{$mb['mb_id']}' ";
sql_query($sql);

$href = G5_BBS_URL.'/password_lost_certify.php?mb_no='.$mb['mb_no'].'&amp;mb_datetime='.$mb_datetime.'&amp;mb_lost_certify='.$mb_lost_certify;

$subject = "[".$config['cf_title']."] 요청하신 회원정보 찾기 안내 메일입니다.";

$content = "";

$content .= '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
$content .= '<div style="border:1px solid #dedede">';
$content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
$content .= '회원정보 찾기 안내';
$content .= '</h1>';
$content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
$content .= '<a href="'.G5_URL.'" target="_blank">'.$config['cf_title'].'</a>';
$content .= '</span>';
$content .= '<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
$content .= addslashes($mb['mb_name'])." (".addslashes($mb['mb_nick']).")"." 회원님은 ".G5_TIME_YMDHIS." 에 회원정보 찾기 요청을 하셨습니다.<br>";
$content .= '저희 사이트는 관리자라도 회원님의 비밀번호를 알 수 없기 때문에, 비밀번호를 알려드리는 대신 새로운 비밀번호를 생성하여 안내 해드리고 있습니다.<br>';
$content .= '아래에서 변경될 비밀번호를 확인하신 후, <span style="color:#ff3061"><strong>비밀번호 변경</strong> 링크를 클릭 하십시오.</span><br>';
$content .= '비밀번호가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 비밀번호를 입력하시고 로그인 하십시오.<br>';
$content .= '로그인 후에는 정보수정 메뉴에서 새로운 비밀번호로 변경해 주십시오.';
$content .= '</p>';
$content .= '<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
$content .= '<span style="display:inline-block;width:100px">회원아이디</span> '.$mb['mb_id'].'<br>';
$content .= '<span style="display:inline-block;width:100px">변경될 비밀번호</span> <strong style="color:#ff3061">'.$change_password.'</strong>';
$content .= '</p>';
$content .= '<a href="'.$href.'" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">비밀번호 변경</a>';
$content .= '</div>';
$content .= '</div>';

$admin = get_admin('super');
mailer($admin['mb_nick'], $admin['mb_email'], $mb['mb_email'], $subject, $content, 1);

alert_close($email.' 메일로 회원아이디와 비밀번호를 인증할 수 있는 메일이 발송 되었습니다.\\n\\n메일을 확인하여 주십시오.');
?>