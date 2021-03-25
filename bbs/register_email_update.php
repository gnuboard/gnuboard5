<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$mb_id = isset($_POST['mb_id']) ? substr(clean_xss_tags($_POST['mb_id']), 0, 20) : '';
$mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';

if(!$mb_id || !$mb_email)
    alert('올바른 방법으로 이용해 주십시오.', G5_URL);

$sql = " select mb_name from {$g5['member_table']} where mb_id = '{$mb_id}' and substring(mb_email_certify, 1, 1) = '0' ";
$mb = sql_fetch($sql);
if (!$mb) {
    alert("이미 메일인증 하신 회원입니다.", G5_URL);
}

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$sql = " select count(*) as cnt from {$g5['member_table']} where mb_id <> '{$mb_id}' and mb_email = '$mb_email' ";
$row = sql_fetch($sql);
if ($row['cnt']) {
    alert("{$mb_email} 메일은 이미 존재하는 메일주소 입니다.\\n\\n다른 메일주소를 입력해 주십시오.");
}

// 인증메일 발송
$subject = '['.$config['cf_title'].'] 인증확인 메일입니다.';

$mb_name = $mb['mb_name'];

// 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
$mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");

$certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

ob_start();
include_once ('./register_form_update_mail3.php');
$content = ob_get_contents();
ob_end_clean();

mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

$sql = " update {$g5['member_table']} set mb_email = '$mb_email' where mb_id = '$mb_id' ";
sql_query($sql);

alert("인증메일을 {$mb_email} 메일로 다시 보내 드렸습니다.\\n\\n잠시후 {$mb_email} 메일을 확인하여 주십시오.", G5_URL);