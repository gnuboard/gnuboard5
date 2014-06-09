<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$board['bo_use_sns']) return;

$sns_msg = urlencode(str_replace('\"', '"', $view['subject']));
$sns_url = googl_short_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$msg_url = $sns_msg.' : '.$sns_url;

// 카카오톡 매뉴얼 : https://github.com/kakao/kakaolink-web
$kakao_appid   = $_SERVER['HTTP_HOST']; // Mobile Site Domain 정확히 입력하지 않을 경우 이용이 제한될 수 있습니다.
$kakao_appname = urlencode(str_replace('\"', '"', $g5['title']));

$facebook_url  = 'http://www.facebook.com/sharer/sharer.php?s=100&p[url]='.$sns_url.'&p[title]='.$sns_msg;
$twitter_url   = 'http://twitter.com/home?status='.$msg_url;
$gplus_url     = 'https://plus.google.com/share?url='.$sns_url;

/*
$sns_send  = G5_BBS_URL.'/sns_send.php?longurl='.urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//$sns_send .= '&amp;title='.urlencode(utf8_strcut(get_text($view['subject']),140));
$sns_send .= '&amp;title='.$sns_msg;
*/

?>
<ul id="bo_v_sns">
    <li><a href="<?php echo $facebook_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/facebook.png" alt="페이스북으로 보내기"></a></li>
    <li><a href="<?php echo $twitter_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/twitter.png" alt="트위터로 보내기"></a></li>
    <li><a href="<?php echo $gplus_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/gplus.png" alt="구글플러스로 보내기"></a></li>
    <?php
    if (G5_IS_MOBILE) {
        $kakao_url = 'kakaolink://sendurl?msg='.$sns_msg.'&amp;url='.$sns_url.'&amp;appid='.$kakao_appid.'&amp;appver=1.0&amp;type=link&amp;appname='.$kakao_appname.'&amp;apiver=2.0';
    ?>
    <li><a href="<?php echo $kakao_url; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/kakaotalk.png" alt="카카오톡으로 보내기"></a></li>
    <?php
    }
    ?>
</ul>
