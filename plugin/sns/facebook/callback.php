<?php
include_once("./_common.php");
include_once(G4_SNS_PATH."/facebook/src/facebook.php");

$facebook = new Facebook(array(
    'appId'  => $config['cf_facebook_appid'],
    'secret' => $config['cf_facebook_secret']
));

$user = $facebook->getUser();

if ($user) {
    try {
        $user_profile = $facebook->api('/me');
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = NULL;
    }
}

$g4['title'] = '페이스북 콜백';
include_once(G4_PATH.'/head.sub.php');

if ($user) {

    $access_token = $facebook->getAccessToken();

    $appid = $config['cf_facebook_appid'];

    setcookie('fbs_'.$appid,                 1,                                        G4_SERVER_TIME + 86400 * 31, '/', G4_COOKIE_DOMAIN);
    setcookie('fbs_'.$appid.'_code',         $_SESSION['fb_'.$appid.'_code'],          G4_SERVER_TIME + 86400 * 31, '/', G4_COOKIE_DOMAIN);
    setcookie('fbs_'.$appid.'_access_token', $_SESSION['fb_'.$appid.'_access_token'],  G4_SERVER_TIME + 86400 * 31, '/', G4_COOKIE_DOMAIN);
    setcookie('fbs_'.$appid.'_user_id',      $_SESSION['fb_'.$appid.'_user_id'],       G4_SERVER_TIME + 86400 * 31, '/', G4_COOKIE_DOMAIN);

    sql_query(" update {$g4['member_table']} set mb_facebook_token = '{$access_token}' where mb_id = '{$member['mb_id']}' ", true);
    
    $g4_sns_url = G4_SNS_URL;

    echo <<<EOT
    <script>
    $(function() {
        document.write("<strong>페이스북 승인이 되었습니다.</strong>");

        var opener = window.opener;
        opener.$("#facebook_icon").attr("src", "{$g4_sns_url}/icon/facebook_on.png");
        opener.$("#facebook_checked").attr("disabled", false);
        opener.$("#facebook_checked").attr("checked", true);
        window.close();
    });
    </script>
EOT;

} else {

    echo <<<EOT
    <script>
    $(function() {
        alert("페이스북 승인이 되지 않았습니다.");
        window.close();
    });
    </script>
EOT;

}

include_once(G4_PATH.'/tail.sub.php');
?>