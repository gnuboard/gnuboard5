<?php
include_once("./_common.php");

require_once(G4_SNS_PATH."/facebook/src/facebook.php");

//https://www.facebook.com/dialog/oauth?client_id=440461349373258&redirect_uri=http%3A%2F%2Fchin.so%2Fbbs%2Fajax.sns.php%3Fsns_name%3Dfacebook%26_%3D1364434991642&state=951675978f0f4a6f5a2dfc01fb45e653

//http://chin.so/plugin/facebook/callback.php?code=AQDKN0YBYm9NB_Ca_XtFZckplQk74Oubsd8OVuFDMHGiXDdiZ9cxr-Sw2cs37XvKQ4h0ryhC21nAbB2I0KhqtWaFyPGpDEnQuMMxZRdwKZvvwRRQrGzl7ttI9oQAQ5Y0_WuROIl-4lKTifbZseSP5tJt_YTX8CXfc7h5w6C73N7tPS66UvtsmbJTYT02uXjTiCKPGnqeTgZjZ7XjzRYEn_l_&state=951675978f0f4a6f5a2dfc01fb45e653#_=_

$config = array(
    'appId' => FACEBOOK_APPID,
    'secret' => FACEBOOK_SECRET,
);

$facebook = new Facebook($config);

if ($_GET['logout'] == 'yes') {
    unset($_SESSION['fb_'.FACEBOOK_APPID.'_code']);
    unset($_SESSION['fb_'.FACEBOOK_APPID.'_user_id']);
    unset($_SESSION['fb_'.FACEBOOK_APPID.'_access_token']);
    header("Location: ".$_SERVER['PHP_SELF']."");
}

$user = $facebook->getUser();

if ($user) {
    try {
        $user_profile = $facebook->api('/me');
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = NULL;
    }
}

if ($user_profile) {
    sql_query(" update {$g4['member_table']} set mb_sns_facebook = '{$_REQUEST['code']}' where mb_id = '{$member['mb_id']}' ");
    alert_close("페이스북 승인이 성공 했습니다.\\n\\n댓글의 체크를 해제하지 않으시면 모든 댓글 등록시 페이스북에도 자동 등록됩니다.");
} else {
    sql_query(" update {$g4['member_table']} set mb_sns_facebook = '' where mb_id = '{$member['mb_id']}' ");
    alert_close("페이스북을 로그아웃 했습니다.");
}
