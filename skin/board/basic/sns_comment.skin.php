<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$is_member) return;
if (!$config['cf_facebook_use']) return;

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
} else {
    $appid  = $config['cf_facebook_appid'];
    $secret = $config['cf_facebook_secret'];
    $access_token = $_COOKIE['fbs_'.$appid.'_access_token'];

    $graph_url = "https://graph.facebook.com/oauth/access_token?client_id=$appid&client_secret=$secret&grant_type=client_credentials";
    $access_token = file_get_contents($graph_url);

    if($access_token){

        $graph_url = "https://graph.facebook.com/oauth/access_token_info?client_id=$appid&" . $access_token;
        $access_token_info = json_decode(file_get_contents($graph_url));
        
        function nonceHasBeenUsed($auth_nonce) {
            // Here you would check your database to see if the nonce
            // has been used before. For the sake of this example, we'll
            // just assume the answer is "no".
            return false;
        }

        if (nonceHasBeenUsed($access_token_info->auth_nonce) != true) {
            if ($result = @file_get_contents("https://graph.facebook.com/me/?".$access_token)) {
                $result = json_decode($result, true);
                print_r2($result);
                $user = $result['id'];
            }
        }
        /*
        if ($result = @file_get_contents("https://graph.facebook.com/me/?access_token=".$access_token)) {
            $result = json_decode($result, true);

            print_r2($result);
            //echo $_SESSION['uid'] = $result['id'];
            //if ($result = @file_get_contents("https://graph.facebook.com/{$result['id']}/accounts/test-users?installed=true&name={$result['name']}&locale={$result['locale']}&permissions=read_stream&method=post&access_token=".$access_token)) {
            if ($result = @file_get_contents("https://graph.facebook.com/{$result['id']}/accounts/test-users?access_token=".$access_token)) {
                $result = json_decode($result, true);
                print_r2($result);
                $user = $result['id'];
            }
        }
        */
    }
}
?>
<tr>
    <th scope="row">SNS 등록</th>
    <td>
        <div id="sns_facebook">
        <?php
        if ($user) {
            echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" '.($member['mb_facebook_checked']?'checked':'').' value="1">';
            echo '<img src="'.G4_SNS_URL.'/icon/facebook_on.png" id="facebook_icon">';
        } else {
            $facebook_url = $facebook->getLoginUrl(array("redirect_uri"=>G4_SNS_URL."/facebook/callback.php", "scope"=>"publish_stream,read_stream,offline_access", "display"=>"popup"));

            echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" disabled value="1">';
            echo '<a href="'.$facebook_url.'" id="facebook_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/facebook_'.($user?'on':'off').'.png" id="facebook_icon">';
            echo '<script>$(function(){ $("#facebook_url").click(function(){ window.open(this.href, "facebook_url", "width=500,height=250"); }); });</script>';
        }
        ?>
        </div>
    </td>
</tr>
