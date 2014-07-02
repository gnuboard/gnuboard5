<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$board['bo_use_sns']) return;

$sns_msg = urlencode(str_replace('\"', '"', $view['subject']));
//$sns_url = googl_short_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//$msg_url = $sns_msg.' : '.$sns_url;

/*
$facebook_url  = 'http://www.facebook.com/sharer/sharer.php?s=100&p[url]='.$sns_url.'&p[title]='.$sns_msg;
$twitter_url   = 'http://twitter.com/home?status='.$msg_url;
$gplus_url     = 'https://plus.google.com/share?url='.$sns_url;
*/

$sns_send  = G5_BBS_URL.'/sns_send.php?longurl='.urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//$sns_send .= '&amp;title='.urlencode(utf8_strcut(get_text($view['subject']),140));
$sns_send .= '&amp;title='.$sns_msg;

$facebook_url = $sns_send.'&amp;sns=facebook';
$twitter_url  = $sns_send.'&amp;sns=twitter';
$gplus_url    = $sns_send.'&amp;sns=gplus';
?>
<ul id="bo_v_sns">
    <li><a href="<?php echo $facebook_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/facebook.png" alt="페이스북으로 보내기"></a></li>
    <li><a href="<?php echo $twitter_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/twitter.png" alt="트위터로 보내기"></a></li>
    <li><a href="<?php echo $gplus_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/gplus.png" alt="구글플러스로 보내기"></a></li>
</ul>
