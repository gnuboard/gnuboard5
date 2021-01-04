<?php
include_once('./_common.php');

$it_id = isset($_GET['it_id']) ? get_search_string(trim($_GET['it_id'])) : '';
$no = isset($_GET['no']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['no']) : '';

$row = get_shop_item($it_id, true);

if(! (isset($row['it_id']) && $row['it_id']))
    alert_close('상품정보가 존재하지 않습니다.');

$imagefile = G5_DATA_PATH.'/item/'.$row['it_img'.$no];
$imagefileurl = run_replace('get_item_image_url', G5_DATA_URL.'/item/'.$row['it_img'.$no], $row, $no);
$size = file_exists($imagefile) ? @getimagesize($imagefile) : array();

$g5['title'] = "{$row['it_name']} ($it_id)";
include_once(G5_PATH.'/head.sub.php');

$skin = G5_MSHOP_SKIN_PATH.'/largeimage.skin.php';

if(is_file($skin))
    include_once($skin);
else
    echo '<p>'.str_replace(G5_PATH.'/', '', $skin).'파일이 존재하지 않습니다.</p>';

include_once(G5_PATH.'/tail.sub.php');