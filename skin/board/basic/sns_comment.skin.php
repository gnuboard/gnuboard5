<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$is_member) return;
if (!$config['cf_facebook_use'] && !$config['cf_twitter_use']) return;
?>
<tr>
    <th scope="row">SNS 등록</th>
    <td>
        <div id="sns_facebook">
        <?php
        if ($config['cf_facebook_use']) {
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
                    $user = null;
                }
            }

            if ($user) {
                echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" '.($member['mb_facebook_checked']?'checked':'').' value="1">';
                echo '<img src="'.G4_SNS_URL.'/icon/facebook_on.png" id="facebook_icon">';
            } else {
                $facebook_url = $facebook->getLoginUrl(array("redirect_uri"=>G4_SNS_URL."/facebook/callback.php", "scope"=>"publish_stream,read_stream,offline_access", "display"=>"popup"));

                echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" disabled value="1">';
                echo '<a href="'.$facebook_url.'" id="facebook_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/facebook_'.($user?'on':'off').'.png" id="facebook_icon">';
                echo '<script>$(function(){ $("#facebook_url").click(function(){ window.open(this.href, "facebook_url", "width=600,height=250"); }); });</script>';
            }
        }

        if ($config['cf_twitter_use']) {
            include_once(G4_SNS_PATH."/twitter/twitteroauth/twitteroauth.php");
            include_once(G4_SNS_PATH."/twitter/config.php");

            $user = false;
            if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
                $twitter_url = G4_SNS_URL."/twitter/redirect.php";
            } else {
                $access_token = $_SESSION['access_token']; 
                $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
                $content = $connection->get('account/verify_credentials');
                //print_r2($content);

                switch ($connection->http_code) {
                    case 200:
                        $user = true;
                        $twitter_url = $connection->getAuthorizeURL($token);
                        break;
                    default : 
                        $twitter_url = G4_SNS_URL."/twitter/redirect.php";
                        // 안먹히는 코드 ㅠㅠ
                        if ($member['mb_twitter_token'] && $member['mb_twitter_token_secret']) {
                            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $member['mb_twitter_token'], $member['mb_twitter_token_secret']);
                            $content = $connection->get('account/verify_credentials');
                            if (200 == $connection->http_code) {
                                $user = true;
                                $twitter_url = $connection->getAuthorizeURL($token);
                            }
                        }
                }
            }

            if ($user) {
                echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" '.($member['mb_twitter_checked']?'checked':'').' value="1">';
                echo '<img src="'.G4_SNS_URL.'/icon/twitter_on.png" id="twitter_icon">';
            } else {
                echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" disabled value="1">';
                echo '<a href="'.$twitter_url.'" id="twitter_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/twitter_'.($user?'on':'off').'.png" id="twitter_icon">';
                echo '<script>$(function(){ $("#twitter_url").click(function(){ window.open(this.href, "twitter_url", "width=600,height=250"); }); });</script>';
            }
        }
        ?>
        </div>
    </td>
</tr>
