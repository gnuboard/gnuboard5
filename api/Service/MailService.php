<?php

namespace API\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService
{
    private ?PHPMailer $mail;


    public function __construct()
    {
        $this->mail = $this->mailSetting();
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function mailSetting()
    {
        $is_debug_mode = G5_DEBUG ? SMTP::DEBUG_LOWLEVEL : SMTP::DEBUG_OFF;
        $host = G5_SMTP;
        $username = G5_SMTP_USER;
        $password = G5_SMTP_PASSWORD;
        $port = G5_SMTP_PORT;

        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = $is_debug_mode;       //Enable verbose debug output
            $mail->isSMTP();                         //Send using SMTP
            $mail->Host = $host;                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                  //Enable SMTP authentication
            $mail->Username = $username;             //SMTP username
            $mail->Password = $password;             //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  //Enable implicit TLS encryption
            $mail->Port = $port;                     //TCP port to connect to; use 587 (`SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`) 

        } catch (\RuntimeException $e) {
            error_log($e->getMessage());
            return null;
        }

        return $mail;
    }

    /**
     * @param string $from_name 보낸이 이름
     * @param string $from_mail 보낸이 메일
     * @param string $to 받는이 메일
     * @param string $subject 제목
     * @param string $content 내용
     * @param string $type 0:텍스트, 1:html
     * @param array $files 첨부파일
     * @param string $cc 참조
     * @param string $bcc 숨은참조
     * @return bool
     */
    public function send($from_name, $from_mail, $to, $subject, $content, $type = 0, $files = [], $cc = '', $bcc = '')
    {
        // 메일발송 사용을 하지 않는다면
        if (!ConfigService::getConfig()['cf_email_use']) {
            error_log('config cf_email_use 메일발송 사용을 하지 않습니다.');
            return false;
        }

        if ($type != 1) {
            $content = nl2br($content);
        }

        try {
            $this->mail->CharSet = 'UTF-8';
            $this->mail->FromName = $from_name;
            $this->mail->From = $from_mail;
            $this->mail->Subject = $subject;
            $this->mail->msgHTML($content);
            $this->mail->addAddress($to);

            if ($cc) {
                $this->mail->addCC($cc);
            }

            if ($bcc) {
                $this->mail->addBCC($bcc);
            }

            if ($files) {
                foreach ($files as $file) {
                    $this->mail->addAttachment($file['path'], $file['name']);
                }
            }

            $send_result = $this->mail->send();
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();
            $this->mail->clearReplyTos();
            $this->mail->clearAllRecipients();

            return $send_result;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}