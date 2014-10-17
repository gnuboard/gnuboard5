<?php
$sub_menu = "200300";
include_once('./_common.php');

if (!$config['cf_email_use'])
    alert('환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.');

include_once(G5_LIB_PATH.'/mailer.lib.php');

auth_check($auth[$sub_menu], 'w');

check_demo();

$g5['title'] = '회원메일 테스트';

$name = get_text($member['mb_name']);
$nick = $member['mb_nick'];
$mb_id = $member['mb_id'];
$email = $member['mb_email'];

$sql = "select ma_subject, ma_content from {$g5['mail_table']} where ma_id = '{$ma_id}' ";
$ma = sql_fetch($sql);

$subject = $ma['ma_subject'];

$content = $ma['ma_content'];
$content = preg_replace("/{이름}/", $name, $content);
$content = preg_replace("/{닉네임}/", $nick, $content);
$content = preg_replace("/{회원아이디}/", $mb_id, $content);
$content = preg_replace("/{이메일}/", $email, $content);

$mb_md5 = md5($member['mb_id'].$member['mb_email'].$member['mb_datetime']);

$content = $content . '<p>더 이상 정보 수신을 원치 않으시면 [<a href="'.G5_BBS_URL.'/email_stop.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5.'" target="_blank">수신거부</a>] 해 주십시오.</p>';

mailer($config['cf_title'], $member['mb_email'], $member['mb_email'], $subject, $content, 1);

alert($member['mb_nick'].'('.$member['mb_email'].')님께 테스트 메일을 발송하였습니다. 확인하여 주십시오.');
?>
