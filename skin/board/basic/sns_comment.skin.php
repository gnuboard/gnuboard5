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
//echo $token = $facebook->getAccessToken();

// CAABsXPS0wr4BAIasoXNLyI3Hg6Lqg8Qmze4vrLi2sBhenwe9Sx3qNu6hHRDGiKTVI6sDys3kmhP1B9kSoyfriZBMeTU5VEbJir8rc7QnWbyUZAZAijwd4UvPrJZCQTR4Y2fJTHVUCRILRir5Qqfs

//$user = $facebook->getUser();
//$facebook->setAccessToken("CAABsXPS0wr4BAIasoXNLyI3Hg6Lqg8Qmze4vrLi2sBhenwe9Sx3qNu6hHRDGiKTVI6sDys3kmhP1B9kSoyfriZBMeTU5VEbJir8rc7QnWbyUZAZAijwd4UvPrJZCQTR4Y2fJTHVUCRILRir5Qqfs");

if ($user) {
    try {
        $user_profile = $facebook->api('/me');

        $access_token = $facebook->getAccessToken();
        sql_query(" update {$g4['member_table']} set mb_facebook_token = '{$access_token}' where mb_id = '{$member['mb_id']}' ", true);

    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }
} else {
    if ($member['mb_facebook_token']) {
        $facebook->setAccessToken($member['mb_facebook_token']);
        try {
            $user_profile = $facebook->api('/me');
            //print_r2($user_profile);
            $user = $facebook->getUser();
        } catch (FacebookApiException $e) {
            error_log($e);
            $user = null;
        }
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
            $facebook_url = $facebook->getLoginUrl(array("redirect_uri"=>G4_SNS_URL."/facebook/callback.php", "scope"=>"user_website,publish_stream,read_stream,offline_access", "display"=>"popup"));

            echo '<input type="checkbox" name="facebook_checked" id="facebook_checked" disabled value="1">';
            echo '<a href="'.$facebook_url.'" id="facebook_url" onclick="return false;"><img src="'.G4_SNS_URL.'/icon/facebook_'.($user?'on':'off').'.png" id="facebook_icon">';
            echo '<script>$(function(){ $("#facebook_url").click(function(){ window.open(this.href, "facebook_url", "width=500,height=250"); }); });</script>';
        }
        ?>
        </div>
    </td>
</tr>
