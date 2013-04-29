<?php
include_once("./_common.php");
include_once(G4_LIB_PATH.'/latest.lib.php');

define("_INDEX_", TRUE);

include_once(G4_SHOP_PATH.'/shop.head.php');
?>

<script src="<?php echo G4_JS_URL; ?>/shop.js"></script>

<div id="sidx_img">
    <img src="<?=G4_DATA_URL?>/common/main_img" alt="">
</div>

<div>
<?php
// 히트상품
$type = 1;
if ($default['de_type'.$type.'_list_use'])
{
    echo '<a href="'.G4_SHOP_URL.'/listtype.php?type='.$type.'"><img src="'.G4_SHOP_URL.'/img/bar_type'.$type.'.gif" alt="히트상품"></a><br>';
    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
}
?>
</div>
<div>
<?php
// 추천상품
$type = 2;
if ($default['de_type'.$type.'_list_use'])
{
    echo '<a href="'.G4_SHOP_URL.'/listtype.php?type='.$type.'"><img src="'.G4_SHOP_URL.'/img/bar_type'.$type.'.gif" alt="추천상품"></a><br>';
    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
}
?>
</div>
<div>
<?php
// 인기상품
$type = 4;
if ($default['de_type'.$type.'_list_use'])
{
    echo '<a href="'.G4_SHOP_URL.'"/listtype.php?type='.$type.'"><img src="'.G4_SHOP_URL.'/img/bar_type'.$type.'.gif" alt="인기상품"></a><br>';
    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
}
?>
</div>
<div>
<?php
// 할인상품
$type = 5;
if ($default['de_type'.$type.'_list_use'])
{
    echo '<a href="'.G4_SHOP_URL.'"/listtype.php?type='.$type.'"><img src="'.G4_SHOP_URL.'/img/bar_type'.$type.'.gif" alt="할인상품"></a><br>';
    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
}
?>
</div>
<div>
<?=latest('basic', 'qa', 5, 30);?>
</div>
<div>
<?=latest('basic', 'free', 5, 30);?>
</div>
<div>
<!-- 공지사항 -->
<?//=latest('basic', 'notice', 3, 25);?>
</div>
<div>
<?
// 최신상품
$type = 3;
if ($default["de_type{$type}_list_use"])
{
    echo '<a href="'.G4_SHOP_URL.'"/listtype.php?type='.$type.'"><img src="'.G4_SHOP_URL.'/img/bar_type'.$type.'.gif" alt="최신상품"></a><br>';
    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
}
?>
</div>
<div>
<!-- 온라인 투표 -->
<?=poll('basic');?>
</div>
<div>
<!-- 방문자 수 -->
<?//=visit('basic');?>
</div>

<div>
<!-- 메인 배너 -->
<?=display_banner('메인');?>
</div>

<?
include_once(G4_SHOP_PATH.'/shop.tail.php');
?>