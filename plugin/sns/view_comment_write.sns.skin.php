<?php
include_once('./_common.php');

if (!$board['bo_use_sns']) return;
?>

<ul id="bo_vc_sns">
<?php
//============================================================================
// 트위터
//----------------------------------------------------------------------------
if ($config['cf_twitter_key']) {
    $twitter_user = get_session("ss_twitter_user");
    if (!$twitter_user) {
        include_once(G5_SNS_PATH."/twitter/twitteroauth/twitteroauth.php");
        include_once(G5_SNS_PATH."/twitter/twitterconfig.php");

        $twitter_user = false;
        /*
        if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
            $twitter_url = G5_SNS_URL."/twitter/redirect.php";
        } else {
            $access_token = $_SESSION['access_token'];
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
            $content = $connection->get('account/verify_credentials');

            switch ($connection->http_code) {
                case 200:
                    $twitter_user = true;
                    $twitter_url = $connection->getAuthorizeURL($token);
                    break;
                default :
                    $twitter_url = G5_SNS_URL."/twitter/redirect.php";
            }
        }
        */
        $access_token = get_session('access_token');
        $access_oauth_token = isset($access_token['oauth_token']) ? $access_token['oauth_token'] : '';
        $access_oauth_token_secret = isset($access_token['oauth_token_secret']) ? $access_token['oauth_token_secret'] : '';
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_oauth_token, $access_oauth_token_secret);
        $content = $connection->get('account/verify_credentials');

        switch ($connection->http_code) {
            case 200:
                $twitter_user = true;
                $twitter_url = $connection->getAuthorizeURL($token);
                break;
            default :
                $twitter_url = G5_SNS_URL."/twitter/redirect.php";
        }
    }

    echo '<li class="sns_li_t '.($twitter_user?'':'sns_li_off').'">';
    if ($twitter_user) {
        echo '<img src="'.G5_SNS_URL.'/icon/twitter.png" id="twitter_icon">';
        echo '<label for="" class="sound_only">트위터 동시 등록</label>';
        echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" '.(get_cookie('ck_twitter_checked')?'checked':'').' value="1">';
    } else {
        echo '<label for="" class="sound_only">트위터 동시 등록</label>';
        echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" disabled value="1">';
        echo '<a href="'.$twitter_url.'" id="twitter_url" onclick="return false;" "><img src="'.G5_SNS_URL.'/icon/twitter.png" id="twitter_icon" width="20"></a>';
        echo '<script>$(function(){ $(document).on("click", "#twitter_url", function(){ window.open(this.href, "twitter_url", "width=600,height=250"); }); });</script>';
    }
    echo '</li>';
}
//============================================================================
?>
</ul>