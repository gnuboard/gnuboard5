<?
include_once('./_common.php');

// 금지 메일 도메인 검사
function prohibit_mb_email($reg_mb_email)
{
    global $config;
    list($id, $domain) = explode("@", $reg_mb_email);
    $email_domains = explode("\n", trim($config['cf_prohibit_email']));
    for ($i=0; $i<count($email_domains); $i++) {
        if (strtolower($domain) == strtolower($email_domains[$i]))
            return $domain;
    }
    return "";
}

echo prohibit_mb_email("kagla@naver.com");
echo prohibit_mb_email("kagla@hanmail.net");
?>