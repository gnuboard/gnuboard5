<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if (!$config['cf_email_use'])
    alert('환경설정에서 "메일발송 사용"에 체크하셔야 메일을 발송할 수 있습니다.\\n\\n관리자에게 문의하시기 바랍니다.');

if (!$is_member && $config['cf_formmail_is_member'])
    alert_close('회원만 이용하실 수 있습니다.');

$to = base64_decode($to);

if (substr_count($to, "@") > 1)
    alert_close('한번에 한사람에게만 메일을 발송할 수 있습니다.');


if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}


for ($i=1; $i<=$attach; $i++) {
    if ($_FILES['file'.$i]['name'])
        $file[] = attach_file($_FILES['file'.$i]['name'], $_FILES['file'.$i]['tmp_name']);
}

$content = stripslashes($content);
if ($type == 2) {
    $type = 1;
    $content = str_replace("\n", "<br>", $content);
}

// html 이면
if ($type) {
    $current_url = G5_URL;
    $mail_content = '<!doctype html><html lang="ko"><head><meta charset="utf-8"><title>메일보내기</title><link rel="stylesheet" href="'.$current_url.'/style.css"></head><body>'.$content.'</body></html>';
}
else
    $mail_content = $content;

mailer($fnick, $fmail, $to, $subject, $mail_content, $type, $file);

//$html_title = $tmp_to . "님께 메일발송";
$html_title = '메일 발송중';
include_once(G5_PATH.'/head.sub.php');

alert_close('메일을 정상적으로 발송하였습니다.');

include_once(G5_PATH.'/tail.sub.php');
?>