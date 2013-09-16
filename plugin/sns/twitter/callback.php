<?php
include_once("./_common.php");

/**
 * @file
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

/* Start session and load lib */
//session_start();
require_once(G5_SNS_PATH.'/twitter/twitteroauth/twitteroauth.php');
require_once(G5_SNS_PATH.'/twitter/twitterconfig.php');

//print_r2($_SESSION); print_r2($_REQUEST); exit;

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
    $_SESSION['oauth_status'] = 'oldtoken';
    header('Location: ./clearsessions.php');
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/*
if (200 == $connection->http_code) {
  $_SESSION['status'] = 'verified';
  header('Location: ./index.php');
} else {
  header('Location: ./clearsessions.php');
}
exit;
*/

$g5['title'] = '트위터 콜백';
include_once(G5_PATH.'/head.sub.php');

if (200 == $connection->http_code) {
    $content  = $connection->get('account/verify_credentials');
    $sns_name = $content->name;
    $sns_user = $content->screen_name;

    set_cookie('ck_sns_name', $sns_name, 86400);
    set_session('ss_twitter_user', $sns_user);

    $g5_sns_url = G5_SNS_URL;

    echo <<<EOT
    <script>
    $(function() {
        document.write("<strong>트위터에 승인이 되었습니다.</strong>");

        var opener = window.opener;
        opener.$("#wr_name").val("{$sns_name}");
        opener.$("#twitter_icon").attr("src", "{$g5_sns_url}/icon/twitter.png");
        opener.$("#twitter_checked").attr("disabled", false);
        opener.$("#twitter_checked").attr("checked", true);
        window.close();
    });
    </script>
EOT;

} else {

    echo <<<EOT
    <script>
    $(function() {
        alert("트위터에 승인이 되지 않았습니다.");
        window.close();
    });
    </script>
EOT;

}

include_once(G5_PATH.'/tail.sub.php');
?>
