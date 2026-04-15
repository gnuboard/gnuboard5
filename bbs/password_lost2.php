<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if ($is_member) {
    alert_close('이미 로그인중입니다.', G5_URL);
}

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$email = get_email_address(trim($_POST['mb_email']));

if (!$email)
    alert_close('메일주소 오류입니다.');

// OWASP 권장: 이메일 존재 여부와 무관하게 동일한 응답 메시지 사용
// (이메일 열거 공격 방지)
$generic_message = $email.' 메일로 회원아이디와 비밀번호를 인증할 수 있는 메일이 발송 되었습니다.\\n\\n메일을 확인하여 주십시오.';

$sql = " select count(*) as cnt from {$g5['member_table']} where mb_email = '$email' ";
$row = sql_fetch($sql);
if ($row['cnt'] > 1) {
    // 시스템 데이터 무결성 이슈 - 운영자 로그에만 기록하고 사용자에겐 일반 메시지
    @error_log("[g5 password_lost2] Duplicate email detected: $email (count={$row['cnt']})");
    alert_close($generic_message);
}

$sql = " select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime, mb_leave_date from {$g5['member_table']} where mb_email = '$email' ";
$mb = sql_fetch($sql);

// 회원이 없거나 탈퇴했거나 관리자이면 메일 발송 없이 동일한 메시지로 응답
if (empty($mb['mb_id']) || $mb['mb_leave_date'] || is_admin($mb['mb_id'])) {
    alert_close($generic_message);
}

// 임시비밀번호 발급 (CSPRNG 사용)
$change_password = get_random_token_string(5);  // 10자리 hex (0-9, a-f)
$mb_lost_certify = get_encrypt_string($change_password);

// 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용 (CSPRNG 사용)
$mb_nonce = get_random_token_string(16);

// 임시비밀번호와 난수를 mb_lost_certify 필드에 저장
$sql = " update {$g5['member_table']} set mb_lost_certify = '$mb_nonce $mb_lost_certify' where mb_id = '{$mb['mb_id']}' ";
sql_query($sql);

// 인증 링크 생성
$href = G5_BBS_URL.'/password_lost_certify.php?mb_no='.$mb['mb_no'].'&amp;mb_nonce='.$mb_nonce;

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

mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb['mb_email'], $subject, $content, 1);

run_event('password_lost2_after', $mb, $mb_nonce, $mb_lost_certify);

alert_close($generic_message);