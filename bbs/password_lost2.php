<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/abuse_rate.lib.php');

if ($is_member) {
    alert_close('이미 로그인중입니다.', G5_URL);
}

// CAPTCHA 오답 여부와 무관하게 직접 POST 및 새 세션의 반복 요청을 제한한다.
$rate_allowed = g5_password_lost_rate_allow('ip', $_SERVER['REMOTE_ADDR']);
if ($rate_allowed === null) {
    alert_close('현재 비밀번호 찾기를 이용할 수 없습니다. 관리자에게 문의해 주십시오.');
}
if (!$rate_allowed) {
    alert_close('요청이 많습니다. 잠시 후 다시 이용해 주십시오.');
}

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$email_input = isset($_POST['mb_email']) && is_string($_POST['mb_email']) ? $_POST['mb_email'] : '';
$email = get_email_address(trim($email_input));

if (!$email)
    alert_close('메일주소 오류입니다.');

// OWASP 권장: 이메일 존재 여부와 무관하게 동일한 응답 메시지 사용
// (이메일 열거 공격 방지)
$generic_message = '입력하신 정보와 일치하는 회원이 있으면 비밀번호 찾기 안내 메일이 발송됩니다.\\n최근 요청이 있거나 발송이 제한된 경우 추가 발송되지 않을 수 있습니다.\\n메일함과 스팸함을 확인하고, 메일이 없으면 잠시 후 다시 요청해 주십시오.';

$security_mail_url = g5_security_mail_base_url();
if ($security_mail_url === false) {
    error_log('[g5 security mail] Valid G5_DOMAIN is required.');
    alert_close($generic_message);
}

$sql = " select count(*) as cnt from {$g5['member_table']} where mb_email = '$email' ";
$row = sql_fetch($sql);
if ($row['cnt'] > 1) {
    // 시스템 데이터 무결성 이슈 - 운영자 로그에만 기록하고 사용자에겐 일반 메시지
    @error_log("[g5 password_lost2] Duplicate email detected: $email (count={$row['cnt']})");
    alert_close($generic_message);
}

$sql = " select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime, mb_leave_date, mb_lost_certify from {$g5['member_table']} where mb_email = '$email' ";
$mb = sql_fetch($sql);

// 회원이 없거나 탈퇴했거나 관리자이면 메일 발송 없이 동일한 메시지로 응답
if (empty($mb['mb_id']) || $mb['mb_leave_date'] || is_admin($mb['mb_id'])) {
    alert_close($generic_message);
}

// 실제 발송 대상의 수신자·전체 한도를 함께 확보한다.
// 어느 한도가 부족해도 다른 한도의 사용량을 남기지 않는다.
if (empty($config['cf_email_use'])) {
    alert_close($generic_message);
}
$mail_reservation = array();
$rate_allowed = g5_password_lost_mail_allow($email, $mail_reservation);
if ($rate_allowed === null) {
    alert_close('현재 비밀번호 찾기를 이용할 수 없습니다. 관리자에게 문의해 주십시오.');
}
if (!$rate_allowed) {
    alert_close($generic_message);
}

// 임시비밀번호 발급 (CSPRNG 사용)
$change_password = get_random_token_string(5);  // 10자리 hex (0-9, a-f)
$mb_lost_certify = get_encrypt_string($change_password);

// 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용 (CSPRNG 사용)
$mb_nonce = get_random_token_string(16);

// 임시비밀번호와 난수를 mb_lost_certify 필드에 저장
$previous_certify = $mb['mb_lost_certify'];
$pending_certify = $mb_nonce.' '.$mb_lost_certify;
if (!g5_password_lost_compare_update($mb['mb_no'], $previous_certify, $pending_certify)) {
    g5_password_lost_mail_failed($mail_reservation);
    alert_close($generic_message);
}

// 인증 링크 생성
$href = $security_mail_url.'/'.G5_BBS_DIR.'/password_lost_certify.php?mb_no='.$mb['mb_no'].'&amp;mb_nonce='.$mb_nonce;

$subject = "[".$config['cf_title']."] 요청하신 회원정보 찾기 안내 메일입니다.";

$content = "";

$content .= '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
$content .= '<div style="border:1px solid #dedede">';
$content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
$content .= '회원정보 찾기 안내';
$content .= '</h1>';
$content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
$content .= '<a href="'.htmlspecialchars($security_mail_url, ENT_QUOTES, 'UTF-8').'" target="_blank">'.$config['cf_title'].'</a>';
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

$mail_sent = mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb['mb_email'], $subject, $content, 1);
if (!$mail_sent) {
    // 다른 요청에서 사용하거나 갱신한 인증값은 덮어쓰지 않는다.
    g5_password_lost_compare_update($mb['mb_no'], $pending_certify, $previous_certify);
    g5_password_lost_mail_failed($mail_reservation);
    alert_close($generic_message);
}

run_event('password_lost2_after', $mb, $mb_nonce, $mb_lost_certify);

alert_close($generic_message);