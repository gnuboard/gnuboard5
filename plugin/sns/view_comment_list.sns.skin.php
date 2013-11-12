<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$mobile_sns_icon = '';
if (G5_IS_MOBILE) $sns_mc_icon = '';
else $sns_mc_icon = '_cmt';

if (!$board['bo_use_sns']) return;
?>
<?php if ($list[$i]['wr_facebook_user']) { ?>
<a href="https://www.facebook.com/profile.php?id=<?php echo $list[$i]['wr_facebook_user']; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/facebook<?php echo $sns_mc_icon; ?>.png" alt="페이스북에도 등록됨"></a>
<?php } ?>
<?php if ($list[$i]['wr_twitter_user']) { ?>
<a href="https://www.twitter.com/<?php echo $list[$i]['wr_twitter_user']; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/twitter<?php echo $sns_mc_icon; ?>.png" alt="트위터에도 등록됨"></a>
<?php } ?>
