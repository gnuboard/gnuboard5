<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$board['bo_use_sns']) return;
?>
<?php if ($list[$i]['wr_facebook_user']) { ?>
<a href="https://www.facebook.com/profile.php?id=<?php echo $list[$i]['wr_facebook_user']; ?>" target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/facebook.png" alt="페이스북에도 등록됨"></a>
<?php } ?>
<?php if ($list[$i]['wr_twitter_user']) { ?>
<a href="https://www.twitter.com/<?php echo $list[$i]['wr_twitter_user']; ?>" target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/twitter.png" alt="트위터에도 등록됨"></a>
<?php } ?>
<?php if ($list[$i]['wr_me2day_user']) { ?>
<a href="http://me2day.net/<?php echo $list[$i]['wr_me2day_user']; ?>" target="_blank"><img src="<?php echo G4_SNS_URL; ?>/icon/me2day.png" alt="미투데이에도 등록됨"></a>
<?php } ?>
