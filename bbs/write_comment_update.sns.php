<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

if (!$config['cf_sns_use']) return;

set_cookie('ck_facebook_checked', false);
set_cookie('ck_twitter_checked' , false);
set_cookie('ck_me2day_checked'  , false);

//============================================================================
// 페이스북
//----------------------------------------------------------------------------
if ($_POST['facebook_checked']) {
    include_once(G4_SNS_PATH."/facebook/src/facebook.php");

    $facebook = new Facebook(array(
        'appId'  => $config['cf_facebook_appid'],
        'secret' => $config['cf_facebook_secret']
    ));

    $user = $facebook->getUser();

    if ($user) {
        try {
            $link = G4_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr['wr_parent'].'&#c_'.$comment_id;
            $attachment = array(
                'message'       => stripslashes($wr_content),
                'name'          => $wr_subject,
                'link'          => $link,
                'description'   => stripslashes(strip_tags($wr['wr_content']))
            );
            // 등록
            $facebook->api('/me/feed/', 'post', $attachment);
            //$errors = error_get_last(); print_r2($errros); exit;

            set_cookie('ck_facebook_checked', true, 86400*31);
        } catch(FacebookApiException $e) {
            ;;;
        }
    }
}
//============================================================================


//============================================================================
// 트위터
//----------------------------------------------------------------------------
if ($_POST['twitter_checked']) {
    include_once(G4_SNS_PATH."/twitter/twitteroauth/twitteroauth.php");
    include_once(G4_SNS_PATH."/twitter/config.php");
    
    if ( !(empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) ) {
        $post = googl_short_url($comment_url).' '.$wr_content;
        $post = utf8_strcut($post, 140);

        $access_token = $_SESSION['access_token'];
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        // 등록
        $connection->post('statuses/update', array('status' => $post));
    
        set_cookie('ck_twitter_checked', true, 86400*31);
    }
}
//============================================================================


//============================================================================
// 미투데이
//----------------------------------------------------------------------------
if ($_POST['me2day_checked']) {
    if (!empty($_SESSION['me2day']['user_id']) && !empty($_SESSION['me2day']['user_key'])) {
        $user_id  = $_SESSION['me2day']['user_id'];
        $user_key = $_SESSION['me2day']['user_key'];
        $auth_key = "12345678" . md5("12345678" . $user_key);
        $result = file_get_contents("http://me2day.net/api/create_post/{$user_id}.json?uid={$user_id}&ukey={$auth_key}&akey=".$config['cf_me2day_key']."&post[body]=".urlencode($wr_content));

        set_cookie('ck_me2day_checked', true, 86400*31);
    }
}
//============================================================================
?>