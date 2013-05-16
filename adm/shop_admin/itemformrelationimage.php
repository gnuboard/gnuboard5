<?php
$sub_menu = '400300';
include_once('./_common.php');

$it_id = $_POST['it_id'];
$width = $_POST['width'];
$height = $_POST['height'];

$sql = " select it_id, it_price from {$g4['shop_item_table']} where it_id = '$it_id' ";
$row = sql_fetch($sql);

if(!$row['it_id']) {
    echo '상품 정보가 존재하지 않습니다.';
    exit;
}

$img = get_it_image($row['it_id'], $width, $height);

if(!$img)
    $img = '<img src="'.G4_SHOP_URL.'/img/no_image.gif" width="'.$width.'" height="'.$height.'" alt="">';

echo '<a href="'.G4_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" target="_blank">'.$img.'</a><br>'.display_price($row['it_price']);
?>