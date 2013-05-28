<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$board['bo_use_sns']) return;
?>
<tr>
    <th scope="row">SNS 등록</th>
    <td>
        <div id="sns_facebook">
        <?php
        //============================================================================
        // 페이스북
        //----------------------------------------------------------------------------
        if ($config['cf_facebook_appid']) {
            include_once(G4_SNS_PATH."/facebook/src/facebook.php");
            $facebook = new Facebook(array(
                'appId'  => $config['cf_facebook_appid'],
                'secret' => $config['cf_facebook_secret']
            ));

            $facebook_user = $facebook->getUser();

            if ($facebook_user) {
                try {
                    $facebook_user_profile = $facebook->api('/me');
                } catch (FacebookApiException $e) {
                    error_log($e);
                    $facebook_user = null;
                }
            }

            if ($facebook_user) {
                echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" '.(get_cookie('ck_facebook_checked')?'checked':'').' value="1">';
                echo '<img src="'.G4_SNS_URL.'/icon/facebook.png" id="facebook_icon">';
            } else {
                $facebook_url = $facebook->getLoginUrl(array("redirect_uri"=>G4_SNS_URL."/facebook/callback.php", "scope"=>"publish_stream,read_stream,offline_access", "display"=>"popup"));

                echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" disabled value="1">';
                echo '<a href="'.$facebook_url.'" id="facebook_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/facebook'.($facebook_user?'':'_off').'.png" id="facebook_icon"></a>';
                echo '<script>$(function(){ $("#facebook_url").click(function(){ window.open(this.href, "facebook_url", "width=600,height=250"); }); });</script>';
            }
        }
        //============================================================================


        //============================================================================
        // 트위터
        //----------------------------------------------------------------------------
        if ($config['cf_twitter_key']) {
            include_once(G4_SNS_PATH."/twitter/twitteroauth/twitteroauth.php");
            include_once(G4_SNS_PATH."/twitter/twitterconfig.php");

            $twitter_user = false;
            if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
                $twitter_url = G4_SNS_URL."/twitter/redirect.php";
            } else {
                $access_token = $_SESSION['access_token']; 
                $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
                $content = $connection->get('account/verify_credentials');
                //print_r2($content);

                switch ($connection->http_code) {
                    case 200:
                        $twitter_user = true;
                        $twitter_url = $connection->getAuthorizeURL($token);
                        break;
                    default : 
                        $twitter_url = G4_SNS_URL."/twitter/redirect.php";
                        // 안먹히는 코드 ㅠㅠ
                        if ($member['mb_twitter_token'] && $member['mb_twitter_token_secret']) {
                            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $member['mb_twitter_token'], $member['mb_twitter_token_secret']);
                            $content = $connection->get('account/verify_credentials');
                            if (200 == $connection->http_code) {
                                $twitter_user = true;
                                $twitter_url = $connection->getAuthorizeURL($token);
                            }
                        }
                }
            }

            if ($twitter_user) {
                echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" '.(get_cookie('ck_twitter_checked')?'checked':'').' value="1">';
                echo '<img src="'.G4_SNS_URL.'/icon/twitter.png" id="twitter_icon">';
            } else {
                echo '<input type="checkbox" name="twitter_checked" id="twitter_checked" disabled value="1">';
                echo '<a href="'.$twitter_url.'" id="twitter_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/twitter'.($twitter_user?'':'_off').'.png" id="twitter_icon"></a>';
                echo '<script>$(function(){ $("#twitter_url").click(function(){ window.open(this.href, "twitter_url", "width=600,height=250"); }); });</script>';
            }
        }
        //============================================================================


        //============================================================================
        // 미투데이
        //----------------------------------------------------------------------------
        if ($config['cf_me2day_key']) {
            $me2day_user = false;
            if (empty($_SESSION['me2day']['user_id']) || empty($_SESSION['me2day']['user_key'])) {
                $result = json_decode(file_get_contents("http://me2day.net/api/get_auth_url.json?akey=".$config['cf_me2day_key']));
                $me2day_url = $result->url;
            } else {
                $me2day_user = true;
            }

            if ($me2day_user) {
                echo '<input type="checkbox" name="me2day_checked" id="me2day_checked" '.(get_cookie('ck_me2day_checked')?'checked':'').' value="1">';
                echo '<img src="'.G4_SNS_URL.'/icon/me2day.png" id="me2day_icon">';
            } else {
                echo '<input type="checkbox" name="me2day_checked" id="me2day_checked" disabled value="1">';
                echo '<a href="'.$me2day_url.'" id="me2day_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/me2day'.($me2day_user?'':'_off').'.png" id="me2day_icon"></a>';
                echo '<script>$(function(){ $("#me2day_url").click(function(){ window.open(this.href, "me2day_url", "width=1000,height=800"); }); });</script>';
            }
        }
        //============================================================================
        ?>
        </div>
    </td>
</tr>
