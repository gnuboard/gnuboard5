<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/itemqa.php');
    return;
}

include_once(G5_LIB_PATH.'/thumbnail.lib.php');

$itemqa_skin = G5_SHOP_SKIN_PATH.'/itemqa.skin.php';

if(!file_exists($itemqa_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemqa_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemqa_skin);
}
?>