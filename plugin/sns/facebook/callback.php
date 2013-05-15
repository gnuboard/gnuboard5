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
    $g4_sns_url = G4_SNS_URL;
    echo <<<EOT
<script>
$(function() {
    var opener = window.opener;
    opener.$("#facebook_icon").attr("src", "{$g4_sns_url}/icon/facebook_on.png");
    opener.$("#facebook_checked").attr("disabled", false);
    opener.$("#facebook_checked").attr("checked", true);
    //alert("페이스북 승인이 되었습니다.");
    window.close();
});
</script>
EOT;
} else {
}

include_once(G4_PATH.'/tail.sub.php');
?>