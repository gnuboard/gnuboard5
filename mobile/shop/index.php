<?php
include_once("./_common.php");
include_once(G4_LIB_PATH.'/latest.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');

define("_INDEX_", TRUE);

include_once(G4_MSHOP_PATH.'/shop.head.php');
?>

<section class="sct_wrap">
    <header>
        <h2><a href="<?php echo G4_SHOP_URL; ?>/listtype.php?type=3">최신상품</a></h2>
        <p class="sct_wrap_hdesc"><?php echo $config['cf_title']; ?> 최신상품 모음</p>
    </header>
    <?php
    // 최신상품
    $type = 3;
    if ($default['de_mobile_type'.$type.'_list_us'])
    {
        mobile_display_type($type, $default["de_mobile_type{$type}_list_skin"], $default["de_mobile_type{$type}_list_mod"], $default["de_mobile_type{$type}_list_row"], $default["de_mobile_type{$type}_img_width"], $default["de_mobile_type{$type}_img_height"]);
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
    if ($default['de_mobile_type'.$type.'_list_use'])
    {
        mobile_display_type($type, $default["de_mobile_type{$type}_list_skin"], $default["de_mobile_type{$type}_list_mod"], $default["de_mobile_type{$type}_list_row"], $default["de_mobile_type{$type}_img_width"], $default["de_mobile_type{$type}_img_height"]);
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
    if ($default['de_mobile_type'.$type.'_list_use'])
    {
        mobile_display_type($type, $default["de_mobile_type{$type}_list_skin"], $default["de_mobile_type{$type}_list_mod"], $default["de_mobile_type{$type}_list_row"], $default["de_mobile_type{$type}_img_width"], $default["de_mobile_type{$type}_img_height"]);
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
    if ($default['de_mobile_type'.$type.'_list_use'])
    {
        mobile_display_type($type, $default["de_mobile_type{$type}_list_skin"], $default["de_mobile_type{$type}_list_mod"], $default["de_mobile_type{$type}_list_row"], $default["de_mobile_type{$type}_img_width"], $default["de_mobile_type{$type}_img_height"]);
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
    if ($default['de_mobile_type'.$type.'_list_use'])
    {
        mobile_display_type($type, $default["de_mobile_type{$type}_list_skin"], $default["de_mobile_type{$type}_list_mod"], $default["de_mobile_type{$type}_list_row"], $default["de_mobile_type{$type}_img_width"], $default["de_mobile_type{$type}_img_height"]);
    }
    ?>
</section>

<?php
include_once(G4_MSHOP_PATH.'/shop.tail.php');
?>