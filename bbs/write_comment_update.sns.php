<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

if ($config['cf_sns_use']) return;

// 페이스북
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
        } catch(FacebookApiException $e) {
            ;;;
        }
    }
}

// 트위터
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
    }
}
?>