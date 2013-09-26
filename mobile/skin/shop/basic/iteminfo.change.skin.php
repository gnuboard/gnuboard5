<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<section id="sit_ex">
    <h2>교환/반품</h2>
    <?php echo pg_anchor($info); ?>

    <?php echo conv_content($default['de_change_content'], 1); ?>
</section>