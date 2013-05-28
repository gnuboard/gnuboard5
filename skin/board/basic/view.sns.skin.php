<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$config['cf_sns_use']) return;

$sns_msg = urlencode(str_replace('\"', '"', $view['subject']));
$sns_url = googl_short_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$msg_url = $sns_msg.' : '.$sns_url;

// 카카오톡 매뉴얼 : https://github.com/kakao/kakaolink-web
$kakao_appid   = $_SERVER['HTTP_HOST']; // Mobile Site Domain 정확히 입력하지 않을 경우 이용이 제한될 수 있습니다.
$kakao_appname = $g4['title'];

$facebook_url  = "http://www.facebook.com/sharer/sharer.php?s=100&p[url]=".$sns_url."&p[title]=".$sns_msg;
$twitter_url   = "http://twitter.com/home?status=".$msg_url;
$me2day_url    = "http://me2day.net/posts/new?new_post[body]=".$msg_url;
$gplus_url     = "https://plus.google.com/share?url=".$sns_url;
$kakao_url     = "kakaolink://sendurl?msg={$sns_msg}&url={$sns_url}&appid={$kakao_appid}&appver=1.0&type=link&appname={$kakao_appname}&apiver=2.0";

/*
$sns_send  = G4_BBS_URL.'/sns_send.php?longurl='.urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//$sns_send .= '&amp;title='.urlencode(utf8_strcut(get_text($view['subject']),140));
$sns_send .= '&amp;title='.$sns_msg;
*/

?>
<ul>
    <li><a href="<?php echo $facebook_url; ?>" target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/facebook.png"  alt="이 글을 내 페이스북 계정으로 보내기"></a></li>
    <li><a href="<?php echo $twitter_url; ?>"  target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/twitter.png"   alt="이 글을 내 트위터 계정으로 보내기"></a></li>
    <li><a href="<?php echo $me2day_url; ?>"   target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/me2day.png"    alt="이 글을 내 미투데이 계정으로 보내기"></a></li>
    <li><a href="<?php echo $gplus_url; ?>"    target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/gplus.png"     alt="이 글을 내 구글플러스 계정으로 보내기"></a></li>
    <li><a href="<?php echo $kakao_url; ?>"    target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/kakaotalk.png" alt="이 글을 카카오톡으로 보내기"></a></li>
</ul>
