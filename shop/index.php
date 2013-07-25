<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/index.php');
    return;
}

define("_INDEX_", TRUE);

include_once(G4_LIB_PATH.'/latest.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');

include_once(G4_SHOP_PATH.'/shop.head.php');
?>

<?php
/*
$disp = new display_item(1);
echo $disp->run();


$disp = new display_item(1);
$disp->set_img_size(60, 0);
$disp->set_view("it_price", false);
$disp->set_view("it_id", true);
$disp->set_view("it_icon", true);
echo $disp->run();


$disp = new display_item();
$disp->set_event("1366852726");
$disp->set_list_skin("type10.skin.php");
$disp->set_img_size(125, 0);
$disp->set_list_mod(3);
$disp->set_list_row(4);
echo $disp->run();
exit;
*/
?>

<!-- 메인이미지 시작 { -->
<div id="sidx_img">
    <img src="<?php echo G4_DATA_URL; ?>/common/main_img" alt="">
</div>
<!-- } 메인이미지 끝 -->

<!-- 히트상품 시작 { -->
<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=1">히트상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 히트상품 모음</p>
    </header>
    <?php 
    $disp = new display_item(1);
    echo $disp->run();
    ?>
</section>
<!-- } 히트상품 끝 -->

<!-- 추천상품 시작 { -->
<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=2">추천상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 추천상품 모음</p>
    </header>
    <?php 
    $disp = new display_item(2);
    echo $disp->run();
    ?>
</section>
<!-- } 추천상품 끝 -->

<!-- 최신상품 시작 { -->
<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
    </header>
    <?php 
    $disp = new display_item(3);
    echo $disp->run();
    ?>
</section>
<!-- } 최신상품 끝 -->

<!-- 인기상품 시작 { -->
<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 인기상품 모음</p>
    </header>
    <?php 
    $disp = new display_item(4);
    echo $disp->run();
    ?>
</section>
<!-- } 인기상품 끝 -->

<!-- 할인상품 시작 { -->
<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=5">할인상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 할인상품 모음</p>
    </header>
    <?php 
    $disp = new display_item(5);
    echo $disp->run();
    ?>
</section>
<!-- } 할인상품 끝 -->

<!-- 커뮤니티 최신글 시작 { -->
<section id="sidx_lat">
    <h2>커뮤니티 최신글</h2>
    <?php echo latest('shop_basic', 'notice', 5, 30); ?>
    <?php echo latest('shop_basic', 'free', 5, 25); ?>
    <?php echo latest('shop_basic', 'qa', 5, 20); ?>
</section>
<!-- } 커뮤니티 최신글 끝 -->

<?php echo poll('shop_basic'); // 설문조사 ?>

<?php echo visit('shop_basic'); // 접속자 ?>

<!-- 메인배너 시작 { -->
<section id="sbn_idx">
    <h2>쇼핑몰 메인 배너</h2>
    <?php echo display_banner('메인'); ?>
</section>
<!-- } 메인배너 끝 -->

<?php
include_once(G4_SHOP_PATH.'/shop.tail.php');
?>