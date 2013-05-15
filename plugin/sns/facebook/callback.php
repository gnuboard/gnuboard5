<?php
include_once("./_common.php");
include_once(G4_SNS_PATH."/facebook/src/facebook.php");

$facebook = new Facebook(array(
  'appId'  => $config['cf_facebook_appid'],
  'secret' => $config['cf_facebook_secret'],
));

$user = $facebook->getUser();
print_r2($user); exit;

if ($user) {
    try {
        $user_profile = $facebook->api('/me');
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = NULL;
    }
}

if ($user_profile) {
    sql_query(" update {$g4['member_table']} set mb_facebook_user = '{$_REQUEST['code']}' where mb_id = '{$member['mb_id']}' ");
    alert_close("페이스북 승인이 성공 했습니다.\\n\\n페이스북 체크를 하시면 모든 댓글 등록시 페이스북에도 자동 등록됩니다.");
} else {
    sql_query(" update {$g4['member_table']} set mb_facebook_user = '' where mb_id = '{$member['mb_id']}' ");
    alert_close("페이스북을 로그아웃 했습니다.");
}
?>