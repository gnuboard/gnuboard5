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

<div id="sidx_img">
    <img src="<?php echo G4_DATA_URL; ?>/common/main_img" alt="">
</div>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
    </header>
    <?php
    // 최신상품
    $type = 3;
    if ($default["de_type{$type}_list_use"])
    {
        display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
    }
    ?>
</section>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=1">히트상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 히트상품 모음</p>
    </header>
    <?php
    // 히트상품
    $type = 1;
    if ($default['de_type'.$type.'_list_use'])
    {
        display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
    }
    ?>
</section>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=2">추천상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 추천상품 모음</p>
    </header>
    <?php
    // 추천상품
    $type = 2;
    if ($default['de_type'.$type.'_list_use'])
    {
        display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
    }
    ?>
</section>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=4">인기상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 인기상품 모음</p>
    </header>
    <?php
    // 인기상품
    $type = 4;
    if ($default['de_type'.$type.'_list_use'])
    {
        display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
    }
    ?>
</section>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=5">할인상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 할인상품 모음</p>
    </header>
    <?php
    // 할인상품
    $type = 5;
    if ($default['de_type'.$type.'_list_use'])
    {
        display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
    }
    ?>
</section>

<section id="sidx_lat">
    <h2>커뮤니티 최신글</h2>
    <?php echo latest('shop_basic', 'notice', 5, 30); ?>
    <?php echo latest('shop_basic', 'free', 5, 25); ?>
    <?php echo latest('shop_basic', 'qa', 5, 20); ?>
</section>

<?php echo poll('shop_basic'); ?>

<!-- 방문자 수 -->
<?php echo visit('shop_basic'); ?>

<section id="sbn_idx">
    <h2>쇼핑몰 메인 배너</h2>
    <!-- 메인 배너 -->
    <?php echo display_banner('메인'); ?>
</section>


<?php
include_once(G4_SHOP_PATH.'/shop.tail.php');
?>