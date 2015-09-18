<?php
include_once('./_common.php');

$it_id = $_GET['it_id'];
$no = $_GET['no'];

$sql = " select it_id, it_name, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
            from {$g5['g5_shop_item_table']} where it_id='$it_id' ";
$row = sql_fetch_array(sql_query($sql));

if(!$row['it_id'])
    alert_close('상품정보가 존재하지 않습니다.');

$imagefile = G5_DATA_PATH.'/item/'.$row['it_img'.$no];
$imagefileurl = G5_DATA_URL.'/item/'.$row['it_img'.$no];
$size = getimagesize($imagefile);

$g5['title'] = "{$row['it_name']} ($it_id)";
include_once(G5_PATH.'/head.sub.php');

$skin = G5_MSHOP_SKIN_PATH.'/largeimage.skin.php';

if(is_file($skin))
    include_once($skin);
else
    echo '<p>'.str_replace(G5_PATH.'/', '', $skin).'파일이 존재하지 않습니다.</p>';

include_once(G5_PATH.'/tail.sub.php');
?>